@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- En-tête Service Stock (Style NovaBank Cards Management) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Service <span class="text-blue-600">Stock</span> & Inventaire Atomique
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Composant Dev 3 : Gestion atomique, verrouillage pessimiste des lignes SQL (<code class="font-mono text-blue-600">lockForUpdate</code>) et logs JSON structurés.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Service Stock En Ligne
            </span>
        </div>
    </div>

    <!-- 4 Cartes d'indicateurs de stock (Style NovaBank Usage Control) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Références</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900">{{ $products->count() }}</div>
            <div class="mt-2 text-xs text-slate-400">Articles catalogués</div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Quantité Globale</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="box" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 font-mono">{{ $totalItems }}</div>
            <div class="mt-2 flex items-center gap-1 text-[11px] text-emerald-600 font-bold">
                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                <span>En rayon ou entrepôt</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-500">Alertes Stock (≤ 5)</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-amber-600 font-mono">{{ $lowStockCount }}</div>
            <div class="mt-2 text-[11px] text-slate-400">À réapprovisionner rapidement</div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-500">Ruptures Sèches</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="slash" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-rose-600 font-mono">{{ $outOfStockCount }}</div>
            <div class="mt-2 text-[11px] text-slate-400">Commandes bloquées si demandé</div>
        </div>
    </div>

    <!-- Tableau Moderne de Gestion des Stocks -->
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-card overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Inventaire et Niveaux de Remplissage</h2>
                <p class="text-xs text-slate-400 mt-0.5">Suivi en direct des jauges d'inventaire et réapprovisionnement instantané.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-5">Produit</th>
                        <th class="py-3.5 px-4">Catégorie</th>
                        <th class="py-3.5 px-4">Prix</th>
                        <th class="py-3.5 px-4 w-44">Jauge de Stock</th>
                        <th class="py-3.5 px-4">Quantité</th>
                        <th class="py-3.5 px-4">Statut</th>
                        <th class="py-3.5 px-5 text-right">Réapprovisionner</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($products as $product)
                        @php
                            $maxStockGoal = 50;
                            $pct = min(100, round(($product->stock / $maxStockGoal) * 100));
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Produit avec Image -->
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 overflow-hidden flex-shrink-0 border border-slate-200/60">
                                        <img src="{{ $product->image ?: 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=200' }}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs">{{ $product->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $product->slug }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Catégorie -->
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-semibold text-[11px]">
                                    {{ $product->category }}
                                </span>
                            </td>

                            <!-- Prix -->
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                                {{ number_format($product->price, 0, ',', ' ') }} Ar
                            </td>

                            <!-- Jauge Capsule Moderne (Style NovaBank Image 4) -->
                            <td class="py-3.5 px-4">
                                <div class="w-full">
                                    <div class="flex justify-between text-[10px] text-slate-400 mb-1 font-mono">
                                        <span>{{ $product->stock }} / {{ $maxStockGoal }}</span>
                                        <span>{{ $pct }}%</span>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200/60">
                                        <div class="h-full rounded-full transition-all duration-500
                                            {{ $product->stock > 15 ? 'bg-blue-600' : ($product->stock > 0 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Quantité numérique -->
                            <td class="py-3.5 px-4 font-mono font-extrabold text-sm
                                {{ $product->stock <= 0 ? 'text-rose-600' : ($product->stock <= 5 ? 'text-amber-600' : 'text-slate-900') }}">
                                {{ $product->stock }}
                            </td>

                            <!-- Statut Badge -->
                            <td class="py-3.5 px-4">
                                @if($product->stock > 5)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px] border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Optimal
                                    </span>
                                @elseif($product->stock > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 font-bold text-[10px] border border-amber-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Critique
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 font-bold text-[10px] border border-rose-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Rupture
                                    </span>
                                @endif
                            </td>

                            <!-- Formulaire de réapprovisionnement -->
                            <td class="py-3.5 px-5 text-right">
                                <form action="{{ route('stock.restock', $product->id) }}" method="POST" class="inline-flex items-center gap-1.5">
                                    @csrf
                                    <input type="number" name="quantity" value="10" min="1" max="500" 
                                           class="w-16 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs font-mono font-bold text-center focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <button type="submit" 
                                            class="px-3 py-1 bg-slate-900 hover:bg-blue-600 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm active:scale-95">
                                        <i data-lucide="plus" class="w-3 h-3"></i>
                                        <span>Ajouter</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
