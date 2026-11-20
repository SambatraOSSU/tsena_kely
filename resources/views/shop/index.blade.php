@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- En-tête de la Boutique (Style NovaBank) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Boutique <span class="text-blue-600">Tsena Kely</span> & Catalogue
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Point d'entrée du Service Accueil : réception des achats, vérification atomique et génération de la trace distribuée.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('telemetry.dashboard') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition">
                <i data-lucide="activity" class="w-4 h-4 text-blue-600"></i>
                <span>Console Diagnostic</span>
            </a>
            
            <form action="{{ route('telemetry.simulate') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="count" value="5">
                <button type="submit" 
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-glow-brand transition active:scale-95">
                    <i data-lucide="zap" class="w-4 h-4"></i>
                    <span>Simuler 5 Achats</span>
                </button>
            </form>
        </div>
    </div>

    <!-- 4 Cartes d'indicateurs rapides (Style NovaBank Overview) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Catalogue</span>
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="package" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900">{{ $products->count() }}</div>
            <div class="mt-2 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-0.5 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                    <i data-lucide="arrow-up-right" class="w-3 h-3"></i> 100% en ligne
                </span>
                <span class="text-[11px] text-slate-400">Articles actifs</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Stock Total</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900">{{ $products->sum('stock') }}</div>
            <div class="mt-2 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-0.5 text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">
                    Disponibles
                </span>
                <span class="text-[11px] text-slate-400">Unités prêtes</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Observabilité</span>
                <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="git-branch" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 font-mono">W3C</div>
            <div class="mt-2 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-0.5 text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-100">
                    OpenTelemetry
                </span>
                <span class="text-[11px] text-slate-400">Trace Context</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-card hover:shadow-card-hover transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Service Accueil</span>
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-emerald-600">Actif</div>
            <div class="mt-2 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-0.5 text-[10px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-full font-mono">
                    HTTP 200
                </span>
                <span class="text-[11px] text-slate-400">Santé nominale</span>
            </div>
        </div>
    </div>

    <!-- Barre d'outils : Filtres & Recherche Instantanée -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 sm:p-4 shadow-card flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <!-- Onglets Catégories Pills -->
        <div class="flex flex-wrap items-center gap-1.5">
            <a href="{{ route('shop.index') }}" 
               class="category-btn px-4 py-2 rounded-xl text-xs font-bold transition {{ !request('category') ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100/90 text-slate-600 hover:text-slate-900 hover:bg-slate-200/70' }}">
                Tous les produits
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('shop.index', ['category' => $cat]) }}" 
                   class="category-btn px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ request('category') === $cat ? 'bg-blue-600 text-white shadow-sm font-bold' : 'bg-slate-100/90 text-slate-600 hover:text-slate-900 hover:bg-slate-200/70' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>

        <!-- Champ de recherche instantanée (avec icône) -->
        <div class="relative w-full md:w-72">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 transform -translate-y-1/2"></i>
            <input type="text" id="liveSearchInput" placeholder="Recherche rapide..." 
                   value="{{ request('search') }}"
                   class="w-full text-xs pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
        </div>
    </div>

    <!-- Grille Moderne des Produits (Cartes avec visuels) -->
    <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @forelse($products as $product)
            <div class="product-card bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-card hover:shadow-card-hover hover:-translate-y-1 transition duration-300 flex flex-col justify-between group"
                 data-name="{{ strtolower($product->name) }}"
                 data-category="{{ strtolower($product->category) }}">
                
                <!-- Zone Visuel & Badges Flottants -->
                <div class="relative h-44 w-full bg-slate-100 overflow-hidden">
                    <img src="{{ $product->image ?: 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=600' }}" 
                         alt="{{ $product->name }}" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    
                    <!-- Badge Catégorie -->
                    <div class="absolute top-3 left-3">
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-white/95 backdrop-blur-sm text-slate-700 shadow-sm border border-slate-200/50">
                            {{ $product->category }}
                        </span>
                    </div>

                    <!-- Badge Statut Stock -->
                    <div class="absolute top-3 right-3">
                        @if($product->stock > 5)
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-500 text-white shadow-sm flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                Stock: {{ $product->stock }}
                            </span>
                        @elseif($product->stock > 0)
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-amber-500 text-white shadow-sm flex items-center gap-1">
                                Faible ({{ $product->stock }})
                            </span>
                        @else
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-rose-600 text-white shadow-sm">
                                Épuisé
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Informations Produit -->
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm mb-1.5 group-hover:text-blue-600 transition">
                            {{ $product->name }}
                        </h3>
                        <p class="text-xs text-slate-500 leading-relaxed line-clamp-2 mb-4">
                            {{ $product->description }}
                        </p>
                    </div>

                    <!-- Prix & Action Ajouter -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Prix unitaire</span>
                            <span class="text-base font-extrabold text-slate-900 font-mono">
                                {{ number_format($product->price, 0, ',', ' ') }} Ar
                            </span>
                        </div>

                        <button type="button" 
                                onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }}, {{ $product->stock }}, '{{ $product->image }}')"
                                {{ $product->stock <= 0 ? 'disabled' : '' }}
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl transition active:scale-95
                                    {{ $product->stock > 0 
                                        ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-glow-brand' 
                                        : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                            @if($product->stock > 0)
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Ajouter</span>
                            @else
                                <span>Rupture</span>
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white border border-slate-200/80 rounded-2xl">
                <i data-lucide="inbox" class="w-10 h-10 text-slate-300 mx-auto mb-2"></i>
                <p class="text-sm font-semibold text-slate-700">Aucun produit trouvé dans cette catégorie.</p>
                <a href="{{ route('shop.index') }}" class="mt-3 inline-block text-xs font-bold text-blue-600 hover:underline">
                    Réinitialiser les filtres
                </a>
            </div>
        @endforelse
    </div>

</div>

<!-- Script Filtrage Instantané Côté Client -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('liveSearchInput');
        const cards = document.querySelectorAll('.product-card');

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase().trim();
                cards.forEach(card => {
                    const name = card.getAttribute('data-name');
                    const cat = card.getAttribute('data-category');
                    if (name.includes(term) || cat.includes(term)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endsection
