# Learning loop

Comment les apprentissages d'un projet remontent vers le socle (pour profiter à tous les projets Renaissance).

## 3 lieux de mémoire articulés

| Lieu | Périmètre | Persistance |
|---|---|---|
| `~/.claude/projects/<projet>/memory/` | Auto-memory Claude Code (perso machine, par projet) | Local dev, jamais commité |
| `docs/runbook/lessons-runtime.md` | Lessons projet, équipe | Commité dans le projet |
| `docs/HISTORIQUE_SESSIONS.md` | Bloc par session, contexte d'avancement | Commité dans le projet |

## Convention `lessons-runtime.md`

Format :

```markdown
## YYYY-MM-DD — <slug court>

**Scope** : local (spécifique à ce projet) | candidate-socle (généralisable)

**Contexte** : ce qui s'est passé.
**Lesson** : ce qu'on retient.
**Application** : où/quand on doit s'en servir à l'avenir.
```

Une lesson `candidate-socle` est candidate à remontée via `/socle-promote-learning`.

## Convention `lessons-candidates.md`

Variante : un fichier dédié aux candidates seules (extrait de `lessons-runtime.md`). Format identique. Sert de file d'attente pour la promotion.

## Articulation auto-memory ↔ lessons-runtime (M4)

- L'auto-memory Claude Code détecte des patterns (corrections récurrentes du dev, préférences répétées).
- À `/end-session`, Claude propose explicitement : "voici ce que je vois comme lesson durable basée sur cette session : ...". Le dev tranche (oui/non/reformulation).
- L'auto-memory **propose**, le dev **inscrit** dans `lessons-runtime.md`. Pas d'auto-écriture sans validation.

## Rituel revue mensuelle

- Une fois par mois, review de `lessons-runtime.md` :
  - Quelles candidates promouvoir au socle ?
  - Quelles lessons sont obsolètes (ne plus reproduire) ?
- Pour les candidates : `/socle-promote-learning` ouvre une PR sur le repo socle.

## Propagation vers les projets

- Quand le socle release une nouvelle version (tag), les projets peuvent upgrade via `/socle-update` (différé v0.3) ou manuellement (recopier les nouveaux templates).
