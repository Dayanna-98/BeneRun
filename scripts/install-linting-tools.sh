#!/bin/bash

# Script d'installation des outils de linting et d'analyse de code
# À exécuter une fois en développement local

echo "📦 Installation des outils de linting et d'analyse..."

# Installation des dépendances Composer optionnelles
composer require --dev \
  phpstan/phpstan \
  phpstan/extension-installer \
  phpstan/phpstan-laravel \
  nunomaduro/larastan

echo ""
echo "✅ Installation complétée!"
echo ""
echo "Commandes disponibles:"
echo "  ./vendor/bin/phpstan analyse            - Analyse statique du code PHP"
echo "  ./vendor/bin/pint                       - Linting et formatage PHP (déjà installé)"
echo "  composer audit                          - Audit de sécurité des dépendances"
echo ""
echo "Exécute ces commandes dans ta pipeline CI/CD!"
