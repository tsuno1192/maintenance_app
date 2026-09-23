<?php

namespace App\Jobs;

use App\Models\Documentation;
use App\Models\GrowthAlbum;
use App\Models\GrowthAlbumItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class GenerateGrowthAlbumJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(
        public int $albumId,
    ) {}

    public function handle(): void
    {
        $album = GrowthAlbum::query()->find($this->albumId);
        if (! $album) {
            return;
        }

        [$year, $month] = array_pad(explode('-', $album->year_month), 2, null);
        if (! $year || ! $month) {
            $album->update(['status' => GrowthAlbum::STATUS_FAILED]);

            return;
        }

        $start = Carbon::createFromDate((int) $year, (int) $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $photos = Documentation::query()
            ->where('child_id', $album->child_id)
            ->whereNotNull('child_id')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('face_match_status', ['matched', 'manual', 'skipped'])
            ->latest('id')
            ->limit(40)
            ->get(['id', 'ai_episode_title', 'non_cognitive_skill', 'created_at']);

        if ($photos->isEmpty()) {
            $album->update(['status' => GrowthAlbum::STATUS_FAILED]);

            return;
        }

        GrowthAlbumItem::query()->where('growth_album_id', $album->id)->delete();

        $photos
            ->map(function (Documentation $doc, int $index) {
                $score = 50.0;
                $title = mb_strtolower((string) $doc->ai_episode_title);
                if (str_contains($title, '笑') || str_contains($title, '笑顔') || str_contains($title, '喜び')) {
                    $score += 30;
                }
                if (filled($doc->non_cognitive_skill)) {
                    $score += 10;
                }

                return [
                    'doc' => $doc,
                    'score' => min(99.9, $score + (40 - $index) * 0.1),
                ];
            })
            ->sortByDesc('score')
            ->take(12)
            ->values()
            ->each(function (array $row, int $order) use ($album) {
                GrowthAlbumItem::create([
                    'growth_album_id' => $album->id,
                    'documentation_id' => $row['doc']->id,
                    'score' => $row['score'],
                    'sort_order' => $order + 1,
                ]);
            });

        $album->update([
            'status' => GrowthAlbum::STATUS_READY,
            'shared_at' => now(),
        ]);
    }
}
