@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- En-tête de la Console d'Observabilité (Style NovaBank) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Console d'<span class="text-blue-600">Observabilité</span> & Chaos Engineering
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Plateforme P20 : Supervision temps réel, corrélation W3C (<code class="font-mono text-blue-600">trace_id</code>), scraping Prometheus et injection d'anomalies.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('telemetry.metrics') }}" target="_blank" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-mono font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-sm transition">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Prometheus (/metrics)</span>
            </a>
        </div>
    </div>

    <!-- 4 Cartes Métriques Clés (Style NovaBank Overview) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Commandes</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 font-mono">{{ $totalOrders }}</div>
            <div class="mt-2 text-xs text-slate-400">Trafic global analysé</div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Succès (200 OK)</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-emerald-600 font-mono">{{ $successfulOrders }}</div>
            <div class="mt-2 flex items-center gap-1 text-[11px] text-emerald-600 font-bold">
                @php
                    $rate = $totalOrders > 0 ? round(($successfulOrders / $totalOrders) * 100) : 100;
                @endphp
                <span>{{ $rate }}% de réussite</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-500">Pannes & Rejets (5xx)</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-rose-600 font-mono">{{ $failedOrders }}</div>
            <div class="mt-2 text-xs text-slate-400">Erreurs capturées</div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Latence Moyenne</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-blue-600 font-mono">{{ $avgLatency }} <span class="text-xs font-sans text-slate-400">ms</span></div>
            <div class="mt-2 text-xs text-slate-400">Temps d'exécution moyen</div>
        </div>
    </div>

    <!-- 2 Graphiques NovaBank (Style Image 1 & 2 : Courbe Latence + Barres Capsules) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Graphique 1 : Courbe de Latence (Style Image 2 "Transaction Value Overview") -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-card">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Évolution de la Latence d'Exécution</h2>
                    <p class="text-xs text-slate-400">Durée en millisecondes des dernières requêtes de commande.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-bold font-mono">
                    Temps Réel
                </span>
            </div>
            <div class="h-64 relative">
                <canvas id="latencyChart"></canvas>
            </div>
        </div>

        <!-- Graphique 2 : Niveaux de Stock (Style Image 1 "Billing Category Trends") -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-card">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Niveaux de Stocks Disponibles</h2>
                    <p class="text-xs text-slate-400">Jauges d'inventaire par référence de produit (en unités).</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold font-mono">
                    Service Stock
                </span>
            </div>
            <div class="h-64 relative">
                <canvas id="stockChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Section Injection de Chaos & Simulateur de Trafic (Style NovaBank Image 4) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Panneau Chaos Testing (Dev 1) -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-card">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-5">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <i data-lucide="sliders" class="w-4 h-4 text-amber-500"></i>
                        <span>Interrupteurs de Pannes (Chaos Engineering)</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Injectez des anomalies pour tester la résilience et l'observabilité.</p>
                </div>
            </div>

            <form action="{{ route('telemetry.settings') }}" method="POST" class="space-y-5">
                @csrf
                
                <!-- Latence artificielle -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold text-slate-700">Latence artificielle injectée</label>
                        <span id="latVal" class="text-xs font-mono font-extrabold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100">
                            {{ $settings['chaos_latency_ms'] }} ms
                        </span>
                    </div>
                    <input type="range" name="chaos_latency_ms" min="0" max="3000" step="100" 
                           value="{{ $settings['chaos_latency_ms'] }}" 
                           oninput="document.getElementById('latVal').innerText = this.value + ' ms'" 
                           class="w-full h-2 bg-slate-100 rounded-lg cursor-pointer accent-blue-600">
                    <span class="text-[11px] text-slate-400 block mt-1">Simule des lenteurs réseau ou des verrous SQL bloquants.</span>
                </div>

                <!-- Taux d'erreurs 500 -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold text-slate-700">Taux d'erreurs internes (HTTP 500)</label>
                        <span id="errVal" class="text-xs font-mono font-extrabold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-100">
                            {{ $settings['chaos_error_rate'] }} %
                        </span>
                    </div>
                    <input type="range" name="chaos_error_rate" min="0" max="100" step="5" 
                           value="{{ $settings['chaos_error_rate'] }}" 
                           oninput="document.getElementById('errVal').innerText = this.value + ' %'" 
                           class="w-full h-2 bg-slate-100 rounded-lg cursor-pointer accent-rose-600">
                    <span class="text-[11px] text-slate-400 block mt-1">Provoque des défaillances aléatoires dans le traitement.</span>
                </div>

                <!-- Toggles Services Coupés -->
                <div class="pt-3 border-t border-slate-100 space-y-3">
                    <label class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/60 cursor-pointer hover:bg-slate-100/60 transition">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Couper le Service Stock (Code 503)</span>
                            <span class="text-[11px] text-slate-400">Simule la chute du microservice d'inventaire.</span>
                        </div>
                        <input type="checkbox" name="chaos_stock_down" value="1" {{ $settings['chaos_stock_down'] ? 'checked' : '' }} 
                               class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                    </label>

                    <label class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/60 cursor-pointer hover:bg-slate-100/60 transition">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Couper le Service Commandes (Code 503)</span>
                            <span class="text-[11px] text-slate-400">Simule l'indisponibilité de la base PostgreSQL.</span>
                        </div>
                        <input type="checkbox" name="chaos_orders_down" value="1" {{ $settings['chaos_orders_down'] ? 'checked' : '' }} 
                               class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                    </label>
                </div>

                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-slate-900 hover:bg-blue-600 text-white rounded-xl text-xs font-bold shadow-sm transition active:scale-98">
                    Appliquer les réglages de panne
                </button>
            </form>
        </div>

        <!-- Simulateur de Trafic (Dev 1) -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-card flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <i data-lucide="zap" class="w-4 h-4 text-blue-600"></i>
                        <span>Générateur de Trafic Simulé</span>
                    </h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">
                        Injection Continue
                    </span>
                </div>
                
                <p class="text-xs text-slate-500 leading-relaxed mb-4">
                    Génère une rafale de paniers aléatoires pour peupler immédiatement Prometheus, Grafana et Tempo avec des métriques réelles.
                </p>

                <form action="{{ route('telemetry.simulate') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nombre de requêtes d'achats à injecter</label>
                        <select name="count" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="5">5 requêtes de commandes</option>
                            <option value="10" selected>10 requêtes de commandes (Recommandé)</option>
                            <option value="25">25 requêtes intensives</option>
                            <option value="50">50 requêtes massives</option>
                        </select>
                    </div>

                    <button type="submit" 
                            class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white rounded-xl text-xs font-bold shadow-glow-brand flex items-center justify-center gap-2 transition">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        <span>Lancer la simulation de trafic</span>
                    </button>
                </form>
            </div>

            <!-- Commande CLI -->
            <div class="mt-6 p-4 bg-slate-50 rounded-xl border border-slate-200/60 font-mono text-xs">
                <div class="flex items-center gap-2 text-slate-700 font-bold mb-1">
                    <i data-lucide="terminal" class="w-3.5 h-3.5 text-blue-600"></i>
                    <span>Commande CLI pour trafic en continu :</span>
                </div>
                <div class="bg-slate-900 text-emerald-400 p-2.5 rounded-lg text-[11px] overflow-x-auto select-all">
                    php artisan tsena:simulate --requests=100 --delay=200
                </div>
            </div>
        </div>

    </div>

    <!-- Tableau des Logs Structurés JSON & Traces (Dev 3 & RSI 2) -->
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-card overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Logs Structurés JSON & Traces (Loki / Tempo)</h2>
                <p class="text-xs text-slate-400 mt-0.5">Explorez les événements système reliés par le TraceContext W3C.</p>
            </div>

            <!-- Formulaire de filtrage -->
            <form action="{{ route('telemetry.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <input type="text" name="trace_id" value="{{ request('trace_id') }}" 
                       placeholder="Filtrer par Trace ID..." 
                       class="text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 w-48 sm:w-60 focus:outline-none focus:ring-2 focus:ring-blue-500">

                <select name="service" class="text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                    <option value="">Tous les services</option>
                    <option value="accueil" {{ request('service') === 'accueil' ? 'selected' : '' }}>Accueil</option>
                    <option value="stock" {{ request('service') === 'stock' ? 'selected' : '' }}>Stock</option>
                    <option value="commandes" {{ request('service') === 'commandes' ? 'selected' : '' }}>Commandes</option>
                </select>

                <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-blue-600 text-white rounded-xl text-xs font-bold transition shadow-sm">
                    Filtrer
                </button>

                @if(request('trace_id') || request('service'))
                    <a href="{{ route('telemetry.dashboard') }}" class="text-xs font-bold text-slate-400 hover:text-slate-700 px-2">
                        Réinitialiser
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px] font-mono">
                        <th class="py-3.5 px-5">Heure</th>
                        <th class="py-3.5 px-4">Service</th>
                        <th class="py-3.5 px-4">Niveau</th>
                        <th class="py-3.5 px-4">Événement / Message</th>
                        <th class="py-3.5 px-4">Trace ID W3C</th>
                        <th class="py-3.5 px-5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-5 text-slate-400 text-[11px]">
                                {{ $log->created_at->format('H:i:s.u') }}
                            </td>
                            
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $log->service === 'accueil' ? 'bg-blue-50 text-blue-700 border border-blue-200' : ($log->service === 'stock' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-purple-50 text-purple-700 border border-purple-200') }}">
                                    {{ $log->service }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4">
                                @if($log->level === 'error')
                                    <span class="inline-flex items-center gap-1 text-rose-600 font-bold text-[11px]">
                                        <i data-lucide="x" class="w-3 h-3"></i> ERROR
                                    </span>
                                @elseif($log->level === 'warning')
                                    <span class="inline-flex items-center gap-1 text-amber-600 font-bold text-[11px]">
                                        <i data-lucide="alert-triangle" class="w-3 h-3"></i> WARN
                                    </span>
                                @else
                                    <span class="text-slate-500 text-[11px]">INFO</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 font-sans text-slate-800 font-medium max-w-sm truncate">
                                {{ $log->message }}
                            </td>

                            <td class="py-3.5 px-4 text-blue-600 truncate max-w-[150px]">
                                <a href="{{ route('telemetry.trace', $log->trace_id) }}" class="underline hover:text-blue-800">
                                    {{ $log->trace_id }}
                                </a>
                            </td>

                            <td class="py-3.5 px-5 text-right">
                                <a href="{{ route('telemetry.trace', $log->trace_id) }}" 
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 text-[11px] font-sans font-bold transition">
                                    <span>Explorer</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 font-sans">
                                Aucun événement journalisé pour ce filtre. Lancez une simulation pour peupler les traces !
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Initialisation de Chart.js (Style NovaBank Image 1 & 2) -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Données du graphique de latence
        const latencyLabels = [
            @foreach($recentOrders as $ro)
                "{{ substr($ro->order_number, -4) }}",
            @endforeach
        ];
        const latencyData = [
            @foreach($recentOrders as $ro)
                {{ $ro->execution_time_ms }},
            @endforeach
        ];

        // S'il n'y a pas assez de commandes, données d'exemple fluides
        const finalLabels = latencyLabels.length > 0 ? latencyLabels : ['CMD-1', 'CMD-2', 'CMD-3', 'CMD-4', 'CMD-5', 'CMD-6'];
        const finalData = latencyData.length > 0 ? latencyData : [12, 15, 8, 24, 18, 14];

        const ctxLatency = document.getElementById('latencyChart')?.getContext('2d');
        if (ctxLatency) {
            new Chart(ctxLatency, {
                type: 'line',
                data: {
                    labels: finalLabels,
                    datasets: [{
                        label: 'Latence (ms)',
                        data: finalData,
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        fill: true,
                        tension: 0.4, // Courbe fluide comme sur NovaBank Image 2
                        pointBackgroundColor: '#2563EB',
                        pointBorderColor: '#FFFFFF',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'JetBrains Mono', size: 10 } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#F1F5F9' },
                            ticks: { 
                                font: { family: 'JetBrains Mono', size: 10 },
                                callback: val => val + ' ms'
                            }
                        }
                    }
                }
            });
        }

        // Données du graphique de stock (Style NovaBank Barres arrondies Image 1)
        const stockLabels = [
            @foreach($stockProducts as $sp)
                "{{ addslashes($sp->name) }}",
            @endforeach
        ];
        const stockData = [
            @foreach($stockProducts as $sp)
                {{ $sp->stock }},
            @endforeach
        ];

        const ctxStock = document.getElementById('stockChart')?.getContext('2d');
        if (ctxStock) {
            new Chart(ctxStock, {
                type: 'bar',
                data: {
                    labels: stockLabels.map(l => l.length > 12 ? l.substring(0, 12) + '...' : l),
                    datasets: [{
                        label: 'Quantité en Stock',
                        data: stockData,
                        backgroundColor: '#3B82F6',
                        borderRadius: 8, // Capsule rounded bars
                        barThickness: 20,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#F1F5F9' },
                            ticks: { font: { family: 'JetBrains Mono', size: 10 } }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
