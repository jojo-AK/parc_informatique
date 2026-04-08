#!/bin/bash
# Initialise la base si elle n'existe pas encore
if [ ! -f "database/parc_informatique.db" ]; then
    echo "Initialisation de la base SQLite..."
    php config/init_sqlite.php
fi

echo ""
echo "Lancement de Parc IT sur http://localhost:8080"
echo "Appuie sur Ctrl+C pour arreter."
echo ""
php -S localhost:8080 -t .
