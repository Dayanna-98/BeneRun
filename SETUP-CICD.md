# 🎯 Guide de mise en place CI/CD pour BeneRun

## 📦 Fichiers créés

```
.github/
├── workflows/
│   ├── tests.yml              # Pipeline tests (PHPUnit, Linting, Build)
│   ├── quality.yml            # Analyse de code (PHPStan, Audit)
│   └── deploy-optional.yml    # Déploiement optionnel
└── CI-CD-README.md            # Documentation complète

phpstan.neon                   # Configuration PHPStan

scripts/
├── install-linting-tools.sh   # Script d'installation des outils
├── pre-commit                 # Hook pre-commit pour tests locaux
```

## 🚀 Quick Start

### 1️⃣ Mise en place locale (optionnel mais recommandé)

```bash
# Rendre le script exécutable
chmod +x scripts/install-linting-tools.sh

# Installer les outils optionnels
bash scripts/install-linting-tools.sh
```

### 2️⃣ Configurer le pre-commit hook (optionnel)

```bash
# Copier le hook
chmod +x scripts/pre-commit
cp scripts/pre-commit .git/hooks/pre-commit

# Maintenant, chaque commit lancera les tests localement!
```

### 3️⃣ Push ton code sur GitHub

```bash
git add .
git commit -m "ci: setup CI/CD pipeline"
git push origin main
```

## ✅ Vérifier que tout fonctionne

### Localement (avant de committer):

```bash
# Test PHPUnit
composer test

# Format PHP (auto-fix)
./vendor/bin/pint

# Build frontend
npm run build
```

### Sur GitHub (automatiquement):

1. Va dans **Actions** de ton repo
2. Tu verras les workflows en cours d'exécution
3. Vérifie que tout passe ✅

## 🔐 Configuration optionnelle

### Codecov (rapports de couverture)

```bash
# 1. Inscris-toi sur https://codecov.io
# 2. Connecte ton repo GitHub
# 3. Copie le token
# 4. Va dans: Settings > Secrets > New repository secret
# 5. Ajoute: CODECOV_TOKEN = <ton_token>
```

### Protection de branche (très recommandé!)

**Settings > Branches > Branch protection rules:**
- ✅ Require status checks to pass before merging
- ✅ Require branches to be up to date before merging
- ✅ Require code reviews

Cela garantit que personne ne peut merger du code qui échoue les tests! 🛡️

### SSH Deploy (optionnel pour déploiement auto)

Si tu veux auto-déployer après succès des tests:

```bash
# 1. Génère une clé SSH
ssh-keygen -t ed25519 -f deploy_key

# 2. Va dans Settings > Secrets > New repository secret
# Ajoute:
#   HOST = ton_ip_serveur
#   SSH_USER = utilisateur_serveur
#   SSH_KEY = contenu_de_deploy_key (clé privée)

# 3. Ajoute la clé publique au serveur:
#   ssh-copy-id -i deploy_key.pub user@server
```

## 📊 Badges de statut

Ajoute ces badges à ton `README.md`:

```markdown
## 🚀 CI/CD Status

![Tests](https://github.com/YOUR_USERNAME/benerun/actions/workflows/tests.yml/badge.svg)
![Quality](https://github.com/YOUR_USERNAME/benerun/actions/workflows/quality.yml/badge.svg)
```

## 🐛 Dépannage

### Les workflows n'apparaissent pas?
- ✅ Committe les fichiers `.github/workflows/*.yml`
- ✅ Push vers GitHub
- ✅ Rafraîchis la page Actions (F5)

### Les tests échouent en CI mais pas localement?
```bash
# Réinitialise et relance en environnement test:
php artisan migrate:refresh --seed --env=testing
composer test
```

### Pint refuse de passer?
```bash
# Auto-fixe le code
./vendor/bin/pint
git add .
git commit -m "style: format code"
```

## 📚 Commandes utiles

```bash
# Tester en local avant de committer
composer test

# Analyser le code (si PHPStan installé)
./vendor/bin/phpstan analyse

# Audit de sécurité
composer audit

# Formatter le code
./vendor/bin/pint

# Build frontend
npm run build

# Tout en un
composer test && ./vendor/bin/pint && npm run build
```

## 🎓 Structure des workflows

### `tests.yml` - Exécuté à chaque push/PR
```
Jobs:
  ├─ backend-tests (PHP 8.2 + 8.3)
  │  ├─ PHPUnit tests
  │  ├─ Code coverage
  │  └─ Upload to Codecov
  ├─ linting
  │  └─ Laravel Pint
  └─ frontend-build
     ├─ npm install
     ├─ npm run build
     └─ Vérifier les outputs
```

### `quality.yml` - Analyse de code
```
Jobs:
  ├─ PHPStan (analyse statique)
  └─ Composer Audit (vulnérabilités)
```

---

**Besoin d'aide?** Consulte [.github/CI-CD-README.md](.github/CI-CD-README.md)
