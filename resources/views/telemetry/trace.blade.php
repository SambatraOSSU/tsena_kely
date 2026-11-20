@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- En-tête Navigation Trace -->
    <div class="flex items-center justify-between">
        <a href="{{ route('telemetry.dashboard') }}" 
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Retour à la console de diagnostic</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="text-xs font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded-full flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span>TraceContext W3C</span>
            </span>
        </div>
    </div>

    <!-- Carte Résumé de la Trace (Style NovaBank Image 1 & 2) -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-8 shadow-card">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                    Identifiant Global de Trace (Trace ID)
                </span>
                <div class="flex items-center gap-3">
                    <h1 class="text-lg sm:text-xl font-mono font-extrabold text-blue-600 break-all select-all">
                        {{ $traceId }}
                    </h1>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $traceId }}'); showToast('Trace ID copié dans le presse-papier !');" 
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition" title="Copier le Trace ID">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            @if($order)
                <div class="text-left md:text-right">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Statut Transaction</span>
                    @if($order->status === 'completed')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Succès ({{ $order->execution_time_ms }} ms)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            Échec ({{ $order->execution_time_ms }} ms)
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <!-- Visualisation Distribuée du Pipeline (Accueil ➔ Stock ➔ Commandes) -->
        <div class="mt-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">
                Parcours Distribué de la Requête (Pipeline Microservices)
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                
                <!-- Service Accueil -->
                <div class="p-4 rounded-xl border border-blue-200/80 bg-blue-50/50">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-extrabold text-xs text-blue-900 flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                            Service Accueil
                        </span>
                        <span class="text-[10px] font-mono font-bold text-blue-700 bg-white px-2 py-0.5 rounded-md border border-blue-100">Dev 1</span>
                    </div>
                    <p class="text-[11px] text-blue-800 leading-relaxed">
                        Réception de la requête HTTP client, génération de la trace W3C et amorçage du workflow.
                    </p>
                </div>

                <!-- Service Stock -->
                <div class="p-4 rounded-xl border border-amber-200/80 bg-amber-50/50">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-extrabold text-xs text-amber-900 flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-full bg-amber-600 text-white flex items-center justify-center text-[10px] font-bold">2</span>
                            Service Stock
                        </span>
                        <span class="text-[10px] font-mono font-bold text-amber-700 bg-white px-2 py-0.5 rounded-md border border-amber-100">Dev 3</span>
                    </div>
                    <p class="text-[11px] text-amber-800 leading-relaxed">
                        Vérification de la disponibilité et décrémentation sous verrou atomique (<code class="font-mono text-amber-900">lockForUpdate</code>).
                    </p>
                </div>

                <!-- Service Commandes -->
                <div class="p-4 rounded-xl border border-purple-200/80 bg-purple-50/50">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-extrabold text-xs text-purple-900 flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-full bg-purple-600 text-white flex items-center justify-center text-[10px] font-bold">3</span>
                            Service Commandes
                        </span>
                        <span class="text-[10px] font-mono font-bold text-purple-700 bg-white px-2 py-0.5 rounded-md border border-purple-100">Dev 2</span>
                    </div>
                    <p class="text-[11px] text-purple-800 leading-relaxed">
                        Persistance dans la base relationnelle, enregistrement de la latence globale et validation du statut.
                    </p>
                </div>

            </div>
        </div>
    </div>

    <!-- Chronologie des Spans & Logs JSON (Tempo / Loki) -->
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-card overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Spans et Événements Associés à cette Trace</h2>
                <p class="text-xs text-slate-400 mt-0.5">Chronologie complète des appels inter-services collectés par Loki et Tempo.</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                {{ $logs->count() }} spans
            </span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($logs as $idx => $l)
                <div class="p-5 hover:bg-slate-50/60 transition space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-2.5 font-mono">
                            <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-[11px]">
                                #{{ $idx + 1 }}
                            </span>
                            
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $l->service === 'accueil' ? 'bg-blue-50 text-blue-700 border border-blue-200' : ($l->service === 'stock' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-purple-50 text-purple-700 border border-purple-200') }}">
                                {{ $l->service }}
                            </span>

                            @if($l->level === 'error')
                                <span class="text-rose-600 font-bold uppercase text-[10px] flex items-center gap-1">
                                    <i data-lucide="x" class="w-3 h-3"></i> ERROR
                                </span>
                            @elseif($l->level === 'warning')
                                <span class="text-amber-600 font-bold uppercase text-[10px] flex items-center gap-1">
                                    <i data-lucide="alert-triangle" class="w-3 h-3"></i> WARN
                                </span>
                            @else
                                <span class="text-slate-500 uppercase text-[10px]">INFO</span>
                            @endif
                        </div>

                        <span class="text-slate-400 font-mono text-[11px]">
                            {{ $l->created_at->format('H:i:s.u') }}
                        </span>
                    </div>

                    <div class="font-bold text-slate-800 text-xs sm:text-sm">
                        {{ $l->message }}
                    </div>

                    <!-- Bloc Code JSON Structuré -->
                    <div class="relative bg-slate-900 text-slate-200 rounded-xl p-4 font-mono text-[11px] overflow-x-auto shadow-inner">
                        <pre><code>{{ json_encode([
                            'timestamp' => $l->created_at->toISOString(),
                            'trace_id'  => $l->trace_id,
                            'span_id'   => $l->span_id,
                            'service'   => $l->service,
                            'level'     => $l->level,
                            'message'   => $l->message,
                            'context'   => $l->context ?? [],
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-slate-400 text-xs">
                    Aucun span ni log enregistré pour cette trace.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
