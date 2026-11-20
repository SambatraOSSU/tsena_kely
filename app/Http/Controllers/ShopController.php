<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\OrderService;
use App\Services\TelemetryService;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    protected OrderService $orderService;

    protected TelemetryService $telemetry;

    public function __construct(OrderService $orderService, TelemetryService $telemetry)
    {
        $this->orderService = $orderService;
        $this->telemetry = $telemetry;
    }

    // Affichage de la vitrine et du catalogue de produits
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $term = $request->input('search');
            $operator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($term, $operator) {
                $q->where('name', $operator, "%{$term}%")
                    ->orWhere('description', $operator, "%{$term}%");
            });
        }

        $products = $query->orderBy('name')->get();
        $categories = Product::select('category')->distinct()->pluck('category');

        return view('shop.index', compact('products', 'categories'));
    }

    // Passage de commande par le client
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_email' => 'nullable|email|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'cart_items' => 'required|string',
        ]);

        $rawItems = json_decode($validated['cart_items'], true);
        if (! is_array($rawItems) || empty($rawItems)) {
            return back()->with('error', 'Le panier est vide ou invalide.');
        }

        $items = [];
        foreach ($rawItems as $it) {
            if (isset($it['id']) && isset($it['qty']) && (int) $it['qty'] > 0) {
                $items[] = [
                    'product_id' => (int) $it['id'],
                    'quantity' => (int) $it['qty'],
                ];
            }
        }

        if (empty($items)) {
            return back()->with('error', 'Aucun article valide selectionne.');
        }

        $traceId = TelemetryService::generateTraceId();
        $this->telemetry->log('accueil', 'info', 'Requete de commande recue depuis l interface web', $traceId, null, [
            'customer' => $validated['customer_name'],
            'client_ip' => $request->ip(),
        ]);

        $order = $this->orderService->processOrder([
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'items' => $items,
        ], $traceId);

        return view('shop.confirmation', compact('order'));
    }
}
