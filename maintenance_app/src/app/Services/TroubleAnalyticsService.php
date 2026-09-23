<?php

namespace App\Services;

use App\Models\Trouble;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TroubleAnalyticsService
{
    /**
     * 大・中・小分類ごとの件数・発生頻度を集計する。
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function aggregateByCategory(?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $query = Trouble::query()
            ->select([
                'category_major',
                'category_middle',
                'category_minor',
                'occurred_on',
                'created_at',
            ]);

        if ($from) {
            $query->whereDate('occurred_on', '>=', $from->toDateString());
        }
        if ($to) {
            $query->whereDate('occurred_on', '<=', $to->toDateString());
        }

        $rows = $query->get();

        return $rows
            ->groupBy(function (Trouble $trouble) {
                return implode('||', [
                    $trouble->category_major ?: '(未設定)',
                    $trouble->category_middle ?: '(未設定)',
                    $trouble->category_minor ?: '(未設定)',
                ]);
            })
            ->map(function (Collection $group) {
                /** @var Trouble $first */
                $first = $group->first();

                $dates = $group
                    ->map(fn (Trouble $t) => $t->occurred_on ?? $t->created_at?->startOfDay())
                    ->filter()
                    ->sort()
                    ->values();

                $count = $group->count();
                $firstDate = $dates->first();
                $lastDate = $dates->last();

                $spanDays = 1;
                if ($firstDate && $lastDate) {
                    $spanDays = max(1, $firstDate->diffInDays($lastDate) + 1);
                }

                $months = max(1 / 30, $spanDays / 30);
                $frequencyPerMonth = round($count / $months, 2);

                $avgDaysBetween = null;
                if ($count >= 2 && $firstDate && $lastDate) {
                    $avgDaysBetween = round($firstDate->diffInDays($lastDate) / max(1, $count - 1), 1);
                }

                return [
                    'category_major' => $first->category_major ?: '(未設定)',
                    'category_middle' => $first->category_middle ?: '(未設定)',
                    'category_minor' => $first->category_minor ?: '(未設定)',
                    'count' => $count,
                    'first_occurred_on' => $firstDate?->toDateString(),
                    'last_occurred_on' => $lastDate?->toDateString(),
                    'span_days' => $spanDays,
                    'frequency_per_month' => $frequencyPerMonth,
                    'avg_days_between' => $avgDaysBetween,
                ];
            })
            ->sortByDesc('count')
            ->values();
    }

    /**
     * @return array{total: int, categories: int, top_major: string|null}
     */
    public function summary(Collection $aggregates): array
    {
        return [
            'total' => (int) $aggregates->sum('count'),
            'categories' => $aggregates->count(),
            'top_major' => $aggregates->first()['category_major'] ?? null,
        ];
    }
}
