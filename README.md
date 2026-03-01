<div align="center">
  <img src="docs/assets/header.png" alt="Renaissance Plateforme" width="60%">
  <br/><br/>

  [![CI/CD](https://github.com/parti-renaissance/espace-adherent/actions/workflows/ci-cd.yml/badge.svg?branch=master)](https://github.com/parti-renaissance/espace-adherent/actions/workflows/ci-cd.yml)
  [![CodeQL](https://github.com/parti-renaissance/espace-adherent/workflows/codeql-analysis.yml/badge.svg)](https://github.com/parti-renaissance/espace-adherent/actions)
  [![CodeFactor](https://www.codefactor.io/repository/github/parti-renaissance/espace-adherent/badge)](https://www.codefactor.io/repository/github/parti-renaissance/espace-adherent)
  [![License: GPL-3.0](https://img.shields.io/badge/License-GPL--3.0-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
</div>

---

**Renaissance Plateforme** est le back-end et l'API qui alimentent les services numériques de Parti Renaissance : événements, actions de terrain, communications, votes internes et dons. Il est consommé principalement par [l'app mobile Vox](https://github.com/parti-renaissance/espace-militant).

Nous avons choisi l'open source car cela s'accorde avec notre idée d'un mouvement qui n'existe que par ses membres — une conviction portée depuis En Marche ! en 2017, et que nous continuons avec Renaissance.

---

## Comment puis-je aider ?

Toutes les contributions comptent : code, tests, documentation, remontées de bugs ou retours d'usage.

**1. Trouver une tâche**
Parcourez les [issues ouvertes](https://github.com/parti-renaissance/espace-adherent/issues) pour trouver quelque chose qui vous intéresse. Pour un changement significatif, **ouvrez d'abord une issue** pour en discuter avec l'équipe — cela évite les PRs orphelines.

**2. Installer le projet**
Suivez le [guide d'installation](docs/1-Installer-le-projet-en-local.md). Les clés d'API nécessaires en développement y sont documentées.

**3. Soumettre une PR**
Lisez [CONTRIBUTING.md](CONTRIBUTING.md) pour les conventions de commit, les standards de code et le [processus de développement](docs/3-Processus-de-developpement.md). Vérifiez que les tests passent avant de soumettre :

```bash
php bin/phpunit              # tests unitaires et fonctionnels
php bin/phpstan analyse      # analyse statique
php vendor/bin/php-cs-fixer fix --dry-run  # style
```

Les PRs sont reviewées par l'équipe tech de Renaissance, en général sous **2 à 5 jours ouvrés**.

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
├── Event/          # Événements et rassemblements
├── Pap/            # Porte-à-porte
├── Phoning/        # Campagnes téléphoniques
├── JeMengage/      # Hub d'actions terrain
├── VotingPlatform/ # Votes internes du parti
├── Donation/       # Dons et contributions
├── OAuth/          # Authentification et tokens
├── Mailer/         # Communications e-mail
├── Adherent/       # Profil sympathisant
└── ...             # 80+ modules — voir docs/2-Architecture-du-projet.md
```

---

## Installation locale

**Prérequis :** Docker, PHP 8.4, Composer, Node.js 20+

```bash
git clone https://github.com/parti-renaissance/espace-adherent.git
cd espace-adherent

cp .env .env.local   # compléter avec vos valeurs — voir docs/1-Installer-le-projet-en-local.md
docker compose up -d
composer install

php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
php bin/console app:oauth:generate-keys
```

> 📖 Guide complet : [docs/1-Installer-le-projet-en-local.md](docs/1-Installer-le-projet-en-local.md)

---

## Sécurité

Vous avez découvert une vulnérabilité ? Ne créez pas d'issue publique.
Contactez-nous à `security@parti-renaissance.fr`

---

## Licence

GNU GPL-3.0 — voir [LICENSE](LICENSE).
