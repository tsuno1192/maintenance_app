"""
TMQ Python Engine — 現場トラブル分析 / 点検周期自動計画 API
"""

from __future__ import annotations

from typing import Any

from fastapi import FastAPI
from pydantic import BaseModel, Field

from inspection_planner import build_inspection_plans

app = FastAPI(title="TMQ Python Engine", version="0.2.0")


class AnalyzeRequest(BaseModel):
    title: str
    description: str | None = None


class AnalyzeResponse(BaseModel):
    status: str
    summary: str
    suggestions: list[str]


class TroubleAggregate(BaseModel):
    category_major: str
    category_middle: str | None = None
    category_minor: str | None = None
    count: int = 0
    first_occurred_on: str | None = None
    last_occurred_on: str | None = None
    span_days: int | None = None
    frequency_per_month: float = 0
    avg_days_between: float | None = None


class MonitoringRow(BaseModel):
    equipment: str | None = None
    area: str | None = None
    point: str | None = None
    category_major: str | None = None
    category_middle: str | None = None
    category_minor: str | None = None
    date: str | None = None
    metric: str | None = None
    value: float | None = None
    threshold: float | None = None
    status: str | None = None


class InspectionPlanOptions(BaseModel):
    default_cycle_days: int = 180
    min_cycle_days: int = 7
    max_cycle_days: int = 365


class InspectionPlanRequest(BaseModel):
    troubles: list[TroubleAggregate] = Field(default_factory=list)
    monitoring: list[MonitoringRow] | None = None
    options: InspectionPlanOptions | None = None


@app.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok", "service": "tmq-python-engine"}


@app.post("/analyze", response_model=AnalyzeResponse)
def analyze(payload: AnalyzeRequest) -> AnalyzeResponse:
    return AnalyzeResponse(
        status="ok",
        summary=f"Received: {payload.title}",
        suggestions=[
            "Check recent maintenance logs",
            "Verify sensor / equipment status",
            "Escalate if recurring within 7 days",
        ],
    )


@app.post("/inspection/plan")
def inspection_plan(payload: InspectionPlanRequest) -> dict[str, Any]:
    """
    トラブル集計 + 状態監視データを突合し、推奨点検周期・点検箇所を返す。
    monitoring 未指定時は python/data/monitoring_sample.csv を使用。
    """
    monitoring_rows = None
    if payload.monitoring is not None:
        monitoring_rows = [row.model_dump() for row in payload.monitoring]

    return build_inspection_plans(
        troubles=[row.model_dump() for row in payload.troubles],
        monitoring_rows=monitoring_rows,
        options=(payload.options.model_dump() if payload.options else None),
    )
