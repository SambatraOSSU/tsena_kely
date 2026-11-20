<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;

class SimulationService
{
    protected OrderService $orderService;

    protected TelemetryService $telemetry;

    public function __construct(OrderService $orderService, TelemetryService $telemetry)
    {
        $this->orderService = $orderService;
        $this->telemetry = $telemetry;
    }

    // Simulation de flux de trafic continu ou par lots d utilisateurs
    public function simulateTraffic(int $count = 5): array
    {
        $products = Product::all();
        if ($products->isEmpty()) {
            return ['status' => 'error', 'message' => 'Aucun produit disponible pour simuler'];
        }

        $names = ['Rabe', 'Rasoa', 'Andry', 'Voahirana', 'Koto', 'Bema', 'Fara', 'Herizo', 'Hery', 'Soa'];
        $results = [];

        for ($i = 0; $i < $count; $i++) {
            $traceId = TelemetryService::generateTraceId();
            $customerName = $names[array_rand($names)].' '.rand(10, 99);

            // Selection aleatoire de 1 a 3 produits
            $numItems = rand(1, min(3, $products->count()));
            $chosenProducts = $products->random($numItems);
            $items = [];

            foreach ($chosenProducts as $product) {
                // Occasionnellement demander plus que le stock pour declencher une alerte
                $qty = (rand(1, 10) === 10) ? $product->stock + rand(1, 5) : rand(1, 2);
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => max(1, $qty),
                ];
            }

            $order = $this->orderService->processOrder([
                'customer_name' => $customerName,
                'customer_email' => strtolower(Str::slug($customerName)).'@example.mg',
                'customer_phone' => '034'.rand(1000000, 9999999),
                'items' => $items,
            ], $traceId);

            $results[] = [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'customer' => $order->customer_name,
                'trace_id' => $order->trace_id,
                'execution_time_ms' => $order->execution_time_ms,
                'failure_reason' => $order->failure_reason,
            ];
        }

        return [
            'status' => 'success',
            'count' => $count,
            'results' => $results,
        ];
    }
}
