# Agent onboarding (mode mixed | human-team)

Pour qu'un agent Claude qui rejoint le projet (ou un dev humain qui démarre) soit productif rapidement.

## Lectures critiques (ordre)

1. `CLAUDE.md` du projet — doctrine, anti-patterns, garde-fous, decision tree.
2. `docs/runbook/git-workflow.md` — comment commiter/PR/merger.
3. `docs/runbook/code-review-protocol.md` — comment review.
4. `docs/runbook/secrets-handling.md` — comment manipuler les tokens.
5. `docs/HISTORIQUE_SESSIONS.md` (3 derniers blocs) — où on en est.
6. `docs/issues/` — bugs actifs.
7. ADR principaux dans `docs/adrs/` — décisions structurantes.

## Pour un dev humain

- Lance `/socle-init-local` pour configurer ton profil (harness-pure ou pair).
- Lis CLAUDE.local.md.example pour comprendre le système 3 niveaux.
- Demande à un membre de l'équipe un walkthrough du module sur lequel tu commences.

## Pour un agent Claude qui prend le relais

- `/start-session` : protocole automatisé (3 derniers blocs, issues actives, dernier run CI).
- Skill `using-superpowers` activée par défaut.
- Decision tree CLAUDE.md identifie la skill spécifique selon la tâche.
- Si scope inconnu : poser des questions au dev avant de commencer.

## Erreurs classiques

- Coder sans lire CLAUDE.md → ignore les anti-patterns du projet.
- Skip le `/start-session` → manque le contexte des 3 dernières sessions.
- Ne pas demander de clarification → diverge de l'intention du dev.
