"""
点検周期・点検箇所の自動計画ロジック（pandas）。
トラブル集計と日々の状態監視データを突合して推奨値を返す。
"""

from __future__ import annotations

from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import pandas as pd

DATA_DIR = Path(__file__).resolve().parent / "data"
SAMPLE_MONITORING_CSV = DATA_DIR / "monitoring_sample.csv"


def load_monitoring_frame(rows: list[dict[str, Any]] | None = None) -> tuple[pd.DataFrame, str]:
    """監視データを DataFrame 化。未指定時はサンプル CSV を使用。"""
    if rows:
        df = pd.DataFrame(rows)
        source = "request"
    elif SAMPLE_MONITORING_CSV.exists():
        df = pd.read_csv(SAMPLE_MONITORING_CSV)
        source = "sample_csv"
    else:
        df = pd.DataFrame(
            columns=[
                "equipment",
                "area",
                "point",
                "category_major",
                "category_middle",
                "category_minor",
                "date",
                "metric",
                "value",
                "threshold",
                "status",
            ]
        )
        source = "empty"

    if df.empty:
        return df, source

    if "date" in df.columns:
        df["date"] = pd.to_datetime(df["date"], errors="coerce")
    if "value" in df.columns:
        df["value"] = pd.to_numeric(df["value"], errors="coerce")
    if "threshold" in df.columns:
        df["threshold"] = pd.to_numeric(df["threshold"], errors="coerce")

    # 閾値超過を異常扱い
    if "status" not in df.columns:
        df["status"] = "ok"
    exceed = df["threshold"].notna() & df["value"].notna() & (df["value"] >= df["threshold"])
    df.loc[exceed, "status"] = df.loc[exceed, "status"].where(
        ~df.loc[exceed, "status"].isin(["ok", ""]), "warn"
    )

    return df, source


def _monitoring_risk(df: pd.DataFrame, major: str, middle: str, minor: str) -> dict[str, Any]:
    if df.empty:
        return {
            "anomaly_rate": 0.0,
            "recent_anomaly_count": 0,
            "hot_points": [],
            "avg_over_ratio": 0.0,
        }

    mask = pd.Series(True, index=df.index)
    if "category_major" in df.columns and major and major != "(未設定)":
        mask &= df["category_major"].fillna("") == major
    if "category_middle" in df.columns and middle and middle != "(未設定)":
        mask &= df["category_middle"].fillna("").isin([middle, ""]) | (
            df["category_middle"].fillna("") == middle
        )
    if "category_minor" in df.columns and minor and minor != "(未設定)":
        # 小分類一致を優先。無ければ中分類単位にフォールバック
        minor_mask = df["category_minor"].fillna("") == minor
        if minor_mask.any():
            mask &= minor_mask

    subset = df.loc[mask].copy()
    if subset.empty:
        # 大分類のみで再試行
        if "category_major" in df.columns:
            subset = df[df["category_major"].fillna("") == major].copy()
        else:
            subset = df.copy()

    if subset.empty:
        return {
            "anomaly_rate": 0.0,
            "recent_anomaly_count": 0,
            "hot_points": [],
            "avg_over_ratio": 0.0,
        }

    anomaly = subset["status"].astype(str).str.lower().isin(["warn", "alarm", "critical", "ng"])
    anomaly_rate = float(anomaly.mean()) if len(subset) else 0.0

    recent_cut = subset["date"].max()
    recent_anomaly_count = 0
    if pd.notna(recent_cut):
        recent = subset[subset["date"] >= (recent_cut - pd.Timedelta(days=14))]
        recent_anomaly_count = int(
            recent["status"].astype(str).str.lower().isin(["warn", "alarm", "critical", "ng"]).sum()
        )

    over_ratio = 0.0
    if subset["threshold"].notna().any() and subset["value"].notna().any():
        valid = subset.dropna(subset=["value", "threshold"])
        valid = valid[valid["threshold"] > 0]
        if not valid.empty:
            over_ratio = float((valid["value"] / valid["threshold"]).mean())

    hot_points: list[str] = []
    if "point" in subset.columns:
        hot = (
            subset.loc[anomaly]
            .groupby("point", dropna=False)
            .size()
            .sort_values(ascending=False)
            .head(3)
        )
        hot_points = [str(idx) for idx in hot.index if str(idx) not in ("", "nan", "None")]

    return {
        "anomaly_rate": round(anomaly_rate, 3),
        "recent_anomaly_count": recent_anomaly_count,
        "hot_points": hot_points,
        "avg_over_ratio": round(over_ratio, 3),
    }


def _cycle_days(
    frequency_per_month: float,
    avg_days_between: float | None,
    anomaly_rate: float,
    recent_anomaly_count: int,
    default_cycle: int,
    min_cycle: int,
    max_cycle: int,
) -> tuple[int, float, str]:
    """リスクに応じて推奨周期（日）を算出。"""
    risk = 0.0
    reasons: list[str] = []

    # トラブル頻度
    if frequency_per_month >= 4:
        risk += 0.45
        reasons.append(f"高頻度トラブル({frequency_per_month}/月)")
    elif frequency_per_month >= 2:
        risk += 0.30
        reasons.append(f"中頻度トラブル({frequency_per_month}/月)")
    elif frequency_per_month >= 0.5:
        risk += 0.15
        reasons.append(f"低〜中頻度({frequency_per_month}/月)")
    else:
        risk += 0.05
        reasons.append("低頻度")

    if avg_days_between is not None and avg_days_between > 0:
        if avg_days_between <= 14:
            risk += 0.25
            reasons.append(f"再発間隔が短い({avg_days_between}日)")
        elif avg_days_between <= 45:
            risk += 0.12
            reasons.append(f"再発間隔 {avg_days_between}日")

    # 監視データ
    if anomaly_rate >= 0.35:
        risk += 0.30
        reasons.append(f"監視異常率が高い({anomaly_rate:.0%})")
    elif anomaly_rate >= 0.15:
        risk += 0.18
        reasons.append(f"監視異常率 {anomaly_rate:.0%}")
    elif anomaly_rate > 0:
        risk += 0.08
        reasons.append(f"監視に軽微な異常({anomaly_rate:.0%})")

    if recent_anomaly_count >= 3:
        risk += 0.15
        reasons.append(f"直近14日の異常 {recent_anomaly_count}件")
    elif recent_anomaly_count >= 1:
        risk += 0.07
        reasons.append(f"直近異常 {recent_anomaly_count}件")

    risk = min(1.0, round(risk, 3))

    # 周期: リスクが高いほど短く
    # risk 0 → default, risk 1 → min
    cycle = int(round(default_cycle - (default_cycle - min_cycle) * risk))
    cycle = max(min_cycle, min(max_cycle, cycle))

    # 平均再発間隔がある場合は、その半分〜同等を上限の目安に
    if avg_days_between is not None and avg_days_between > 0:
        suggested = max(min_cycle, int(round(avg_days_between * 0.7)))
        cycle = min(cycle, suggested)
        cycle = max(min_cycle, min(max_cycle, cycle))

    return cycle, risk, " / ".join(reasons)


def build_inspection_plans(
    troubles: list[dict[str, Any]],
    monitoring_rows: list[dict[str, Any]] | None = None,
    options: dict[str, Any] | None = None,
) -> dict[str, Any]:
    options = options or {}
    default_cycle = int(options.get("default_cycle_days", 180))
    min_cycle = int(options.get("min_cycle_days", 7))
    max_cycle = int(options.get("max_cycle_days", 365))

    mon_df, mon_source = load_monitoring_frame(monitoring_rows)
    trouble_df = pd.DataFrame(troubles)

    plans: list[dict[str, Any]] = []
    if trouble_df.empty:
        return {
            "plans": [],
            "generated_at": datetime.now(timezone.utc).isoformat(),
            "monitoring_source": mon_source,
            "notes": "トラブル集計が空のため計画を生成できませんでした。",
        }

    for _, row in trouble_df.iterrows():
        major = str(row.get("category_major") or "(未設定)")
        middle = str(row.get("category_middle") or "(未設定)")
        minor = str(row.get("category_minor") or "(未設定)")
        freq = float(row.get("frequency_per_month") or 0)
        avg_between = row.get("avg_days_between")
        avg_between_f = float(avg_between) if avg_between not in (None, "", "None") else None
        count = int(row.get("count") or 0)

        mon = _monitoring_risk(mon_df, major, middle, minor)
        cycle, risk, rationale = _cycle_days(
            frequency_per_month=freq,
            avg_days_between=avg_between_f,
            anomaly_rate=mon["anomaly_rate"],
            recent_anomaly_count=mon["recent_anomaly_count"],
            default_cycle=default_cycle,
            min_cycle=min_cycle,
            max_cycle=max_cycle,
        )

        points: list[str] = []
        if minor and minor != "(未設定)":
            points.append(f"{middle}/{minor}" if middle != "(未設定)" else minor)
        if middle and middle != "(未設定)" and middle not in "".join(points):
            points.append(f"{major}/{middle}" if major != "(未設定)" else middle)
        points.extend(mon["hot_points"])
        # 重複除去（順序維持）
        uniq_points: list[str] = []
        for p in points:
            if p and p not in uniq_points:
                uniq_points.append(p)
        if not uniq_points:
            uniq_points = [major if major != "(未設定)" else "全体"]

        plans.append(
            {
                "category_major": major,
                "category_middle": middle,
                "category_minor": minor,
                "trouble_count": count,
                "frequency_per_month": freq,
                "recommended_cycle_days": cycle,
                "recommended_points": uniq_points[:5],
                "risk_score": risk,
                "monitoring_anomaly_rate": mon["anomaly_rate"],
                "rationale": rationale,
            }
        )

    plans.sort(key=lambda p: (-p["risk_score"], p["recommended_cycle_days"]))

    return {
        "plans": plans,
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "monitoring_source": mon_source,
        "notes": f"{len(plans)} 件の分類について推奨周期を算出しました。",
    }


if __name__ == "__main__":
    import json
    import sys

    payload = json.load(sys.stdin)
    result = build_inspection_plans(
        troubles=payload.get("troubles", []),
        monitoring_rows=payload.get("monitoring"),
        options=payload.get("options"),
    )
    json.dump(result, sys.stdout, ensure_ascii=False, indent=2)
    sys.stdout.write("\n")
