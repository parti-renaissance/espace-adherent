# Test protocol (scope: structured)

TDD obligatoire pour toute feature ou bugfix qui modifie du code applicatif. Skill `test-driven-development` (Superpowers) à activer en HARD-GATE pre-merge.

## Règles dures

- **Test avant code** pour les nouvelles features. Pas d'exception sauf prototype explicite (jeté).
- **Test régression** pour les bugfixes. Le test reproduit le bug avant le fix.
- **CI verte avant merge** : tests bloquants en PR.
- Skill `verification-before-completion` activée avant tout message final "OK / livré / mergé / déployé".

## Stack-spécifique

- **Python** : `pytest`. Couverture mesurée via `pytest-cov`. Snapshot dans `coverage.xml`.
- **JS/TS (Next.js, React Native)** : `vitest` ou `jest`. E2E via `playwright` si UI.
- **PHP/Symfony** : `phpunit`. `composer test` comme cible standard.

## Structure suggérée

- `tests/unit/` — pur, sans I/O.
- `tests/integration/` — DB, API, fichiers. Conteneurs éphémères.
- `tests/e2e/` — flow utilisateur complet.
- Pas de mock de la DB en intégration (cf. lessons appris : mock/prod divergence masque souvent les bugs).

## Specs TDD format daté (T7)

`docs/specs/<date>-<slug>-design.md` au format §9.6.7. Activé par défaut si `scope: structured`.
