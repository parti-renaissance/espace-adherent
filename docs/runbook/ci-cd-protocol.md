# CI/CD protocol

Auto-deploy staging sur merge `main`. Deploy prod sur tag GitHub release.

## Workflows attendus

- `.github/workflows/ci.yml` : lint + tests sur chaque PR. **Bloquant pour merge**.
- `.github/workflows/deploy-staging.yml` : déclenché sur push sur `main`. Deploy automatique vers staging.
- `.github/workflows/deploy-prod.yml` : déclenché sur tag `v*`. Deploy vers prod.
- `.github/workflows/deploy-preview.yml` (optionnel) : déclenché sur draft release. Pré-prod.

## Règles dures

- **Jamais merger sans CI verte.** Confirmation explicite si force-merge nécessaire (rare).
- **Secrets via GitHub Secrets** (jamais dans le code, jamais dans les variables CI en clair). Cf. `secrets-handling.md`.
- Workload Identity Federation (WIF) pour GCP — pas de Service Account JSON.
- Notif Slack/Telegram sur deploy prod (succès et échec).

## Slash commands

- `/check-ci` : voir le dernier run et le statut. Si échec, propose `openai/skills --skill gh-fix-ci`.
- `/before-push` : checklist locale avant `git push` (status propre, tests, lint, scan secrets).

## Si CI échoue

1. `gh run view <id>` pour voir les détails.
2. Si lint/format : `pre-commit run --all-files` localement, push fix.
3. Si test : reproduire localement, fixer, push.
4. Si flaky : marquer le test, créer une issue, ne pas force-merge.
