# 1. Installer le projet en local

[Suivant : 2. Architecture du projet](2-Architecture-du-projet.md)

**Renaissance Plateforme** est une API Symfony. Elle repose sur les outils open source suivants :

- [Symfony](https://symfony.com) — framework PHP
- [API Platform](https://api-platform.com) — couche API REST/GraphQL
- [Sonata-Admin](https://sonata-project.org) — back-office d'administration
- [MySQL](https://www.mysql.com) — base de données relationnelle
- [Redis](https://redis.io) — cache et sessions
- [RabbitMQ](https://www.rabbitmq.com) — file de messages asynchrones

---

## Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)

---

## Installation

Le projet utilise un **Makefile** qui encapsule toutes les commandes Docker. Tapez `make help` pour la liste complète.

### 1. Cloner le dépôt

```bash
git clone git@github.com:parti-renaissance/espace-adherent.git
cd espace-adherent
```

### 2. Lancer l'installation complète

```bash
make start
```

Cette unique commande effectue : build des images Docker, démarrage des conteneurs, installation des dépendances (Composer + Yarn), build des assets, initialisation de la base de données (migrations + fixtures) et génération des clés OAuth.

### 3. Vérifier l'installation

```bash
make tu
```

Les tests unitaires doivent passer sans erreur.

---

## Lancer les tests

```bash
make tu   # tests unitaires
make tf   # tests fonctionnels (Behat + PHPUnit)
make test # tous les tests (unitaires + fonctionnels + JS)
```

---

## Commandes utiles

| Commande        | Description                                      |
|-----------------|--------------------------------------------------|
| `make start`    | Installation complète (build, DB, assets, clés)  |
| `make stop`     | Arrêter et supprimer les conteneurs              |
| `make reset`    | Stop + rebuild complet                           |
| `make db`       | Réinitialiser la base et charger les fixtures    |
| `make cc`       | Vider le cache Symfony                           |
| `make tu`       | Tests unitaires                                  |
| `make tf`       | Tests fonctionnels                               |
| `make test`     | Tous les tests                                   |
| `make lint`     | Lint complet (PHP, Twig, YAML, JS)               |
| `make phpcsfix` | Corriger le style PHP                            |
| `make lintfix`  | Corriger tout le style (PHP, JS, Prettier, Twig) |
| `make tty`      | Shell interactif dans le conteneur app           |
| `make help`     | Liste complète des commandes                     |

## Profiling (optionnel)

Le projet est compatible avec [Blackfire](https://blackfire.io). Pour l'activer, décommentez le service `blackfire` dans `docker-compose.override.yml` et renseignez vos clés `BLACKFIRE_CLIENT_ID`, `BLACKFIRE_CLIENT_TOKEN`, `BLACKFIRE_SERVER_ID`, `BLACKFIRE_SERVER_TOKEN`.

[Suivant : 2. Architecture du projet](2-Architecture-du-projet.md)
