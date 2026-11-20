@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Carte Ticket de Commande (Style NovaBank Transaction Detail) -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-8 shadow-card overflow-hidden">
        
        @if($order->status === 'completed')
            <!-- En-tête Succès -->
            <div class="text-center pb-6 border-b border-slate-100">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center mb-4 shadow-sm border border-emerald-100">
                    <i data-lucide="check-circle-2" class="w-8 h-8"></i>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Commande Reussie
                </span>
                <h1 class="text-2xl font-extrabold text-slate-900 mt-1">Merci pour votre commande !</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    La transaction a été persistée avec succès et les stocks ont été mis à jour de façon atomique.
                </p>
            </div>
        @else
            <!-- En-tête Échec (Chaos ou rupture) -->
            <div class="text-center pb-6 border-b border-slate-100">
                <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-600 mx-auto flex items-center justify-center mb-4 shadow-sm border border-rose-100">
                    <i data-lucide="alert-octagon" class="w-8 h-8"></i>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 mb-2">
                    Échec de Traitement (Chaos / Indisponible)
                </span>
                <h1 class="text-2xl font-extrabold text-slate-900 mt-1">La commande n'a pas pu aboutir</h1>
                <p class="text-xs sm:text-sm text-rose-600 mt-1 font-semibold">{{ $order->failure_reason }}</p>
            </div>
        @endif

        <!-- Bloc Détails d'Observabilité & TraceContext W3C -->
        <div class="mt-6 bg-slate-50 rounded-2xl border border-slate-200/80 p-5 space-y-3">
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-500">Numéro de Commande</span>
                <span class="font-mono font-extrabold text-slate-900 text-sm">{{ $order->order_number }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-500">Client Facturé</span>
                <span class="font-bold text-slate-800">{{ $order->customer_name }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-500">Durée d'Exécution (Latence)</span>
                <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-lg border border-blue-100">
                    {{ $order->execution_time_ms }} ms
                </span>
            </div>
            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200/60">
                <span class="font-bold text-slate-500">Trace ID (OpenTelemetry)</span>
                <a href="{{ route('telemetry.trace', $order->trace_id) }}" 
                   class="font-mono text-blue-600 hover:text-blue-800 underline truncate max-w-[220px] font-medium"
                   title="{{ $order->trace_id }}">
                    {{ $order->trace_id }}
                </a>
            </div>
        </div>

        <!-- Récapitulatif des Articles -->
        @if($order->items->isNotEmpty())
            <div class="mt-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Articles commandés</h3>
                <div class="divide-y divide-slate-100 border-t border-b border-slate-100">
                    @foreach($order->items as $item)
                        <div class="py-3 flex justify-between items-center text-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0">
                                    <img src="{{ $item->product->image ?: 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=100' }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800">{{ $item->product->name }}</span>
                                    <span class="text-slate-400 block text-[11px]">{{ number_format($item->unit_price, 0, ',', ' ') }} Ar × {{ $item->quantity }}</span>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-slate-900">{{ number_format($item->total_price, 0, ',', ' ') }} Ar</span>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 flex justify-between items-center text-sm font-extrabold text-slate-900">
                    <span>Total Facturé</span>
                    <span class="font-mono text-lg text-blue-600">{{ number_format($order->total_amount, 0, ',', ' ') }} Ar</span>
                </div>
            </div>
        @endif

        <!-- Boutons d'Action -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('shop.index') }}" 
               class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold shadow-glow-brand flex items-center justify-center gap-2 transition">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                <span>Retourner à la boutique</span>
            </a>

            <a href="{{ route('telemetry.trace', $order->trace_id) }}" 
               class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold flex items-center justify-center gap-2 transition">
                <i data-lucide="activity" class="w-4 h-4 text-blue-600"></i>
                <span>Examiner la trace & les spans</span>
            </a>
        </div>
    </div>
</div>

<!-- Vidage automatique du panier lors de la confirmation réussie -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        @if($order->status === 'completed')
            // Vider le panier dans localStorage pour éviter qu'il reste plein
            localStorage.removeItem('tsena_cart');
            if (typeof clearCart === 'function') {
                clearCart();
            }
        @endif
    });
</script>
@endsection
