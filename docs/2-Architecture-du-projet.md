# 2. Architecture du projet

[Précédent : 1. Installer le projet en local](1-Installer-le-projet-en-local.md) —
[Suivant : 3. Processus de développement](3-Processus-de-developpement.md)

---

## Structure des dossiers

```
espace-adherent/
├── config/         # Configuration Symfony (services, routes, sécurité)
├── docs/           # Documentation développeur
├── migrations/     # Migrations Doctrine
├── src/            # Code source — organisé par thématique métier
├── tests/          # Tests unitaires et fonctionnels
├── features/       # Tests BDD (Behat)
└── var/            # Cache, logs (ignoré par Git)
```

---

## Organisation du code source

Le projet est un **monolithe Symfony mature** dont le code est organisé par thématique métier dans `src/`. Les entités, services et repositories sont partagés entre dossiers selon les besoins fonctionnels.

```
src/
├── Event/          # Événements et rassemblements
├── Pap/            # Porte-à-porte
├── Phoning/        # Campagnes téléphoniques
├── JeMengage/      # Hub d'actions terrain
├── VotingPlatform/ # Votes internes du parti
├── Donation/       # Dons et contributions financières
├── OAuth/          # Authentification et gestion des tokens
├── Mailer/         # Communications e-mail (via Mandrill)
├── Adherent/       # Profil et compte sympathisant
├── Committee/      # Comités locaux
├── Api/            # Contrôleurs API Platform
├── Admin/          # Back-office Sonata
└── ...             # 80+ dossiers
```

Au sein d'un dossier, on retrouve généralement :

| Dossier                           | Rôle                                 |
|-----------------------------------|--------------------------------------|
| `Entity/`                         | Entités Doctrine (modèle de données) |
| `Handler/`                        | Logique métier — command handlers    |
| `Repository/`                     | Requêtes base de données             |
| `Listener/` ou `EventSubscriber/` | Réaction aux événements Symfony      |
| `Controller/` ou `Api/`           | Points d'entrée HTTP                 |

---

## Couche API

L'API est construite avec **API Platform 4**. Les ressources sont déclarées via des attributs PHP directement sur les entités ou des classes dédiées dans `src/*/Api/`.

Documentation interactive disponible sur `/api` en environnement de développement.

---

## Authentification

Le projet implémente **OAuth 2.0** (Authorization Code) pour l'application mobile Renaissance App. Les tokens sont gérés par le module `src/OAuth/`.

La double authentification (2FA) est disponible pour les comptes administrateurs.

---

## Asynchronisme

Les traitements longs (envoi d'e-mails, webhooks, notifications) passent par **Symfony Messenger** avec **RabbitMQ** comme transport.

---

## Outils notables

| Outil                                                                           | Usage                                          |
|---------------------------------------------------------------------------------|------------------------------------------------|
| [Sentry](https://sentry.io)                                                     | Monitoring des erreurs en production           |
| [Algolia](https://www.algolia.com)                                              | Recherche full-text                            |
| [Google Cloud Storage](https://cloud.google.com/storage)                        | Stockage des fichiers et médias                |
| [Mailchimp & Mandrill](https://mailchimp.com/fr/features/transactional-email/)  | Envoi d'e-mails de campagne et transactionnels |
| [GoCardless](https://gocardless.com)                                            | Prélèvements bancaires (cotisations)           |

[Précédent : 1. Installer le projet en local](1-Installer-le-projet-en-local.md) —
[Suivant : 3. Processus de développement](3-Processus-de-developpement.md)
