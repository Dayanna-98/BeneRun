# 🚀 CI/CD Pipeline pour BeneRun

Cette documentation explique la configuration CI/CD mise en place pour le projet BeneRun.

## 📋 Workflows disponibles

### 1. **Tests CI/CD** (`.github/workflows/tests.yml`)

Lance automatiquement les tests à chaque `push` ou `pull request` sur `main` et `develop`.

#### Ce qu'il teste:
- ✅ **Tests PHPUnit** - Backend Laravel avec couverture de code
- ✅ **Build Vite** - Frontend (Tailwind CSS + assets)
- ✅ **Linting PHP** - Laravel Pint pour la qualité du code

#### Matrice PHP:
- PHP 8.2
- PHP 8.3

#### Environnement de test:
- Base de données: SQLite en mémoire
- Cache: Array (rapidité)
- Queue: Sync (mode synchrone)

### 2. **Code Quality** (`.github/workflows/quality.yml`)

Analyse la qualité du code et les vulnérabilités de sécurité.

#### Outils utilisés:
- **PHPStan** - Analyse statique PHP (détecte les bugs potentiels)
- **Composer Audit** - Vérifie les vulnérabilités dans les dépendances

## 🔧 Configuration requise

### Pour le succès des pipelines:

1. **PHPUnit** - Tests doivent être verts
```bash
composer test
```

2. **Laravel Pint** - Code doit être formaté
```bash
./vendor/bin/pint
```

3. **Frontend** - Doit compiler avec Vite
```bash
npm run build
```

## 📊 Résultats & Rapports

### Coverage (Couverture de code)
Les résultats sont uploadés sur **Codecov** automatiquement (si token configuré).

Pour activer:
1. Connecte-toi à https://codecov.io
2. Ajoute le token à GitHub Secrets: `Settings` > `Secrets and variables` > `Actions`
3. Ajoute: `CODECOV_TOKEN = <ton_token>`

### Status Badges
Ajoute à ton `README.md`:

```markdown
![Tests](https://github.com/OWNER/REPO/actions/workflows/tests.yml/badge.svg)
![Quality](https://github.com/OWNER/REPO/actions/workflows/quality.yml/badge.svg)
```

## 🛑 Dépannage

### Les tests échouent?

**1. Migrations échouent**
```bash
php artisan migrate:refresh --seed
php artisan test
```

**2. Pint refuse le format**
```bash
./vendor/bin/pint  # Auto-fix le code
git add .
git commit -m "style: format code with pint"
```

**3. Build Vite échoue**
```bash
npm install
npm run build
```

## 📝 Fichiers de configuration

- `.env.example` - Copié automatiquement en `.env` pour les tests
- `phpunit.xml` - Configuration PHPUnit (déjà présente)
- `package.json` - Scripts npm pour le build
- `composer.json` - Dépendances PHP et scripts Composer

## 🚀 Prochaines étapes optionnelles

### Ajouter un test de sécurité OWASP
```yaml
- name: Run OWASP Dependency Check
  uses: dependency-check/Dependency-Check_Action@main
```

### Ajouter SonarQube
```yaml
- name: SonarQube Scan
  uses: SonarSource/sonarcloud-github-action@master
```

### Ajouter un workflow de déploiement
Crée `.github/workflows/deploy.yml` pour déployer automatiquement sur réussite des tests.

---

**Questions?** Consulte la [documentation GitHub Actions](https://docs.github.com/en/actions)
