<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\TelemetryLog;
use Illuminate\Support\Facades\File;

class TelemetryService
{
    // Generation d identifiant de trace standard W3C
    public static function generateTraceId(): string
    {
        return bin2hex(random_bytes(16));
    }

    // Generation d identifiant de span
    public static function generateSpanId(): string
    {
        return bin2hex(random_bytes(8));
    }

    // Enregistrement de log structure JSON pour Loki et Tempo
    public function log(string $service, string $level, string $message, string $traceId, ?string $spanId = null, array $context = []): void
    {
        $spanId = $spanId ?? self::generateSpanId();
        $timestamp = now()->toISOString();

        $logData = [
            'timestamp' => $timestamp,
            'trace_id' => $traceId,
            'span_id' => $spanId,
            'service' => $service,
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        // Ecriture dans le fichier de log JSON pour le collecteur
        $logPath = storage_path('logs/telemetry.json');
        File::append($logPath, json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL);

        // Sauvegarde en base PostgreSQL pour le diagnostic direct
        try {
            TelemetryLog::create([
                'trace_id' => $traceId,
                'span_id' => $spanId,
                'service' => $service,
                'level' => $level,
                'message' => $message,
                'context' => $context,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Ignorer l erreur d ecriture de log si DB indisponible
        }
    }

    // Exportation des metriques au format Prometheus pour scraping
    public function renderPrometheusMetrics(): string
    {
        $totalOrders = Order::count();
        $successfulOrders = Order::where('status', 'completed')->count();
        $failedOrders = Order::where('status', 'failed')->count();
        $avgExecutionTime = Order::avg('execution_time_ms') ?? 0;

        $lines = [];
        $lines[] = '# HELP tsena_orders_total Nombre total de requetes de commandes';
        $lines[] = '# TYPE tsena_orders_total counter';
        $lines[] = "tsena_orders_total {$totalOrders}";

        $lines[] = '# HELP tsena_orders_status_total Commandes ventilees par statut';
        $lines[] = '# TYPE tsena_orders_status_total counter';
        $lines[] = "tsena_orders_status_total{status=\"completed\"} {$successfulOrders}";
        $lines[] = "tsena_orders_status_total{status=\"failed\"} {$failedOrders}";

        $lines[] = '# HELP tsena_order_execution_time_ms_avg Duree moyenne de traitement des commandes en ms';
        $lines[] = '# TYPE tsena_order_execution_time_ms_avg gauge';
        $lines[] = sprintf('tsena_order_execution_time_ms_avg %.2f', $avgExecutionTime);

        // Metriques de stock par produit
        $lines[] = '# HELP tsena_product_stock_quantity Quantite actuelle en stock';
        $lines[] = '# TYPE tsena_product_stock_quantity gauge';
        $products = Product::all(['id', 'name', 'stock']);
        foreach ($products as $p) {
            $escapedName = addslashes($p->name);
            $lines[] = "tsena_product_stock_quantity{id=\"{$p->id}\",name=\"{$escapedName}\"} {$p->stock}";
        }

        return implode(PHP_EOL, $lines).PHP_EOL;
    }
}
