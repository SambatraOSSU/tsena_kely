<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockService;
use App\Services\TelemetryService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected StockService $stockService;

    protected TelemetryService $telemetry;

    public function __construct(StockService $stockService, TelemetryService $telemetry)
    {
        $this->stockService = $stockService;
        $this->telemetry = $telemetry;
    }

    // Tableau de bord de gestion des stocks
    public function index()
    {
        $products = Product::orderBy('stock', 'asc')->get();
        $totalItems = $products->sum('stock');
        $lowStockCount = $products->where('stock', '<=', 5)->count();
        $outOfStockCount = $products->where('stock', 0)->count();

        return view('stock.index', compact('products', 'totalItems', 'lowStockCount', 'outOfStockCount'));
    }

    // Reapprovisionnement rapide d un produit
    public function restock(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:1000',
        ]);

        $traceId = TelemetryService::generateTraceId();
        $this->stockService->restock($product->id, (int) $request->input('quantity'), $traceId);

        return back()->with('success', "Stock de {$product->name} mis a jour.");
    }
}
