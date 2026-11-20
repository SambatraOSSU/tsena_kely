#!/bin/sh
set -e

# S'assurer que le fichier .env existe
if [ ! -f /var/www/.env ]; then
    echo "Creation du fichier .env a partir de .env.example..."
    cp /var/www/.env.example /var/www/.env
fi

# Configuration et initialisation de SQLite
if [ "$DB_CONNECTION" = "sqlite" ] || grep -q "DB_CONNECTION=sqlite" /var/www/.env; then
    mkdir -p /var/www/database
    if [ ! -f /var/www/database/database.sqlite ]; then
        echo "Creation de la base de donnees SQLite..."
        touch /var/www/database/database.sqlite
    fi
    chmod 666 /var/www/database/database.sqlite
    chmod 777 /var/www/database
fi

# Generer la cle d'application si absente
if ! grep -q "^APP_KEY=base64:" /var/www/.env; then
    echo "Generation de la cle d'application Laravel (APP_KEY)..."
    php artisan key:generate --force
fi

# Executer les migrations
echo "Execution des migrations de base de donnees..."
php artisan migrate --force

# Executer le seeder des produits si la table est vide
echo "Verification du catalogue de produits..."
php artisan db:seed --class=ProductSeeder --force || true

# Droits sur le cache et les logs
echo "Ajustement des permissions storage..."
chmod -R 777 /var/www/storage /var/www/bootstrap/cache

echo "Tsena Kely est pret ! Lancement du service..."
exec "$@"
