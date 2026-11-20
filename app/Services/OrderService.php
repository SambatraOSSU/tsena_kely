<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    protected StockService $stockService;

    protected TelemetryService $telemetry;

    public function __construct(StockService $stockService, TelemetryService $telemetry)
    {
        $this->stockService = $stockService;
        $this->telemetry = $telemetry;
    }

    // Traitement complet de la commande avec observation et injection de chaos
    public function processOrder(array $data, string $traceId): Order
    {
        $startTime = microtime(true);
        $this->telemetry->log('commandes', 'info', 'Debut de reception de commande', $traceId, null, [
            'customer' => $data['customer_name'] ?? 'Anonyme',
            'items' => $data['items'] ?? [],
        ]);

        // Interrupteur: lenteur artificielle
        $latencyMs = (int) SystemSetting::getVal('chaos_latency_ms', 0);
        if ($latencyMs > 0) {
            $this->telemetry->log('commandes', 'warning', "Injection de latence artificielle: {$latencyMs}ms", $traceId);
            usleep($latencyMs * 1000);
        }

        // Interrupteur: simulation de panne du service Commandes
        if (SystemSetting::getVal('chaos_orders_down') === '1') {
            $this->telemetry->log('commandes', 'error', 'Service Commandes indisponible (panne simulee)', $traceId);
            $duration = (microtime(true) - $startTime) * 1000;

            return $this->recordFailedOrder($data, 'Service Commandes indisponible (Code 503)', $traceId, $duration);
        }

        // Interrupteur: simulation d erreur aleatoire ou forcee
        $errorRate = (int) SystemSetting::getVal('chaos_error_rate', 0);
        if ($errorRate > 0 && rand(1, 100) <= $errorRate) {
            $this->telemetry->log('commandes', 'error', "Injection d une erreur serveur interne ({$errorRate}%)", $traceId);
            $duration = (microtime(true) - $startTime) * 1000;

            return $this->recordFailedOrder($data, 'Erreur interne simulee (500)', $traceId, $duration);
        }

        // Appel du service Stock pour verifier la disponibilite
        try {
            $stockCheck = $this->stockService->checkAvailability($data['items'], $traceId);
            if (! $stockCheck['available']) {
                $reason = implode('; ', $stockCheck['errors']);
                $duration = (microtime(true) - $startTime) * 1000;

                return $this->recordFailedOrder($data, $reason, $traceId, $duration);
            }
        } catch (\Throwable $e) {
            $duration = (microtime(true) - $startTime) * 1000;

            return $this->recordFailedOrder($data, $e->getMessage(), $traceId, $duration);
        }

        // Enregistrement de la commande dans PostgreSQL
        try {
            $order = DB::transaction(function () use ($data, $traceId) {
                $totalAmount = 0;
                $preparedItems = [];

                foreach ($data['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $itemTotal = $product->price * $item['quantity'];
                    $totalAmount += $itemTotal;

                    $preparedItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price,
                        'total_price' => $itemTotal,
                    ];
                }

                $orderNumber = 'CMD-'.strtoupper(Str::random(8));

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'customer_name' => $data['customer_name'],
                    'customer_email' => $data['customer_email'] ?? null,
                    'customer_phone' => $data['customer_phone'] ?? null,
                    'total_amount' => $totalAmount,
                    'status' => 'completed',
                    'failure_reason' => null,
                    'trace_id' => $traceId,
                    'execution_time_ms' => 0,
                ]);

                foreach ($preparedItems as $pItem) {
                    $order->items()->create($pItem);
                }

                // Decrementer le stock
                $this->stockService->decrementStock($data['items'], $traceId);

                return $order;
            });

            $duration = (microtime(true) - $startTime) * 1000;
            $order->execution_time_ms = round($duration, 2);
            $order->save();

            $this->telemetry->log('commandes', 'info', "Commande {$order->order_number} validee avec succes", $traceId, null, [
                'order_id' => $order->id,
                'total_amount' => $order->total_amount,
                'execution_time_ms' => $order->execution_time_ms,
            ]);

            return $order;
        } catch (\Throwable $e) {
            $duration = (microtime(true) - $startTime) * 1000;
            $this->telemetry->log('commandes', 'error', 'Erreur SQL lors de l enregistrement de la commande: '.$e->getMessage(), $traceId);

            return $this->recordFailedOrder($data, 'Erreur base de donnees: '.$e->getMessage(), $traceId, $duration);
        }
    }

    // Sauvegarde d un enregistrement de commande en echec pour les statistiques
    protected function recordFailedOrder(array $data, string $reason, string $traceId, float $duration): Order
    {
        $order = Order::create([
            'order_number' => 'FAIL-'.strtoupper(Str::random(8)),
            'customer_name' => $data['customer_name'] ?? 'Inconnu',
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'total_amount' => 0,
            'status' => 'failed',
            'failure_reason' => $reason,
            'trace_id' => $traceId,
            'execution_time_ms' => round($duration, 2),
        ]);

        $this->telemetry->log('commandes', 'warning', "Commande marquee en echec: {$reason}", $traceId, null, [
            'order_id' => $order->id,
            'failure_reason' => $reason,
            'execution_time_ms' => $order->execution_time_ms,
        ]);

        return $order;
    }
}
