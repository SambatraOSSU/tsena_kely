<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Tsena Kely') }} - Boutique & Observabilité</title>

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN avec palette personnalisée NovaBank) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb', // Bleu vibrant NovaBank
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    boxShadow: {
                        'card': '0 1px 3px 0 rgba(15, 23, 42, 0.05), 0 1px 2px -1px rgba(15, 23, 42, 0.05)',
                        'card-hover': '0 10px 25px -5px rgba(37, 99, 235, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04)',
                        'glow-brand': '0 4px 20px -2px rgba(37, 99, 235, 0.35)',
                    }
                }
            }
        }
    </script>

    <!-- Chart.js pour les graphiques du dashboard d'observabilité -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8FAFC;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-full flex flex-col antialiased">

    <!-- Bannière Chaos System Status (visible si chaos activé) -->
    @php
        $chaosLatency = (int) \App\Models\SystemSetting::getVal('chaos_latency_ms', 0);
        $chaosErrorRate = (int) \App\Models\SystemSetting::getVal('chaos_error_rate', 0);
        $chaosStockDown = \App\Models\SystemSetting::getVal('chaos_stock_down', '0') === '1';
        $chaosOrdersDown = \App\Models\SystemSetting::getVal('chaos_orders_down', '0') === '1';
        $isChaosActive = ($chaosLatency > 0 || $chaosErrorRate > 0 || $chaosStockDown || $chaosOrdersDown);
    @endphp

    @if($isChaosActive)
        <div class="bg-amber-500 text-slate-900 text-xs py-1.5 px-4 font-medium flex items-center justify-between border-b border-amber-600">
            <div class="max-w-7xl mx-auto w-full flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-slate-900 animate-ping"></span>
                    <span class="font-bold">⚠️ Mode Chaos Actif :</span>
                    <span>
                        @if($chaosLatency > 0) +{{ $chaosLatency }}ms latence @endif
                        @if($chaosErrorRate > 0) • {{ $chaosErrorRate }}% erreurs 500 @endif
                        @if($chaosStockDown) • Service Stock COUPÉ (503) @endif
                        @if($chaosOrdersDown) • Service Commandes COUPÉ (503) @endif
                    </span>
                </div>
                <a href="{{ route('telemetry.dashboard') }}" class="underline font-semibold hover:text-white transition">Gérer les pannes &rarr;</a>
            </div>
        </div>
    @endif

    <!-- Barre de Navigation Principale -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            
            <!-- Logo & Marque -->
            <div class="flex items-center gap-6">
                <a href="{{ route('shop.index') }}" class="flex items-center gap-2.5 group">
                    <div class="h-9 w-9 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-sm shadow-blue-500/30 group-hover:bg-blue-700 transition">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-lg font-extrabold tracking-tight text-slate-900">TSENA KELY</span>
                            <span class="hidden sm:inline-flex text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">P20</span>
                        </div>
                    </div>
                </a>

                <!-- Navigation Tabs Pills (Style NovaBank) -->
                <nav class="hidden md:flex items-center bg-slate-100/90 p-1 rounded-full border border-slate-200/70 text-xs font-semibold">
                    <a href="{{ route('shop.index') }}" 
                       class="px-4 py-1.5 rounded-full transition {{ request()->routeIs('shop.*') ? 'bg-blue-600 text-white shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                       Boutique
                    </a>
                    <a href="{{ route('stock.index') }}" 
                       class="px-4 py-1.5 rounded-full transition {{ request()->routeIs('stock.*') ? 'bg-blue-600 text-white shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                       Service Stock
                    </a>
                    <a href="{{ route('orders.index') }}" 
                       class="px-4 py-1.5 rounded-full transition {{ request()->routeIs('orders.*') ? 'bg-blue-600 text-white shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                       Service Commandes
                    </a>
                    <a href="{{ route('telemetry.dashboard') }}" 
                       class="px-4 py-1.5 rounded-full transition {{ request()->routeIs('telemetry.*') ? 'bg-blue-600 text-white shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                       Diagnostic & Chaos
                    </a>
                </nav>
            </div>

            <!-- Actions à droite -->
            <div class="flex items-center gap-3">
                <a href="{{ route('telemetry.metrics') }}" target="_blank" 
                   class="hidden lg:inline-flex items-center gap-1.5 text-xs bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 px-3 py-1.5 rounded-full font-mono transition">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>/metrics</span>
                </a>

                <!-- Bouton Panier -->
                <button id="openCartBtn" type="button" 
                        class="relative inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-semibold px-4 py-2 rounded-full shadow-glow-brand transition">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                    <span>Panier</span>
                    <span id="cartCountBadge" class="bg-white text-blue-700 text-[11px] font-extrabold px-1.5 py-0.2 rounded-full min-w-5 text-center">0</span>
                </button>

                <!-- Bouton Menu Mobile Hamburger -->
                <button id="mobileMenuBtn" type="button" class="md:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Menu Mobile Déroulant -->
        <div id="mobileMenu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-4 space-y-1">
            <a href="{{ route('shop.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('shop.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50' }}">Boutique</a>
            <a href="{{ route('stock.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('stock.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50' }}">Service Stock</a>
            <a href="{{ route('orders.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('orders.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50' }}">Service Commandes</a>
            <a href="{{ route('telemetry.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('telemetry.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50' }}">Diagnostic & Chaos</a>
            <a href="{{ route('telemetry.metrics') }}" target="_blank" class="block px-3 py-2 rounded-lg text-xs font-mono text-slate-500 hover:bg-slate-50">Scraping Prometheus (/metrics)</a>
        </div>
    </header>

    <!-- Conteneur Flash Alerts -->
    <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if(session('success'))
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800">&times;</button>
            </div>
        @endif
    </div>

    <!-- Contenu Principal -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Slide-over Drawer Panier (Style Moderne NovaBank) -->
    <div id="cartDrawer" class="fixed inset-0 z-50 overflow-hidden pointer-events-none transition-opacity duration-300 opacity-0 invisible">
        <!-- Backdrop -->
        <div id="cartBackdrop" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity duration-300"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10 pointer-events-auto">
            <div id="cartPanel" class="w-screen max-w-md bg-white shadow-2xl flex flex-col transform translate-x-full transition-transform duration-300 ease-in-out">
                
                <!-- En-tête Panier -->
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Votre Panier</h3>
                            <p class="text-xs text-slate-500" id="cartHeaderCount">0 article</p>
                        </div>
                    </div>
                    <button type="button" id="closeCartBtn" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Corps du Panier -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <!-- Panier vide -->
                    <div id="cartEmptyState" class="py-12 text-center">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center mb-3">
                            <i data-lucide="shopping-bag" class="w-7 h-7"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800">Votre panier est vide</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">Ajoutez des produits depuis la boutique pour tester les commandes et les traces.</p>
                        <button type="button" onclick="closeCartDrawer()" class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                            Explorer la boutique
                        </button>
                    </div>

                    <!-- Liste des articles -->
                    <div id="cartItemsList" class="space-y-3 divide-y divide-slate-100">
                        <!-- Rempli par JavaScript -->
                    </div>

                    <!-- Formulaire de commande client -->
                    <div id="checkoutSection" class="hidden pt-4 border-t border-slate-200">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-blue-600"></i>
                            <span>Informations Client (Commande)</span>
                        </h4>

                        <form id="checkoutForm" action="{{ route('shop.checkout') }}" method="POST" class="space-y-3">
                            @csrf
                            <input type="hidden" name="cart_items" id="cartItemsInput">
                            
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Nom complet *</label>
                                <input type="text" name="customer_name" required placeholder="Ex: Jean Rakoto" 
                                       class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                            </div>

                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                                    <input type="email" name="customer_email" placeholder="client@test.mg" 
                                           class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Téléphone</label>
                                    <input type="text" name="customer_phone" placeholder="034 00 000 00" 
                                           class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                                </div>
                            </div>

                            <!-- Résumé financier -->
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80 space-y-2 mt-4 text-xs">
                                <div class="flex justify-between text-slate-500">
                                    <span>Sous-total articles</span>
                                    <span id="cartSubtotalText" class="font-mono font-medium text-slate-800">0 Ar</span>
                                </div>
                                <div class="flex justify-between text-slate-500">
                                    <span>Frais de livraison</span>
                                    <span class="font-semibold text-emerald-600">Gratuit</span>
                                </div>
                                <div class="border-t border-slate-200 pt-2 flex justify-between text-sm font-bold text-slate-900">
                                    <span>Total à régler</span>
                                    <span id="cartTotalText" class="font-mono text-blue-600 text-base">0 Ar</span>
                                </div>
                            </div>

                            <button type="submit" id="submitOrderBtn" 
                                    class="w-full mt-3 py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-98 text-white text-xs font-bold shadow-glow-brand flex items-center justify-center gap-2 transition">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span>Valider la commande</span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Notification Toast Flottante -->
    <div id="toastNotification" class="fixed bottom-6 right-6 z-50 transform translate-y-16 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="bg-slate-900 text-white px-4 py-3 rounded-2xl shadow-xl flex items-center gap-3 border border-slate-700">
            <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                <i data-lucide="check" class="w-3.5 h-3.5"></i>
            </div>
            <span id="toastMessage" class="text-xs font-medium">Produit ajouté au panier</span>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/80 mt-16 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <div class="h-6 w-6 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs">TK</div>
                <span class="font-semibold text-slate-800">Tsena Kely</span>
                <span>— Architecture 3 Services (Accueil, Stock, Commandes) & Observabilité P20</span>
            </div>
            <div class="flex items-center gap-4 text-slate-600">
                <a href="{{ route('telemetry.metrics') }}" target="_blank" class="hover:text-blue-600 font-mono">/metrics</a>
                <a href="{{ route('telemetry.dashboard') }}" class="hover:text-blue-600">Diagnostic</a>
                <span class="text-slate-300">|</span>
                <span>OpenTelemetry • Prometheus • SQLite / PostgreSQL</span>
            </div>
        </div>
    </footer>

    <!-- Logique Panier & Drawer Global -->
    <script>
        let cart = JSON.parse(localStorage.getItem('tsena_cart') || '[]');

        function saveCart() {
            localStorage.setItem('tsena_cart', JSON.stringify(cart));
            updateCartUI();
        }

        function updateCartUI() {
            const totalCount = cart.reduce((acc, item) => acc + item.qty, 0);
            
            // Badge navbar
            const badge = document.getElementById('cartCountBadge');
            if (badge) badge.innerText = totalCount;

            const headerCount = document.getElementById('cartHeaderCount');
            if (headerCount) headerCount.innerText = totalCount + (totalCount > 1 ? ' articles' : ' article');

            const emptyState = document.getElementById('cartEmptyState');
            const itemsList = document.getElementById('cartItemsList');
            const checkoutSection = document.getElementById('checkoutSection');
            const subtotalText = document.getElementById('cartSubtotalText');
            const totalText = document.getElementById('cartTotalText');
            const itemsInput = document.getElementById('cartItemsInput');

            if (!itemsList) return;

            itemsList.innerHTML = '';
            if (cart.length === 0) {
                if (emptyState) emptyState.classList.remove('hidden');
                if (checkoutSection) checkoutSection.classList.add('hidden');
            } else {
                if (emptyState) emptyState.classList.add('hidden');
                if (checkoutSection) checkoutSection.classList.remove('hidden');

                let total = 0;
                cart.forEach((item, index) => {
                    const subtotal = item.price * item.qty;
                    total += subtotal;

                    const row = document.createElement('div');
                    row.className = 'pt-3 flex items-center justify-between text-xs group';
                    row.innerHTML = `
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 overflow-hidden flex-shrink-0 border border-slate-200/60">
                                <img src="${item.image || 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=200'}" alt="${item.name}" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <div class="font-bold text-slate-800">${item.name}</div>
                                <div class="text-[11px] text-slate-400 font-mono">${item.price.toLocaleString('fr-FR')} Ar</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <!-- Stepper Quantité -->
                            <div class="flex items-center bg-slate-100 rounded-lg p-0.5 border border-slate-200">
                                <button type="button" onclick="changeQty(${index}, -1)" class="w-6 h-6 flex items-center justify-center text-slate-600 hover:text-slate-900 rounded font-bold hover:bg-white transition">-</button>
                                <span class="w-7 text-center font-bold text-slate-800 text-xs">${item.qty}</span>
                                <button type="button" onclick="changeQty(${index}, 1)" class="w-6 h-6 flex items-center justify-center text-slate-600 hover:text-slate-900 rounded font-bold hover:bg-white transition">+</button>
                            </div>
                            <!-- Prix partiel -->
                            <span class="font-mono font-bold text-slate-800 w-20 text-right">${subtotal.toLocaleString('fr-FR')} Ar</span>
                            <!-- Supprimer -->
                            <button type="button" onclick="removeFromCart(${index})" class="text-slate-300 hover:text-rose-500 p-1 transition" title="Supprimer">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    `;
                    itemsList.appendChild(row);
                });

                if (subtotalText) subtotalText.innerText = total.toLocaleString('fr-FR') + ' Ar';
                if (totalText) totalText.innerText = total.toLocaleString('fr-FR') + ' Ar';
                if (itemsInput) itemsInput.value = JSON.stringify(cart);
            }

            if (window.lucide) {
                window.lucide.createIcons();
            }
        }

        function addToCart(id, name, price, maxStock, image) {
            const existing = cart.find(i => i.id === id);
            if (existing) {
                if (existing.qty < maxStock) {
                    existing.qty += 1;
                } else {
                    showToast('Stock maximal disponible atteint (' + maxStock + ')');
                    return;
                }
            } else {
                if (maxStock < 1) {
                    showToast('Produit actuellement en rupture');
                    return;
                }
                cart.push({ id, name, price, qty: 1, image, maxStock });
            }
            saveCart();
            showToast('✓ ' + name + ' ajouté au panier');
            openCartDrawer();
        }

        function changeQty(index, delta) {
            if (!cart[index]) return;
            const newQty = cart[index].qty + delta;
            if (newQty <= 0) {
                cart.splice(index, 1);
            } else if (newQty > cart[index].maxStock) {
                showToast('Stock maximal atteint pour cet article (' + cart[index].maxStock + ')');
                return;
            } else {
                cart[index].qty = newQty;
            }
            saveCart();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            saveCart();
        }

        function clearCart() {
            cart = [];
            saveCart();
        }

        // Drawer Controls
        const drawer = document.getElementById('cartDrawer');
        const backdrop = document.getElementById('cartBackdrop');
        const panel = document.getElementById('cartPanel');

        function openCartDrawer() {
            if (!drawer || !panel) return;
            drawer.classList.remove('invisible', 'opacity-0');
            drawer.classList.add('opacity-100');
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');
        }

        function closeCartDrawer() {
            if (!drawer || !panel) return;
            panel.classList.remove('translate-x-0');
            panel.classList.add('translate-x-full');
            drawer.classList.remove('opacity-100');
            drawer.classList.add('opacity-0');
            setTimeout(() => {
                drawer.classList.add('invisible');
            }, 300);
        }

        document.getElementById('openCartBtn')?.addEventListener('click', openCartDrawer);
        document.getElementById('closeCartBtn')?.addEventListener('click', closeCartDrawer);
        backdrop?.addEventListener('click', closeCartDrawer);

        // Toast Notification
        function showToast(message) {
            const toast = document.getElementById('toastNotification');
            const msg = document.getElementById('toastMessage');
            if (!toast || !msg) return;

            msg.innerText = message;
            toast.classList.remove('translate-y-16', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');

            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-16', 'opacity-0');
            }, 2500);
        }

        // Mobile Menu Toggle
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
            const m = document.getElementById('mobileMenu');
            if (m) m.classList.toggle('hidden');
        });

        document.addEventListener('DOMContentLoaded', () => {
            updateCartUI();
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>
</html>
