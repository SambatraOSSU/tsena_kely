@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Bouton Retour & Action Trace -->
    <div class="flex items-center justify-between">
        <a href="{{ route('orders.index') }}" 
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Retour aux commandes</span>
        </a>

        <a href="{{ route('telemetry.trace', $order->trace_id) }}" 
           class="inline-flex items-center gap-1.5 text-xs bg-blue-50 text-blue-700 border border-blue-200 px-3.5 py-1.5 rounded-xl font-bold hover:bg-blue-100 transition shadow-sm">
            <i data-lucide="activity" class="w-4 h-4 text-blue-600"></i>
            <span>Examiner la trace OpenTelemetry</span>
        </a>
    </div>

    <!-- Carte Principale Transaction (Style NovaBank Image 1 & 2) -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-8 shadow-card space-y-6">
        
        <div class="flex items-center justify-between border-b border-slate-100 pb-5">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Référence Transaction</span>
                <h1 class="text-2xl font-extrabold font-mono text-slate-900 mt-0.5">{{ $order->order_number }}</h1>
            </div>

            <div>
                @if($order->status === 'completed')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Succès
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Échec (Chaos / Rejet)
                    </span>
                @endif
            </div>
        </div>

        @if($order->status === 'failed')
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 flex-shrink-0"></i>
                <div>
                    <span class="font-bold">Motif de l'anomalie :</span> {{ $order->failure_reason }}
                </div>
            </div>
        @endif

        <!-- Grille des Métadonnées -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200/60 text-xs">
            <div>
                <span class="text-slate-400 block mb-1 font-medium">Nom Client</span>
                <span class="font-bold text-slate-900">{{ $order->customer_name }}</span>
            </div>
            <div>
                <span class="text-slate-400 block mb-1 font-medium">Contact</span>
                <span class="text-slate-700 font-mono">{{ $order->customer_email ?: ($order->customer_phone ?: '-') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block mb-1 font-medium">Date & Heure</span>
                <span class="text-slate-700 font-mono">{{ $order->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block mb-1 font-medium">Latence Traitement</span>
                <span class="font-mono font-bold text-blue-600">{{ $order->execution_time_ms }} ms</span>
            </div>
        </div>

        <!-- Articles Inclus -->
        <div class="pt-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Articles commandés</h3>
            <div class="divide-y divide-slate-100 border border-slate-200/80 rounded-xl overflow-hidden bg-white">
                @forelse($order->items as $item)
                    <div class="p-3.5 flex items-center justify-between text-xs hover:bg-slate-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0">
                                <img src="{{ $item->product->image ?: 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=100' }}" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <div class="font-bold text-slate-900">{{ $item->product->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">
                                    {{ number_format($item->unit_price, 0, ',', ' ') }} Ar × {{ $item->quantity }}
                                </div>
                            </div>
                        </div>
                        <span class="font-mono font-bold text-slate-900">{{ number_format($item->total_price, 0, ',', ' ') }} Ar</span>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-400">
                        Aucun article n'a pu être réservé pour cette tentative.
                    </div>
                @endforelse
            </div>

            <!-- Total Facturé -->
            <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200/60 flex justify-between items-center text-sm font-extrabold text-slate-900">
                <span>Total de la Transaction</span>
                <span class="font-mono text-lg text-blue-600">{{ number_format($order->total_amount, 0, ',', ' ') }} Ar</span>
            </div>
        </div>

    </div>

</div>
@endsection
