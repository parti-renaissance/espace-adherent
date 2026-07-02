# Historique des sessions

Trace chronologique des sessions de travail significatives sur ce repo. Tenue à jour par `/end-session`.

## Format

Chaque entrée :

```markdown
## YYYY-MM-DD — Titre court de la session

**Contexte** : pourquoi cette session
**Réalisations** : bullets de ce qui a été fait
**Décisions** : décisions prises pendant la session (LOCKED → ADR)
**Lessons** : à reporter dans `runbook/lessons-runtime.md` si durables
**Suite** : prochaines actions identifiées
```

## Sessions

### 2026-05-10 — Bootstrap socle Renaissance

**Contexte** : standardisation de l'AI tooling pour toute l'équipe.

**Réalisations** : socle Renaissance v0.3 installé via `/socle-migrate-existing` (config + doctrine partagée + skills externes via npx + runbooks templates + settings + MCP + pre-commit + docs structure).

**Décisions** : ADR-0001 ouvert sur le partage de l'AI tooling (inversion de la décision historique `.git/info/exclude`).

**Lessons** : voir PR de migration et review pour les premiers ajustements à reporter.

**Suite** : merge de la PR + onboarding équipe (`/socle-init-local` côté chaque dev).
