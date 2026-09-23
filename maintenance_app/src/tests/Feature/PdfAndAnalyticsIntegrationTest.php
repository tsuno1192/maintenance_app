<?php

namespace Tests\Feature;

use App\Enums\TroubleStatus;
use App\Enums\UserRole;
use App\Models\Trouble;
use App\Models\User;
use App\Services\MaintenanceRequestPdfService;
use App\Services\PythonEngineClient;
use App\Services\TroubleAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PdfAndAnalyticsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_maintenance_request_pdf_is_generated(): void
    {
        $user = User::factory()->role(UserRole::Leader, '運転Gr')->create();
        $trouble = Trouble::factory()
            ->forGroup('運転Gr')
            ->status(TroubleStatus::Leader)
            ->create([
                'title' => 'PDF出力テスト',
                'content' => '内容サンプル',
            ]);

        $response = $this->actingAs($user)
            ->get(route('troubles.pdf', $trouble));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_pdf_service_returns_valid_binary(): void
    {
        $trouble = Trouble::factory()->create(['title' => 'サービスPDF']);
        $response = app(MaintenanceRequestPdfService::class)->download($trouble);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_analytics_page_aggregates_troubles(): void
    {
        $user = User::factory()->role(UserRole::OpsManager, '運転Gr')->create();

        Trouble::factory()->count(2)->create([
            'category_major' => '設備',
            'category_middle' => 'ポンプ',
            'category_minor' => '軸受',
            'created_group' => '運転Gr',
            'occurred_on' => now()->subDays(10)->toDateString(),
        ]);
        Trouble::factory()->create([
            'category_major' => '設備',
            'category_middle' => 'ポンプ',
            'category_minor' => '軸受',
            'created_group' => '運転Gr',
            'occurred_on' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertSee('分類別集計')
            ->assertSee('設備')
            ->assertSee('ポンプ');

        $aggregates = app(TroubleAnalyticsService::class)->aggregateByCategory();
        $this->assertGreaterThanOrEqual(1, $aggregates->count());
        $this->assertSame(3, (int) $aggregates->first()['count']);
    }

    public function test_python_inspection_plan_integration_via_http_fake(): void
    {
        Http::fake([
            'python_engine:8000/health' => Http::response(['status' => 'ok'], 200),
            'python_engine:8000/inspection/plan' => Http::response([
                'plans' => [[
                    'category_major' => '設備',
                    'category_middle' => 'ポンプ',
                    'category_minor' => '軸受',
                    'recommended_cycle_days' => 30,
                    'recommended_points' => ['ポンプ/軸受', '軸受DE'],
                    'risk_score' => 0.72,
                    'rationale' => 'テスト根拠',
                ]],
                'generated_at' => now()->toIso8601String(),
                'monitoring_source' => 'fake',
                'notes' => 'ok',
            ], 200),
        ]);

        $user = User::factory()->role(UserRole::Admin, '運転Gr')->create();

        Trouble::factory()->create([
            'category_major' => '設備',
            'category_middle' => 'ポンプ',
            'category_minor' => '軸受',
            'occurred_on' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->post(route('analytics.plan'))
            ->assertRedirect(route('analytics.index'))
            ->assertSessionHas('success')
            ->assertSessionHas('inspection_plans');

        $this->actingAs($user)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertSee('推奨点検計画')
            ->assertSee('30')
            ->assertSee('軸受DE');
    }

    public function test_python_engine_client_plan_inspections(): void
    {
        Http::fake([
            '*/inspection/plan' => Http::response([
                'plans' => [['recommended_cycle_days' => 45, 'risk_score' => 0.5]],
                'generated_at' => now()->toIso8601String(),
                'monitoring_source' => 'fake',
            ], 200),
        ]);

        $result = app(PythonEngineClient::class)->planInspections([
            [
                'category_major' => '設備',
                'category_middle' => 'ポンプ',
                'category_minor' => '軸受',
                'count' => 2,
                'frequency_per_month' => 1.0,
            ],
        ]);

        $this->assertArrayHasKey('plans', $result);
        $this->assertSame(45, $result['plans'][0]['recommended_cycle_days']);
    }
}
