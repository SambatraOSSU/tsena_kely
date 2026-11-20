<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Liste et historique des commandes
    public function index(Request $request)
    {
        $query = Order::with('items.product')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->paginate(15);
        $totalOrders = Order::count();
        $successfulOrders = Order::where('status', 'completed')->count();
        $failedOrders = Order::where('status', 'failed')->count();

        return view('orders.index', compact('orders', 'totalOrders', 'successfulOrders', 'failedOrders'));
    }

    // Detail d une commande specifique
    public function show(Order $order)
    {
        $order->load('items.product');

        return view('orders.show', compact('order'));
    }
}
