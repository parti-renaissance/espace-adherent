# Multi-agent coordination (mode harness-solo|mixed + scope structured)

Quand plusieurs agents Claude (ou worktrees parallèles) avancent en même temps sur un projet, leur coordination passe par les fichiers du projet, pas par communication directe.

## Règles dures

- **Un seul agent par worktree à la fois.** Pas de partage d'état mémoire entre agents.
- **Fichiers partagés sont read-only** pour les agents concurrents (CLAUDE.md, `.claude/socle/`, `docs/adrs/`). Modifs centralisées sur la branche principale, ré-pull sur les worktrees.
- **`docs/HISTORIQUE_SESSIONS.md` est append-only**. Conflits de merge = un agent fait un block, l'autre lit avant d'append.
- **Pas deux agents sur la même PR.** Si deux agents touchent le même module, un seul à la fois ; l'autre attend ou bosse sur un autre module.

## Patterns

- **Worktrees Git** (`using-git-worktrees` Superpowers) : un dossier par agent, branche dédiée. Évite les conflits.
- **Dispatching** (`dispatching-parallel-agents` Superpowers) : agent maître crée des sous-tâches, sous-agents la font, maître agrège. Coordination explicite.
- **Subagent-driven** (`subagent-driven-development` Superpowers) : pour les explorations massives en parallèle (recherche cross-codebase, analyse impact).

## Si deux agents s'écrasent

1. Stop tout (`SIGINT`).
2. `git status` sur chaque worktree pour voir qui touche quoi.
3. Décider lequel garde la main, l'autre rebase.
4. Ajouter une lesson dans `lessons-runtime.md` : "X est trop concurrentiel, à isoler en worktree".
