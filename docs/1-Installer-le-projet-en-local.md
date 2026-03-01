# 1. Installer le projet en local

[Suivant : 2. Architecture du projet](2-Architecture-du-projet.md)

**Renaissance Plateforme** est une API Symfony. Elle repose sur les outils open source suivants :

- [Symfony](https://symfony.com) — framework PHP
- [API Platform](https://api-platform.com) — couche API REST/GraphQL
- [MySQL](https://www.mysql.com) — base de données relationnelle
- [Redis](https://redis.io) — cache et sessions
- [RabbitMQ](https://www.rabbitmq.com) — file de messages asynchrones

---

## Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (ou [OrbStack](https://orbstack.dev) sous macOS)
- PHP 8.4 avec les extensions `intl`, `gd`, `pdo_mysql`, `amqp`
- [Composer](https://getcomposer.org)
- Node.js 20+

---

## Installation

### 1. Cloner le dépôt

```bash
git clone git@github.com:parti-renaissance/espace-adherent.git
cd espace-adherent
```

### 2. Configurer les variables d'environnement

```bash
cp .env .env.local
```

Éditez `.env.local` et renseignez les valeurs nécessaires. Les clés requises en développement sont :

| Variable | Description |
|---|---|
| `DATABASE_URL` | URL de connexion MySQL (préconfigurée pour Docker) |
| `REDIS_URL` | URL Redis (préconfigurée pour Docker) |
| `APP_SECRET` | Secret Symfony — générez-en un avec `openssl rand -hex 32` |
| `ALGOLIA_APP_ID` / `ALGOLIA_API_KEY` | Clés Algolia — obtenez un compte de test sur [algolia.com](https://www.algolia.com) |
| `SENTRY_DSN` | Optionnel en local, laissez vide |

> Les autres variables (GoCardless, Mailjet, etc.) peuvent être laissées vides pour un environnement de développement basique.

### 3. Démarrer les containers

```bash
docker compose up -d
```

Cela démarre MySQL, Redis et RabbitMQ.

### 4. Installer les dépendances

```bash
composer install
```

### 5. Initialiser la base de données

```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### 6. Générer les clés OAuth

```bash
php bin/console app:oauth:generate-keys
```

### 7. Vérifier l'installation

```bash
php bin/phpunit --testsuite unit
```

Tous les tests unitaires doivent passer sans erreur.

---

## Accéder à l'API en local

L'API est disponible sur `http://localhost:8000`.

La documentation interactive (Swagger UI) est disponible sur `http://localhost:8000/api`.

Comptes de test disponibles après le chargement des fixtures :

```
adherent@example.com / secret!12345   # compte sympathisant
admin@example.com / secret!12345      # compte administrateur
```

---

## Lancer les tests

```bash
php bin/phpunit              # tous les tests
php bin/phpunit tests/Event/ # tests d'un module spécifique
```

---

## Profiling (optionnel)

Le projet est compatible avec [Blackfire](https://blackfire.io). Pour l'activer, décommentez le service `blackfire` dans `docker-compose.override.yml` et renseignez vos clés `BLACKFIRE_CLIENT_ID`, `BLACKFIRE_CLIENT_TOKEN`, `BLACKFIRE_SERVER_ID`, `BLACKFIRE_SERVER_TOKEN`.

```bash
docker compose run --rm app blackfire run php bin/phpunit
```

[Suivant : 2. Architecture du projet](2-Architecture-du-projet.md)
