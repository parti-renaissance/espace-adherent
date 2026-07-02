# docs/issues/

Trace locale des bugs et issues techniques significatifs (en complément du tracker GitHub Issues).

Sert de mémoire ingénierie quand un bug a une histoire complexe (investigation longue, root cause non triviale, plusieurs PRs, runbook associé). Chaque fichier reste lisible hors-conversation.

## Format

`docs/issues/<YYYY-MM-DD>-<slug>.md` pour les issues actives. Une fois résolues, déplacer dans `_resolved/`.

```markdown
# <Titre court de l'issue>

**Statut** : actif | en investigation | bloquant | résolu
**Repro** : étapes minimales
**Impact** : qui/quoi est touché, gravité
**Investigation** : trace (commits, hypothèses, infirmations)
**Root cause** : (à remplir)
**Fix** : PR/commit + résumé
**Lessons** : à reporter dans `runbook/lessons-runtime.md` si durables
```

## Issues actives

(aucune)

## Issues résolues

Voir `_resolved/`.
