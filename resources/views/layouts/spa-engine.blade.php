<script>
/**
 * Tsena Kely - High-Level SPA Routing & Skeleton Shimmer Engine
 * Fournit une expérience applicative instantanée sans rechargement complet de page
 */
(function() {
    'use strict';

    // Cache des pages préchargées pour une transition à 0 ms
    const pageCache = new Map();
    let currentAbortController = null;
    let progressBarTimer = null;

    // --- 1. SKELETON LOADERS HAUTE FIDÉLITÉ (Style NovaBank / SaaS Moderne) ---
    function getSkeleton(pathname) {
        const path = pathname.replace(/\/$/, '') || '/';

        // Skeleton Métriques Universel (4 cartes avec shimmer)
        const metricCardsSkeleton = `
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                ${Array(4).fill(0).map(() => `
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-card space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="h-3 w-20 rounded skeleton-shimmer"></div>
                            <div class="w-8 h-8 rounded-xl skeleton-shimmer"></div>
                        </div>
                        <div class="h-7 w-24 rounded-lg skeleton-shimmer"></div>
                        <div class="h-4 w-32 rounded-full skeleton-shimmer"></div>
                    </div>
                `).join('')}
            </div>
        `;

        // 1.1 SQUELETTE BOUTIQUE (Catalogue, Filtres, Cartes Produits)
        if (path === '/' || path.startsWith('/shop')) {
            return `
                <div class="space-y-6 animate-pulse">
                    <!-- En-tête -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-2">
                            <div class="h-8 w-72 rounded-xl skeleton-shimmer"></div>
                            <div class="h-4 w-96 max-w-full rounded-lg skeleton-shimmer"></div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-36 rounded-xl skeleton-shimmer"></div>
                            <div class="h-9 w-36 rounded-xl skeleton-shimmer"></div>
                        </div>
                    </div>

                    <!-- 4 Métriques -->
                    ${metricCardsSkeleton}

                    <!-- Barre de recherche & Filtres catégories -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-card flex flex-col sm:flex-row gap-3 items-center justify-between">
                        <div class="h-10 w-full sm:w-80 rounded-xl skeleton-shimmer"></div>
                        <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto">
                            ${Array(5).fill(0).map(() => `<div class="h-8 w-24 rounded-full skeleton-shimmer flex-shrink-0"></div>`).join('')}
                        </div>
                    </div>

                    <!-- Grille de 6 Produits -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        ${Array(6).fill(0).map(() => `
                            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-card flex flex-col justify-between space-y-4">
                                <div class="space-y-3">
                                    <div class="h-44 w-full rounded-xl skeleton-shimmer"></div>
                                    <div class="flex justify-between items-center">
                                        <div class="h-4 w-20 rounded-full skeleton-shimmer"></div>
                                        <div class="h-4 w-16 rounded-full skeleton-shimmer"></div>
                                    </div>
                                    <div class="h-5 w-3/4 rounded-lg skeleton-shimmer"></div>
                                    <div class="space-y-1.5">
                                        <div class="h-3 w-full rounded skeleton-shimmer"></div>
                                        <div class="h-3 w-2/3 rounded skeleton-shimmer"></div>
                                    </div>
                                </div>
                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <div class="h-6 w-24 rounded-lg skeleton-shimmer"></div>
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-20 rounded-lg skeleton-shimmer"></div>
                                        <div class="h-8 w-24 rounded-xl skeleton-shimmer"></div>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        // 1.2 SQUELETTE SERVICE STOCK (Tableau d'inventaire atomique & Réapprovisionnement)
        if (path === '/stock' || path.startsWith('/stock')) {
            return `
                <div class="space-y-6 animate-pulse">
                    <!-- En-tête -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-2">
                            <div class="h-8 w-80 rounded-xl skeleton-shimmer"></div>
                            <div class="h-4 w-96 max-w-full rounded-lg skeleton-shimmer"></div>
                        </div>
                        <div class="h-8 w-44 rounded-full skeleton-shimmer"></div>
                    </div>

                    <!-- 4 Métriques Stock -->
                    ${metricCardsSkeleton}

                    <!-- Tableau d'inventaire -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-card overflow-hidden">
                        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                            <div class="h-5 w-48 rounded-lg skeleton-shimmer"></div>
                            <div class="h-8 w-32 rounded-xl skeleton-shimmer"></div>
                        </div>
                        <div class="divide-y divide-slate-100">
                            ${Array(6).fill(0).map(() => `
                                <div class="p-4 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3.5 flex-1">
                                        <div class="w-12 h-12 rounded-xl skeleton-shimmer flex-shrink-0"></div>
                                        <div class="space-y-1.5 flex-1 max-w-xs">
                                            <div class="h-4 w-40 rounded skeleton-shimmer"></div>
                                            <div class="h-3 w-28 rounded skeleton-shimmer"></div>
                                        </div>
                                    </div>
                                    <div class="h-5 w-16 rounded-lg skeleton-shimmer"></div>
                                    <div class="h-5 w-24 rounded-full skeleton-shimmer"></div>
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-16 rounded-lg skeleton-shimmer"></div>
                                        <div class="h-8 w-24 rounded-xl skeleton-shimmer"></div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
        }

        // 1.3 SQUELETTE SERVICE COMMANDES (Transactions, Statuts & Traces)
        if (path === '/orders') {
            return `
                <div class="space-y-6 animate-pulse">
                    <!-- En-tête -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-2">
                            <div class="h-8 w-80 rounded-xl skeleton-shimmer"></div>
                            <div class="h-4 w-96 max-w-full rounded-lg skeleton-shimmer"></div>
                        </div>
                        <div class="h-8 w-48 rounded-full skeleton-shimmer"></div>
                    </div>

                    <!-- 4 Métriques Transactions -->
                    ${metricCardsSkeleton}

                    <!-- Tableau des Commandes -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-card overflow-hidden">
                        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                            <div class="h-5 w-56 rounded-lg skeleton-shimmer"></div>
                            <div class="h-4 w-28 rounded skeleton-shimmer"></div>
                        </div>
                        <div class="divide-y divide-slate-100">
                            ${Array(6).fill(0).map(() => `
                                <div class="p-4 flex items-center justify-between gap-4">
                                    <div class="space-y-1.5 w-36">
                                        <div class="h-4 w-28 rounded skeleton-shimmer font-mono"></div>
                                        <div class="h-3 w-20 rounded skeleton-shimmer"></div>
                                    </div>
                                    <div class="space-y-1.5 flex-1 max-w-xs">
                                        <div class="h-4 w-36 rounded skeleton-shimmer"></div>
                                        <div class="h-3 w-24 rounded skeleton-shimmer"></div>
                                    </div>
                                    <div class="h-5 w-24 rounded-lg skeleton-shimmer font-mono"></div>
                                    <div class="h-5 w-20 rounded-full skeleton-shimmer"></div>
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-20 rounded-xl skeleton-shimmer"></div>
                                        <div class="h-8 w-24 rounded-xl skeleton-shimmer"></div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
        }

        // 1.4 SQUELETTE DÉTAIL COMMANDE (/orders/{id})
        if (path.startsWith('/orders/')) {
            return `
                <div class="max-w-3xl mx-auto space-y-6 animate-pulse">
                    <div class="flex items-center justify-between">
                        <div class="h-6 w-36 rounded-lg skeleton-shimmer"></div>
                        <div class="h-8 w-52 rounded-xl skeleton-shimmer"></div>
                    </div>
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-8 shadow-card space-y-6">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-5">
                            <div class="space-y-2">
                                <div class="h-3 w-28 rounded skeleton-shimmer"></div>
                                <div class="h-7 w-48 rounded-lg skeleton-shimmer font-mono"></div>
                            </div>
                            <div class="h-7 w-24 rounded-full skeleton-shimmer"></div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-xl bg-slate-50">
                            ${Array(4).fill(0).map(() => `
                                <div class="space-y-1.5">
                                    <div class="h-3 w-16 rounded skeleton-shimmer"></div>
                                    <div class="h-4 w-24 rounded skeleton-shimmer"></div>
                                </div>
                            `).join('')}
                        </div>
                        <div class="space-y-3">
                            <div class="h-4 w-32 rounded skeleton-shimmer"></div>
                            ${Array(3).fill(0).map(() => `
                                <div class="flex justify-between items-center p-3 rounded-lg border border-slate-100">
                                    <div class="h-4 w-48 rounded skeleton-shimmer"></div>
                                    <div class="h-4 w-20 rounded skeleton-shimmer"></div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
        }

        // 1.5 SQUELETTE CONSOLE OBSERVABILITÉ & CHAOS (/telemetry)
        if (path === '/telemetry') {
            return `
                <div class="space-y-6 animate-pulse">
                    <!-- En-tête -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-2">
                            <div class="h-8 w-96 max-w-full rounded-xl skeleton-shimmer"></div>
                            <div class="h-4 w-80 max-w-full rounded-lg skeleton-shimmer"></div>
                        </div>
                        <div class="h-9 w-44 rounded-xl skeleton-shimmer"></div>
                    </div>

                    <!-- 4 Métriques -->
                    ${metricCardsSkeleton}

                    <!-- 2 Grands Graphiques Chart.js -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        ${Array(2).fill(0).map(() => `
                            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-card space-y-4">
                                <div class="flex justify-between items-center">
                                    <div class="space-y-1">
                                        <div class="h-5 w-52 rounded skeleton-shimmer"></div>
                                        <div class="h-3 w-36 rounded skeleton-shimmer"></div>
                                    </div>
                                    <div class="h-5 w-20 rounded-full skeleton-shimmer"></div>
                                </div>
                                <div class="h-64 rounded-xl bg-slate-50/60 p-4 flex flex-col justify-between">
                                    <div class="h-px w-full bg-slate-200"></div>
                                    <div class="h-px w-full bg-slate-200"></div>
                                    <div class="h-px w-full bg-slate-200"></div>
                                    <div class="h-px w-full bg-slate-200"></div>
                                </div>
                            </div>
                        `).join('')}
                    </div>

                    <!-- 2 Blocs de Contrôle Chaos Engineering -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        ${Array(2).fill(0).map(() => `
                            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-card space-y-4">
                                <div class="h-5 w-48 rounded skeleton-shimmer"></div>
                                <div class="h-3 w-72 rounded skeleton-shimmer"></div>
                                <div class="h-12 w-full rounded-xl skeleton-shimmer"></div>
                                <div class="h-10 w-32 rounded-xl skeleton-shimmer"></div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        // 1.6 SQUELETTE DÉTAIL TRACE OPENTELEMETRY (/telemetry/trace/{id})
        if (path.startsWith('/telemetry/trace/')) {
            return `
                <div class="space-y-6 animate-pulse">
                    <div class="flex items-center justify-between">
                        <div class="h-6 w-52 rounded-lg skeleton-shimmer"></div>
                        <div class="h-6 w-36 rounded-full skeleton-shimmer"></div>
                    </div>
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-8 shadow-card space-y-6">
                        <div class="flex flex-col md:flex-row justify-between gap-4 border-b border-slate-100 pb-5">
                            <div class="space-y-2">
                                <div class="h-3 w-40 rounded skeleton-shimmer"></div>
                                <div class="h-7 w-80 max-w-full rounded-lg skeleton-shimmer font-mono"></div>
                            </div>
                            <div class="h-7 w-32 rounded-full skeleton-shimmer"></div>
                        </div>
                        <!-- Pipeline 3 Nœuds -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            ${Array(3).fill(0).map(() => `
                                <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50 space-y-2">
                                    <div class="h-4 w-28 rounded skeleton-shimmer"></div>
                                    <div class="h-5 w-16 rounded-full skeleton-shimmer"></div>
                                    <div class="h-3 w-36 rounded skeleton-shimmer"></div>
                                </div>
                            `).join('')}
                        </div>
                        <!-- Timeline des Logs -->
                        <div class="space-y-3 pt-4">
                            <div class="h-4 w-36 rounded skeleton-shimmer"></div>
                            ${Array(4).fill(0).map(() => `
                                <div class="p-3.5 rounded-xl border border-slate-100 flex items-center justify-between">
                                    <div class="h-4 w-64 rounded skeleton-shimmer"></div>
                                    <div class="h-4 w-20 rounded skeleton-shimmer"></div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
        }

        // Fallback Universel
        return `
            <div class="space-y-6 animate-pulse">
                <div class="h-8 w-64 rounded-xl skeleton-shimmer"></div>
                ${metricCardsSkeleton}
                <div class="h-64 rounded-2xl bg-white border border-slate-200/80 shadow-card p-6 skeleton-shimmer"></div>
            </div>
        `;
    }

    // --- 2. BARRE DE PROGRESSION SUPÉRIEURE (Top Progress Bar) ---
    function startProgressBar() {
        const bar = document.getElementById('topProgressBar');
        if (!bar) return;
        clearTimeout(progressBarTimer);
        bar.classList.remove('is-done');
        bar.classList.add('is-loading');
        bar.style.width = '25%';

        progressBarTimer = setTimeout(() => {
            if (bar.classList.contains('is-loading')) {
                bar.style.width = '70%';
            }
        }, 150);
    }

    function completeProgressBar() {
        const bar = document.getElementById('topProgressBar');
        if (!bar) return;
        clearTimeout(progressBarTimer);
        bar.style.width = '100%';
        bar.classList.remove('is-loading');
        bar.classList.add('is-done');

        setTimeout(() => {
            bar.classList.remove('is-done');
            bar.style.width = '0';
        }, 350);
    }

    // --- 3. MISE À JOUR VISUELLE IMMÉDIATE DES ONGLETS DE NAVIGATION ---
    function updateActiveNav(targetPath) {
        const path = targetPath.replace(/\/$/, '') || '/';

        let activeKey = 'shop';
        if (path === '/stock' || path.startsWith('/stock')) activeKey = 'stock';
        else if (path === '/orders' || path.startsWith('/orders')) activeKey = 'orders';
        else if (path === '/telemetry' || path.startsWith('/telemetry')) activeKey = 'telemetry';

        // Mise à jour de la barre desktop
        document.querySelectorAll('[data-nav-route]').forEach(el => {
            const route = el.getAttribute('data-nav-route');
            if (route === activeKey) {
                el.className = 'px-4 py-1.5 rounded-full transition bg-blue-600 text-white shadow-sm font-bold';
            } else {
                el.className = 'px-4 py-1.5 rounded-full transition text-slate-600 hover:text-slate-900 hover:bg-white/60';
            }
        });

        // Mise à jour du menu mobile
        document.querySelectorAll('[data-mobile-route]').forEach(el => {
            const route = el.getAttribute('data-mobile-route');
            if (route === activeKey) {
                el.className = 'block px-3 py-2 rounded-lg text-sm font-semibold bg-blue-50 text-blue-700';
            } else {
                el.className = 'block px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50';
            }
        });
    }

    // --- 4. EXÉCUTION PROPRE DES SCRIPTS SPÉCIFIQUES À LA PAGE ---
    function executePageScripts(container) {
        if (!container) return;

        // Récupérer et exécuter chaque script trouvé
        const scripts = container.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            
            let code = oldScript.textContent;
            // Si le script utilise document.addEventListener('DOMContentLoaded', ...),
            // on l'exécute immédiatement en IIFE pour éviter l'accumulation d'écouteurs dormants
            if (code.includes("DOMContentLoaded")) {
                code = code.replace(/document\.addEventListener\(['"]DOMContentLoaded['"],\s*(?:\(\)\s*=>|function\s*\(\))\s*\{/, '(function() {');
            }

            newScript.textContent = code;
            document.body.appendChild(newScript);
            newScript.remove(); // Nettoyage immédiat
        });

        // Relance des icônes vectorielles Lucide
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        // Notification d'événement global pour les modules
        window.dispatchEvent(new CustomEvent('spa:navigated', { detail: { path: window.location.pathname } }));
    }

    // --- 5. MOTEUR CENTRAL DE NAVIGATION SPA SANS RECHARGEMENT ---
    async function spaNavigate(url, pushState = true, isPrefetch = false) {
        try {
            const parsedUrl = new URL(url, window.location.origin);

            // Ignorer si domaine externe ou endpoint brut /metrics
            if (parsedUrl.origin !== window.location.origin || parsedUrl.pathname === '/metrics') {
                if (!isPrefetch) window.location.href = url;
                return;
            }

            const mainContainer = document.getElementById('app-main');
            if (!mainContainer) {
                if (!isPrefetch) window.location.href = url;
                return;
            }

            // Si c'est juste un clic sur la même page sans query différente
            if (parsedUrl.href === window.location.href && !isPrefetch) {
                return;
            }

            // Annuler toute requête précédente en cours
            if (currentAbortController) {
                currentAbortController.abort();
            }
            currentAbortController = new AbortController();

            // 1. Retour visuel immédiat (0 ms)
            if (!isPrefetch) {
                updateActiveNav(parsedUrl.pathname);
                startProgressBar();

                // 2. Affichage IMMÉDIAT du Skeleton Loader avec Shimmer
                mainContainer.innerHTML = getSkeleton(parsedUrl.pathname);
                mainContainer.classList.remove('spa-fade-in');

                // Fermer le menu mobile s'il était ouvert
                const mobileMenu = document.getElementById('mobileMenu');
                if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }

                // Défilement doux vers le haut
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            // 3. Récupération des données (depuis le cache ou par fetch)
            let htmlText = '';
            const cacheKey = parsedUrl.href;

            if (pageCache.has(cacheKey) && !isPrefetch) {
                htmlText = pageCache.get(cacheKey);
            } else {
                const startTime = performance.now();
                const response = await fetch(parsedUrl.href, {
                    signal: currentAbortController.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-SPA-Request': '1'
                    }
                });

                if (!response.ok) {
                    throw new Error('Erreur HTTP ' + response.status);
                }

                htmlText = await response.text();

                // Si navigation réelle, garantir au moins 140ms d'affichage du Skeleton
                // pour un rendu visuel net, agréable et 'High Level' sans scintillement brusque
                if (!isPrefetch) {
                    const elapsed = performance.now() - startTime;
                    if (elapsed < 140) {
                        await new Promise(r => setTimeout(r, 140 - elapsed));
                    }
                }
            }

            if (isPrefetch) {
                pageCache.set(cacheKey, htmlText);
                return;
            }

            // 4. Parser la réponse HTML
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlText, 'text/html');

            const newMain = doc.getElementById('app-main');
            if (!newMain) {
                window.location.href = url;
                return;
            }

            // Mise à jour du Titre
            document.title = doc.title || 'Tsena Kely';

            // Mise à jour de la bannière Chaos si présente
            const newChaos = doc.getElementById('chaosBannerContainer');
            const currentChaos = document.getElementById('chaosBannerContainer');
            if (newChaos && currentChaos) {
                currentChaos.innerHTML = newChaos.innerHTML;
            }

            // Mise à jour des alertes flash si présentes
            const newFlash = doc.getElementById('flashAlertContainer');
            const currentFlash = document.getElementById('flashAlertContainer');
            if (newFlash && currentFlash) {
                currentFlash.innerHTML = newFlash.innerHTML;
            }

            // 5. Remplacement du Contenu avec Fade-In doux
            mainContainer.innerHTML = newMain.innerHTML;
            mainContainer.classList.add('spa-fade-in');

            // 6. Historique du navigateur
            if (pushState) {
                window.history.pushState({ spa: true, url: parsedUrl.href }, '', parsedUrl.href);
            }

            // 7. Fin de la barre de progression
            completeProgressBar();

            // 8. Ré-exécution des scripts et relance des composants
            executePageScripts(mainContainer);

            // 9. Rafraîchissement du panier global
            if (typeof updateCartUI === 'function') {
                updateCartUI();
            }

        } catch (err) {
            if (err.name === 'AbortError') return;
            console.error('Erreur SPA Navigation:', err);
            completeProgressBar();
            if (!isPrefetch) {
                // Fallback de sécurité
                window.location.href = url;
            }
        }
    }

    // --- 6. INTERCEPTION DES LIENS ET PRÉCHARGEMENT ---
    function isInternalLink(link) {
        if (!link || !link.href) return false;
        if (link.target && link.target !== '_self') return false;
        if (link.hasAttribute('download')) return false;
        if (link.hasAttribute('data-no-spa')) return false;
        if (link.getAttribute('href')?.startsWith('#')) return false;
        if (link.getAttribute('href')?.startsWith('javascript:')) return false;

        const url = new URL(link.href, window.location.origin);
        if (url.origin !== window.location.origin) return false;
        if (url.pathname === '/metrics') return false; // Route brute Prometheus

        return true;
    }

    // Écoute des clics globaux sur les liens
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link || !isInternalLink(link)) return;

        // Si touches modificatrices (Ctrl/Cmd pour nouvel onglet), laisser le comportement natif
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        e.preventDefault();
        spaNavigate(link.href, true);
    });

    // Préchargement ultra-rapide au survol de la souris (Hover Prefetching)
    document.addEventListener('mouseover', (e) => {
        const link = e.target.closest('a');
        if (!link || !isInternalLink(link)) return;

        const url = new URL(link.href, window.location.origin);
        if (!pageCache.has(url.href) && url.href !== window.location.href) {
            spaNavigate(url.href, false, true);
        }
    }, { passive: true });

    // --- 7. GESTION DES BOUTONS HISTORIQUE (PRÉCÉDENT / SUIVANT) ---
    window.addEventListener('popstate', (e) => {
        spaNavigate(window.location.href, false);
    });

    // --- 8. SOUMISSION FLUIDE DES FORMULAIRES (AJAX / SPA) ---
    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('form');
        if (!form || form.hasAttribute('data-no-spa')) return;

        const actionUrl = new URL(form.action || window.location.href, window.location.origin);
        if (actionUrl.origin !== window.location.origin) return;

        e.preventDefault();
        startProgressBar();

        try {
            const formData = new FormData(form);
            const method = (form.method || 'POST').toUpperCase();

            let fetchOptions = {
                method: method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-SPA-Request': '1'
                }
            };

            let reqUrl = actionUrl.href;
            if (method === 'GET') {
                const params = new URLSearchParams(formData);
                reqUrl = actionUrl.origin + actionUrl.pathname + '?' + params.toString();
            } else {
                fetchOptions.body = formData;
            }

            const response = await fetch(reqUrl, fetchOptions);
            const htmlText = await response.text();

            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlText, 'text/html');

            const mainContainer = document.getElementById('app-main');
            const newMain = doc.getElementById('app-main');

            if (newMain && mainContainer) {
                // Mettre à jour l'URL finale si redirection
                if (response.url && response.url !== window.location.href) {
                    window.history.pushState({ spa: true, url: response.url }, '', response.url);
                    updateActiveNav(new URL(response.url).pathname);
                }

                // Titre
                document.title = doc.title || 'Tsena Kely';

                // Bannière Chaos
                const newChaos = doc.getElementById('chaosBannerContainer');
                const currentChaos = document.getElementById('chaosBannerContainer');
                if (newChaos && currentChaos) {
                    currentChaos.innerHTML = newChaos.innerHTML;
                }

                // Alertes Flash
                const newFlash = doc.getElementById('flashAlertContainer');
                const currentFlash = document.getElementById('flashAlertContainer');
                if (newFlash && currentFlash) {
                    currentFlash.innerHTML = newFlash.innerHTML;
                }

                mainContainer.innerHTML = newMain.innerHTML;
                mainContainer.classList.add('spa-fade-in');
                executePageScripts(mainContainer);
            } else {
                window.location.reload();
            }

            completeProgressBar();

            // Vider le cache après mutation POST
            if (method === 'POST') {
                pageCache.clear();
            }

        } catch (err) {
            console.error('Erreur soumission formulaire SPA:', err);
            completeProgressBar();
            form.submit(); // Fallback natif
        }
    });

    // Exposer l'utilitaire globalement si besoin
    window.spaNavigate = spaNavigate;

})();
</script>
