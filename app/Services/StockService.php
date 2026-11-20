<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SystemSetting;
use Exception;
use Illuminate\Support\Facades\DB;

class StockService
{
    protected TelemetryService $telemetry;

    public function __construct(TelemetryService $telemetry)
    {
        $this->telemetry = $telemetry;
    }

    // Verification de disponibilite du stock
    public function checkAvailability(array $items, string $traceId): array
    {
        // Interrupteur de panne forcee sur le service Stock
        if (SystemSetting::getVal('chaos_stock_down') === '1') {
            $this->telemetry->log('stock', 'error', 'Service Stock indisponible (panne simulee)', $traceId);
            throw new Exception('Service Stock indisponible (Code 503)');
        }

        $this->telemetry->log('stock', 'info', 'Debut de verification des stocks', $traceId, null, ['items_count' => count($items)]);

        $missing = [];
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if (! $product) {
                $missing[] = "Produit #{$item['product_id']} introuvable";

                continue;
            }

            if ($product->stock < $item['quantity']) {
                $missing[] = "Stock insuffisant pour {$product->name} (demande: {$item['quantity']}, dispo: {$product->stock})";
                $this->telemetry->log('stock', 'warning', "Stock insuffisant pour {$product->name}", $traceId, null, [
                    'product_id' => $product->id,
                    'requested' => $item['quantity'],
                    'available' => $product->stock,
                ]);
            }
        }

        $success = empty($missing);
        if ($success) {
            $this->telemetry->log('stock', 'info', 'Verification de stock reussie pour tous les articles', $traceId);
        }

        return [
            'available' => $success,
            'errors' => $missing,
        ];
    }

    // Decrementation atomique du stock
    public function decrementStock(array $items, string $traceId): void
    {
        DB::transaction(function () use ($items, $traceId) {
            foreach ($items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                if ($product) {
                    $oldStock = $product->stock;
                    $product->stock = max(0, $product->stock - $item['quantity']);
                    $product->save();

                    $this->telemetry->log('stock', 'info', "Stock mis a jour pour {$product->name}", $traceId, null, [
                        'product_id' => $product->id,
                        'old_stock' => $oldStock,
                        'new_stock' => $product->stock,
                    ]);
                }
            }
        });
    }

    // Reapprovisionnement de stock
    public function restock(int $productId, int $quantity, string $traceId): Product
    {
        $product = Product::findOrFail($productId);
        $product->stock += $quantity;
        $product->save();

        $this->telemetry->log('stock', 'info', "Reapprovisionnement effectue pour {$product->name}", $traceId, null, [
            'product_id' => $productId,
            'added' => $quantity,
            'new_stock' => $product->stock,
        ]);

        return $product;
    }
}
