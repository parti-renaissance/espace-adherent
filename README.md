<div align="center">
  <h1>Renaissance Plateforme</h1>
  <p>La plateforme politique open source de <a href="https://parti-renaissance.fr">Parti Renaissance</a></p>

  [![CI/CD](https://github.com/parti-renaissance/espace-adherent/actions/workflows/ci-cd.yml/badge.svg?branch=master)](https://github.com/parti-renaissance/espace-adherent/actions/workflows/ci-cd.yml)
  [![CodeQL](https://github.com/parti-renaissance/espace-adherent/workflows/CodeQL/badge.svg)](https://github.com/parti-renaissance/espace-adherent/actions)
  [![CodeFactor](https://www.codefactor.io/repository/github/parti-renaissance/espace-adherent/badge)](https://www.codefactor.io/repository/github/parti-renaissance/espace-adherent)
  [![License: GPL-3.0](https://img.shields.io/badge/License-GPL--3.0-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
</div>

---

## Présentation

Renaissance Plateforme est le cœur technique de l'engagement numérique de **Parti Renaissance**. Il propulse l'ensemble des interfaces militantes du parti — à commencer par [l'app mobile Vox](https://github.com/parti-renaissance/espace-militant).

Ce projet est open source depuis l'origine, dans la continuité de la philosophie portée par En Marche ! en 2017 : un mouvement politique appartient à ses membres, et son code aussi.

---

## Stack

| | |
|---|---|
| **Langage** | PHP 8.4 |
| **Framework** | Symfony 7.4 |
| **API** | API Platform 4 |
| **Base de données** | MySQL + Doctrine ORM |
| **Cache / Queue** | Redis + RabbitMQ |
| **Recherche** | Algolia |
| **Stockage** | Google Cloud Storage |
| **Auth** | OAuth2 + 2FA |
| **Monitoring** | Sentry |

---

## Architecture

Le projet suit une approche **Domain-Driven Design** — chaque domaine métier est un module indépendant dans `src/` :

```
src/
├── Adherent/       # Profil sympathisant
├── Event/          # Événements et rassemblements
├── Pap/            # Porte-à-porte
├── Phoning/        # Campagnes téléphoniques
├── JeMengage/      # Hub d'actions terrain
├── VotingPlatform/ # Votes internes
├── Donation/       # Dons et contributions
├── OAuth/          # Authentification
├── Mailer/         # Communications
└── ...             # 80+ modules — voir docs/architecture.md
```

---

## Installation locale

**Prérequis :** Docker, PHP 8.4, Composer, Node.js 20+

```bash
git clone https://github.com/parti-renaissance/espace-adherent.git
cd espace-adherent

cp .env .env.local          # compléter avec vos valeurs locales
docker compose up -d
composer install

php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
php bin/console app:oauth:generate-keys
```

📖 [docs/getting-started.md](docs/getting-started.md)

---

## Contribuer

Les issues [`good first issue`](https://github.com/parti-renaissance/espace-adherent/issues?q=label%3A%22good+first+issue%22) sont de bons points d'entrée. Lisez [CONTRIBUTING.md](CONTRIBUTING.md) avant d'ouvrir une PR.

```bash
php bin/phpunit
php bin/phpstan analyse
php vendor/bin/php-cs-fixer fix --dry-run
```

---

## Sécurité

Vous avez découvert une vulnérabilité ? Ne créez pas d'issue publique.
→ [SECURITY.md](SECURITY.md) · `security@parti-renaissance.fr`

---

## Licence

GNU GPL-3.0 — voir [LICENSE](LICENSE).
