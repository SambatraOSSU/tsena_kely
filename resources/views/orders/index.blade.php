@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- En-tête Service Commandes (Style NovaBank Transaction Activity Center) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Centre des <span class="text-blue-600">Commandes</span> & Transactions
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Composant Dev 2 : Persistance relationnelle, journalisation des latences et association systématique au Trace ID.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                <i data-lucide="database" class="w-3.5 h-3.5 text-blue-600"></i>
                <span>Base Relationnelle Connectée</span>
            </span>
        </div>
    </div>

    <!-- 4 Cartes Métriques (Style NovaBank Image 2) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Commandes</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 font-mono">{{ $totalOrders }}</div>
            <div class="mt-2 text-xs text-slate-400">Requêtes traitées</div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Succès (Validées)</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-emerald-600 font-mono">{{ $successfulOrders }}</div>
            <div class="mt-2 flex items-center gap-1 text-[11px] text-emerald-600 font-bold">
                @php
                    $successRate = $totalOrders > 0 ? round(($successfulOrders / $totalOrders) * 100) : 100;
                @endphp
                <span>{{ $successRate }}% taux de succès</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-500">Échecs (Rejets / Chaos)</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-rose-600 font-mono">{{ $failedOrders }}</div>
            <div class="mt-2 text-[11px] text-slate-400">Pannes ou ruptures capturées</div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Volume Financier</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                </div>
            </div>
            @php
                $totalRevenue = \App\Models\Order::where('status', 'completed')->sum('total_amount');
            @endphp
            <div class="text-xl sm:text-2xl font-extrabold text-slate-900 font-mono">
                {{ number_format($totalRevenue, 0, ',', ' ') }} Ar
            </div>
            <div class="mt-2 text-xs text-slate-400">Chiffre d'affaires valide</div>
        </div>
    </div>

    <!-- Barre d'actions & Filtres (Style NovaBank Image 2) -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 sm:p-4 shadow-card flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-1.5 w-full sm:w-auto">
            <a href="{{ route('orders.index') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ !request('status') ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:text-slate-900' }}">
                Toutes les transactions
            </a>
            <a href="{{ route('orders.index', ['status' => 'completed']) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ request('status') === 'completed' ? 'bg-emerald-600 text-white shadow-sm font-bold' : 'bg-slate-100 text-slate-600 hover:text-slate-900' }}">
                Succès
            </a>
            <a href="{{ route('orders.index', ['status' => 'failed']) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ request('status') === 'failed' ? 'bg-rose-600 text-white shadow-sm font-bold' : 'bg-slate-100 text-slate-600 hover:text-slate-900' }}">
                Échecs
            </a>
        </div>

        <div class="text-xs text-slate-400 font-mono">
            Page {{ $orders->currentPage() }} sur {{ $orders->lastPage() ?: 1 }}
        </div>
    </div>

    <!-- Tableau Moderne des Commandes -->
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-5">Réf. Commande</th>
                        <th class="py-3.5 px-4">Client</th>
                        <th class="py-3.5 px-4">Date / Heure</th>
                        <th class="py-3.5 px-4">Montant</th>
                        <th class="py-3.5 px-4">Statut</th>
                        <th class="py-3.5 px-4">Latence</th>
                        <th class="py-3.5 px-4">Trace ID (Tempo / Loki)</th>
                        <th class="py-3.5 px-5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-sans">
                    @forelse($orders as $ord)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Numéro de commande -->
                            <td class="py-3.5 px-5 font-mono font-bold text-slate-900">
                                {{ $ord->order_number }}
                            </td>

                            <!-- Client -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800">{{ $ord->customer_name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $ord->customer_email ?: '-' }}</div>
                            </td>

                            <!-- Date -->
                            <td class="py-3.5 px-4 text-slate-500 font-mono">
                                {{ $ord->created_at->format('d/m/Y H:i:s') }}
                            </td>

                            <!-- Montant -->
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                {{ number_format($ord->total_amount, 0, ',', ' ') }} Ar
                            </td>

                            <!-- Statut Pill -->
                            <td class="py-3.5 px-4">
                                @if($ord->status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px] border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Succès
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 font-bold text-[10px] border border-rose-100" 
                                          title="{{ $ord->failure_reason }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Échec
                                    </span>
                                @endif
                            </td>

                            <!-- Latence -->
                            <td class="py-3.5 px-4 font-mono font-semibold text-blue-600">
                                {{ $ord->execution_time_ms }} ms
                            </td>

                            <!-- Trace ID -->
                            <td class="py-3.5 px-4 font-mono">
                                <a href="{{ route('telemetry.trace', $ord->trace_id) }}" 
                                   class="text-blue-600 hover:text-blue-800 underline truncate block max-w-[150px] font-medium"
                                   title="{{ $ord->trace_id }}">
                                    {{ $ord->trace_id }}
                                </a>
                            </td>

                            <!-- Action -->
                            <td class="py-3.5 px-5 text-right">
                                <a href="{{ route('orders.show', $ord->id) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 text-xs font-bold transition shadow-sm">
                                    <span>Détail</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <p>Aucune transaction enregistrée pour ce filtre.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-100 flex justify-between items-center bg-slate-50/50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
