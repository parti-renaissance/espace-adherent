# Task tracking (scope: structured) — M2

Persistance des todos inter-sessions. Évite que Claude oublie où il en est entre 2 sessions.

## 2 niveaux

1. **TodoWrite in-session** — outil natif Claude Code, mémoire de session uniquement.
2. **`docs/tasks-active.md` inter-sessions** — fichier markdown persistant, commité.

## Format `tasks-active.md`

```markdown
# Tasks actives

## En cours

- [ ] Implémenter X (assigné: Victor, démarré 2026-05-01)
- [ ] Bug Y dans module Z (issue: docs/issues/2026-05-08-Y.md)

## Backlog (priorité)

- [ ] Refacto auth middleware
- [ ] Doc API publique

## Bloqué

- [ ] Migration DB v3 (dépend: validation produit)
```

## Mise à jour

- À `/start-session` : Claude lit `tasks-active.md`, propose de reprendre la première tâche "en cours".
- À `/end-session` : Claude met à jour le statut des tâches touchées (cocher si faites, ajouter notes si avancement partiel).
- Une tâche **terminée** disparaît de `tasks-active.md` et apparaît dans le bloc `HISTORIQUE_SESSIONS.md` de la session.

## Différence avec `docs/issues/`

- `tasks-active.md` = todos d'avancement (features, refactos, etc.).
- `docs/issues/` = bugs identifiés (avec format §9.6.3 PRD : statut, priorité, symptômes, hypothèses, tentatives).

Une tâche peut référencer une issue, et inversement.
