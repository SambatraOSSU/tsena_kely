# Tsena Kely - Mini Boutique en Ligne avec Observabilite (Projet P20)

Projet Laravel 13 et Blade connecte a PostgreSQL 17 pour la plateforme d'observabilite microservices (OpenTelemetry, Prometheus, Grafana, Loki, Tempo).

## Architecture des 3 Services

1. **Service Accueil (Dev 1)**
   - Vitrine web et panier interactif en Blade (`/`)
   - Generation du TraceContext W3C (trace_id, span_id)
   - Interrupteurs de pannes pour la demonstration (latence artificielle, erreurs 500, arret service)
   - Simulateur de trafic continu (Web et CLI)

2. **Service Stock (Dev 3)**
   - Verification et decrementation atomique des quantites
   - Reapprovisionnement rapide (`/stock`)
   - Logs structures au format JSON avec trace_id dans `storage/logs/telemetry.json`

3. **Service Commandes (Dev 2)**
   - Persistance relationnelle sous PostgreSQL 17 (`tsena_kely`)
   - Comptage des commandes reussies et echouees
   - Calcul de la latence d'execution par commande
   - Historique et detail (`/orders`)

4. **Observabilite & Diagnostic (RSI 2 / Dev 2 & 3)**
   - Endpoint Prometheus de scraping : `/metrics`
   - Console de diagnostic et correlation trace -> logs : `/telemetry`
   - Exploration detaillee par trace : `/telemetry/trace/{traceId}`

## Lancement avec Docker (Recommande)

Le projet dispose d'une configuration Docker Compose complete (PHP 8.3-FPM + Nginx + Prometheus) :

```bash
# 1. Construire et demarrer les conteneurs
docker compose up -d --build

# 2. Acceder a l'application
# Boutique & Services : http://localhost:8000
# Endpoint Prometheus : http://localhost:8000/metrics
# Serveur Prometheus  : http://localhost:9090

# 3. Arreter les conteneurs
docker compose down
```

## Commandes Principales (Local sans Docker)

```bash
# Lancer le serveur de developpement
php artisan serve

# Executer les migrations et seeders
php artisan migrate --seed

# Lancer la simulation de trafic continu (Dev 1)
php artisan tsena:simulate --requests=50 --delay=200
```
