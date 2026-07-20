# Git workflow — flow simple (P8)

Branche `main` unique. PR pour features/fixes. Auto-deploy staging sur merge `main`. Tag GitHub release = deploy prod.

## Règles dures

- Conventional commits : `feat:`, `fix:`, `docs:`, `chore:`, `refactor:`, `test:`. Sujet < 72 caractères.
- Pas de commit direct sur `main` — toujours via PR.
- **Toujours** squash-merge — jamais merge commit, jamais rebase merge. Historique `main` strictement linéaire (1 PR = 1 commit). `gh pr merge --squash` (le repo GitHub doit être configuré "Allow squash merging only" côté Settings).
- CI verte avant merge (cf. `ci-cd-protocol.md`).
- Tag de release créé via `gh release create v<x.y.z> --notes "<extrait CHANGELOG>"`. C'est le tag qui déclenche le deploy prod.

## Recettes de rollback

- **Revert un commit** : `git revert <sha>` sur main puis push (passe par PR si CI obligatoire).
- **Revert une PR mergée** : `gh pr revert <n>` ou créer une PR qui revert le squash commit.
- **Redeploy ancien tag prod** : `gh workflow run deploy-prod.yml -f ref=v<x.y.z-1>` (ou re-tag du SHA d'avant).
- **Restore backup DB** : selon stack (cf. runbook stack-spécifique). Toujours dump avant deploy prod.
- **Force-push sur main** : interdit (deny rule dans `.claude/settings.json`). Utiliser revert.

## Branches utilitaires

- `victor/exp-*` : branches d'expérimentation perso (push libre, jamais merge sur main).
- `learning/<projet>-<slug>` : créée automatiquement par `/socle-promote-learning` pour remontée vers le repo socle.
