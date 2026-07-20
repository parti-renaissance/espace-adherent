---
name: renaissance-symfony-conventions
description: Conventions Symfony Renaissance (placeholder v0.2). Sera enrichie via la boucle d'apprentissage à partir des projets pilotes (plateforme, espace-adherent). À invoquer pour toute édition de code Symfony qui touche aux conventions internes (controllers, entities, security, twig).
stacks: [php-symfony]
---

# renaissance-symfony-conventions

> **Placeholder v0.2** — contenu à enrichir via `/socle-promote-learning` à partir des lessons des projets plateforme et espace-adherent.

## Quand l'invoquer

- Édition de code Symfony : controllers, entities, services, security voters, twig templates.
- Avant un PR qui touche les conventions de nommage / architecture.
- Onboarding d'un dev sur un projet Symfony Renaissance.

## Conventions à venir (ébauche)

### Architecture
- Pattern Action (controllers en classes mono-action) à confirmer.
- Repositories vs DataMappers : décision projet.

### Nommage
- Entities : nom singulier (`Militant`, pas `Militants`).
- Repositories : `<Entity>Repository`.
- Voters : `<Entity>Voter` avec const ATTRIBUTES.

### Tests
- `tests/Unit/` (pur), `tests/Integration/` (DB), `tests/Functional/` (HTTP).
- `phpunit.xml.dist` à la racine.
- Couverture minimum 70% sur le code applicatif.

### Twig
- Macro pour blocs réutilisables.
- Pas de logique métier dans les templates.

## TODO

- [ ] Documenter convention controllers (Action vs CRUD).
- [ ] Documenter convention services (DI tags, autowiring).
- [ ] Documenter convention security (voters, attribute auth).
- [ ] Hisser les anti-patterns identifiés sur plateforme.
- [ ] Promouvoir au socle après stabilisation (via `/socle-promote-learning`).

## Notes

Ce fichier est un placeholder honnête. Mieux vaut signaler qu'il manque que de pretendre couvrir tout. Ne pas l'utiliser comme source de vérité tant qu'il n'est pas enrichi.
