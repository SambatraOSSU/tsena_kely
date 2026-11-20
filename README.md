# Tsena Kely - Boutique Réactive & Plateforme d'Observabilité Microservices (P20)

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com)
[![OpenTelemetry](https://img.shields.io/badge/OpenTelemetry-W3C_TraceContext-blueviolet?style=for-the-badge&logo=opentelemetry&logoColor=white)](https://opentelemetry.io)
[![Prometheus](https://img.shields.io/badge/Prometheus-Metrics_Scraping-E6522C?style=for-the-badge&logo=prometheus&logoColor=white)](https://prometheus.io)

**Tsena Kely** est une application web moderne simulant une architecture distribuée à trois services (Accueil, Stock, Commandes) avec traçabilité distribuée W3C, observabilité temps réel, injection d'anomalies (Chaos Engineering), et une expérience utilisateur **Single Page Application (SPA)** fluide avec **Skeleton Loaders animés**.

---

## 🌟 Fonctionnalités Principales

### 1. Navigation SPA Fluide & Skeleton Loaders Haute Fidélité
- **Zéro Rechargement Complet** : L'App Shell (en-tête, barre d'onglets, tiroir panier, pied de page) ne recharge jamais la page.
- **Barre de progression supérieure** : Barre de chargement ultra-fine et lumineuse (style Vercel / GitHub).
- **Skeleton Shimmer Contextuel** : Dès qu'un onglet est cliqué, un squelette animé spécifique apparaît instantanément (cartes produits pour la boutique, tableau d'inventaire pour le stock, transactions pour les commandes, graphiques pour l'observabilité).
- **Préchargement intelligent (Hover Prefetching)** : Les pages cibles sont préchargées dès le survol de la souris pour des transitions instantanées (< 20 ms).
- **Prise en charge de l'historique** : Navigation native avant/arrière avec `popstate`.

### 2. Architecture 3 Microservices & Traçabilité Distribuée
- **Service Accueil (`/`)** : Vitrine catalogue, panier interactif, gestion des quantités, et génération de l'identifiant W3C `trace_id`.
- **Service Stock (`/stock`)** : Vérification atomique des stocks, verrouillage pessimiste SQL (`lockForUpdate`), et réapprovisionnement instantané.
- **Service Commandes (`/orders`)** : Persistance relationnelle des transactions, calcul de la latence d'exécution par commande, historique détaillé (`/orders/{id}`) et corrélation vers la trace.

### 3. Observabilité & Chaos Engineering (`/telemetry`)
- **Console de Diagnostic en Direct** : Suivi des métriques clés (taux de succès 200 OK, erreurs 500, latence moyenne).
- **Graphiques Interactifs (Chart.js)** :
  - Courbe d'évolution de la latence par transaction.
  - Diagramme en barres arrondies des stocks disponibles.
- **Inspecteur de Traces W3C (`/telemetry/trace/{traceId}`)** : Visualisation du pipeline distribué à 3 nœuds et logs JSON structurés.
- **Injecteur d'Anomalies (Chaos Engineering)** : Injection de latence artificielle (0 à 2000 ms), simulation de taux d'erreur 500, coupure à chaud des services Stock ou Commandes.
- **Endpoint Prometheus** : Scraping natif sur `/metrics`.

---

## 🚀 Démarrage Rapide avec Docker (Recommandé)

Le projet dispose d'une configuration **Docker & Docker Compose** prête pour la production et le développement (PHP 8.3-FPM + Nginx + Prometheus) :

```bash
# 1. Cloner le projet (si ce n'est pas déjà fait)
git clone https://github.com/SambatraOSSU/tsena_kely.git
cd tsena_kely

# 2. Lancer les conteneurs en arrière-plan
docker compose up -d --build

# 3. Accéder aux services :
# - Application Tsena Kely : http://localhost:8000
# - Métriques Prometheus   : http://localhost:8000/metrics
# - Console Prometheus     : http://localhost:9090

# 4. Arrêter les conteneurs
docker compose down
```

> **Note :** Le script d'entrée Docker (`entrypoint.sh`) génère automatiquement la clé d'application (`APP_KEY`), initialise la base de données et peuple le catalogue de produits si nécessaire.

---

## 💻 Installation Locale (Sans Docker)

### Prérequis
- PHP 8.3+ avec extensions `pdo_sqlite` (ou `pdo_pgsql`), `mbstring`, `zip`, `bcmath`.
- Composer 2+.
- Node.js 18+ et NPM.

### Étapes d'installation
```bash
# 1. Installer les dépendances PHP et JavaScript
composer install
npm install

# 2. Configurer l'environnement
copy .env.example .env
php artisan key:generate

# 3. Exécuter les migrations et charger le catalogue de test
php artisan migrate --seed

# 4. Compiler les assets Vite
npm run build

# 5. Démarrer le serveur de développement
php artisan serve
```
L'application sera accessible sur [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## 🧪 Tests & Vérifications

Exécuter la suite de tests automatisés PHPUnit :
```bash
php artisan test
```

Pour lancer une simulation de trafic automatique (génération de commandes et de traces) :
```bash
php artisan tsena:simulate --requests=20 --delay=150
```

---

## 📁 Structure du Projet

```text
├── app/
│   ├── Http/Controllers/    # Contrôleurs (Shop, Stock, Order, Telemetry)
│   ├── Models/                 # Modèles Eloquent (Product, Order, OrderItem, SystemSetting)
│   └── Services/               # Logique métier et traçabilité OpenTelemetry
├── docker/
│   ├── nginx/default.conf     # Configuration Nginx optimisée pour Laravel
│   ├── entrypoint.sh          # Script d'auto-provisioning des conteneurs
│   └── prometheus/            # Configuration de scraping Prometheus
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php        # App Shell persistant et barre de progression
│   │   ├── layouts/spa-engine.blade.php # Moteur SPA et Skeleton Shimmers
│   │   ├── shop/                        # Vitrine catalogue et confirmation
│   │   ├── stock/                       # Tableau d'inventaire atomique
│   │   ├── orders/                      # Centre des transactions et détail
│   │   └── telemetry/                   # Dashboard observabilité et visualiseur de trace
├── Dockerfile                  # Image PHP 8.3-FPM Alpine
├── docker-compose.yml          # Stack complète (PHP, Nginx, Prometheus)
└── routes/web.php              # Définition des routes applicatives et métriques
```

---

## 👤 Auteur & Contribution

- Développé et maintenu par **[SambatraOSSU](https://github.com/SambatraOSSU)**
- Projet P20 - Architecture Microservices & Observabilité
