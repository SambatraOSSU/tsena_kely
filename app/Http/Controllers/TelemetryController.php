<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\SystemSetting;
use App\Models\TelemetryLog;
use App\Services\SimulationService;
use App\Services\TelemetryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TelemetryController extends Controller
{
    protected TelemetryService $telemetry;

    protected SimulationService $simulation;

    public function __construct(TelemetryService $telemetry, SimulationService $simulation)
    {
        $this->telemetry = $telemetry;
        $this->simulation = $simulation;
    }

    // Point de terminaison Prometheus pour le scraping des metriques
    public function metrics(): Response
    {
        $metrics = $this->telemetry->renderPrometheusMetrics();

        return response($metrics, 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }

    // Tableau de bord d observabilite et d injection de pannes
    public function dashboard(Request $request)
    {
        $settings = [
            'chaos_latency_ms' => (int) SystemSetting::getVal('chaos_latency_ms', 0),
            'chaos_error_rate' => (int) SystemSetting::getVal('chaos_error_rate', 0),
            'chaos_stock_down' => SystemSetting::getVal('chaos_stock_down', '0') === '1',
            'chaos_orders_down' => SystemSetting::getVal('chaos_orders_down', '0') === '1',
        ];

        $totalOrders = Order::count();
        $successfulOrders = Order::where('status', 'completed')->count();
        $failedOrders = Order::where('status', 'failed')->count();
        $avgLatency = round(Order::avg('execution_time_ms') ?? 0, 2);

        // Logs de telemetrie avec filtre trace_id optionnel
        $query = TelemetryLog::latest('id');
        if ($request->filled('trace_id')) {
            $query->where('trace_id', $request->input('trace_id'));
        }
        if ($request->filled('service')) {
            $query->where('service', $request->input('service'));
        }
        if ($request->filled('level')) {
            $query->where('level', $request->input('level'));
        }

        $logs = $query->limit(50)->get();
        $recentOrders = Order::latest('id')->limit(12)->get()->reverse()->values();
        $stockProducts = Product::all(['id', 'name', 'stock']);

        return view('telemetry.dashboard', compact('settings', 'totalOrders', 'successfulOrders', 'failedOrders', 'avgLatency', 'logs', 'recentOrders', 'stockProducts'));
    }

    // Mise a jour des interrupteurs de pannes pour la demonstration
    public function updateSettings(Request $request)
    {
        $latency = max(0, min(10000, (int) $request->input('chaos_latency_ms', 0)));
        $errorRate = max(0, min(100, (int) $request->input('chaos_error_rate', 0)));
        $stockDown = $request->boolean('chaos_stock_down') ? '1' : '0';
        $ordersDown = $request->boolean('chaos_orders_down') ? '1' : '0';

        SystemSetting::setVal('chaos_latency_ms', (string) $latency);
        SystemSetting::setVal('chaos_error_rate', (string) $errorRate);
        SystemSetting::setVal('chaos_stock_down', $stockDown);
        SystemSetting::setVal('chaos_orders_down', $ordersDown);

        return back()->with('success', 'Parametres d injection d anomalies mis a jour.');
    }

    // Declenchement manuel de generation de trafic depuis l interface
    public function simulate(Request $request)
    {
        $count = max(1, min(50, (int) $request->input('count', 5)));
        $result = $this->simulation->simulateTraffic($count);

        return back()->with('success', "Simulation executee: {$count} requetes de commandes generees.");
    }

    // Vue detaillee d une trace (liaison trace -> spans -> logs pour le diagnostic)
    public function trace(string $traceId)
    {
        $logs = TelemetryLog::where('trace_id', $traceId)->orderBy('id')->get();
        $order = Order::where('trace_id', $traceId)->first();

        return view('telemetry.trace', compact('traceId', 'logs', 'order'));
    }
}
