<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SystemSetting;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TsenaKelyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    // Test d acces a la vitrine de la boutique
    public function test_shop_index_is_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('TSENA KELY');
    }

    // Test d acces au tableau de bord du service Stock
    public function test_stock_dashboard_is_accessible(): void
    {
        $response = $this->get('/stock');
        $response->assertStatus(200);
        $response->assertSee('Service Stock');
    }

    // Test d acces a la liste des commandes
    public function test_orders_index_is_accessible(): void
    {
        $response = $this->get('/orders');
        $response->assertStatus(200);
        $response->assertSee('Service Commandes');
    }

    // Test de l endpoint de metriques Prometheus
    public function test_prometheus_metrics_endpoint(): void
    {
        $response = $this->get('/metrics');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
        $response->assertSee('tsena_orders_total');
        $response->assertSee('tsena_orders_status_total');
    }

    // Test du passage de commande reussie
    public function test_checkout_processes_order_successfully(): void
    {
        $product = Product::first();
        $this->assertNotNull($product);

        $cart = [
            ['id' => $product->id, 'qty' => 1, 'name' => $product->name, 'price' => $product->price],
        ];

        $response = $this->post('/checkout', [
            'customer_name' => 'Test Unit Client',
            'customer_email' => 'test@tsenakely.mg',
            'customer_phone' => '0341234567',
            'cart_items' => json_encode($cart),
        ]);

        $response->assertStatus(200);
        $response->assertSee('Commande Reussie');
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Test Unit Client',
            'status' => 'completed',
        ]);
    }

    // Test de mise a jour des interrupteurs d anomalies
    public function test_update_chaos_settings(): void
    {
        $response = $this->post('/telemetry/settings', [
            'chaos_latency_ms' => 500,
            'chaos_error_rate' => 20,
            'chaos_stock_down' => '1',
        ]);

        $response->assertRedirect();
        $this->assertEquals('500', SystemSetting::getVal('chaos_latency_ms'));
        $this->assertEquals('20', SystemSetting::getVal('chaos_error_rate'));
        $this->assertEquals('1', SystemSetting::getVal('chaos_stock_down'));

        // Reinitialiser
        SystemSetting::setVal('chaos_latency_ms', '0');
        SystemSetting::setVal('chaos_error_rate', '0');
        SystemSetting::setVal('chaos_stock_down', '0');
    }

    // Test de la structure App Shell SPA et des Skeleton Loaders
    public function test_spa_app_shell_and_skeleton_assets_are_present(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Verification des elements structurels SPA
        $response->assertSee('id="topProgressBar"', false);
        $response->assertSee('id="app-main"', false);
        $response->assertSee('id="chaosBannerContainer"', false);
        $response->assertSee('id="flashAlertContainer"', false);
        $response->assertSee('data-nav-route="shop"', false);
        $response->assertSee('data-nav-route="stock"', false);
        $response->assertSee('data-nav-route="orders"', false);
        $response->assertSee('data-nav-route="telemetry"', false);

        // Verification du moteur de Skeleton Shimmer
        $response->assertSee('skeleton-shimmer', false);
        $response->assertSee('spaNavigate', false);
    }
}
