# Risky actions — M3

Actions dont les conséquences sont irréversibles ou à blast radius élargi. Claude doit confirmer explicitement avant chaque exécution.

## Modes

- **`ask`** (paranoid, défaut Renaissance) : confirmation explicite à chaque commande risquée. Double confirmation pour les actions irréversibles (drop DB, force-push, delete repo).
- **`yolo`** (default uniquement) : exécution sans confirmation. **Déconseillé** sauf solo + sandbox.

## Liste paramétrable

Définie dans `.claude/socle/config.yaml` champ `risky_actions` :

```yaml
risky_actions:
  - command: "git push origin main"
    requires: "no-direct-push-to-main-confirm"
  - command: "gh release create"
    requires: "version-bump-changelog-ready-confirm"
  - command: "gcloud sql instances delete"
    requires: "double-confirm-with-instance-name"
```

## Pattern dry-run

Pour toute action destructrice qui supporte un dry-run, **dry-run d'abord**, puis confirmation, puis exécution.

Exemples :
- `gcloud sql ... --dry-run` puis confirmer.
- `git rebase --interactive` (preview) puis exécuter.
- `terraform plan` puis `terraform apply`.

## Liste de base (toujours `ask`)

- `rm -rf` (sauf scopes éphémères tmp)
- `git push --force` sur main (interdit en deny rule)
- `git reset --hard origin/<branch>`
- `gcloud sql ...delete`
- `gh repo delete`
- `gh release create` (notif équipe)
- `gh pr merge` direct (sans review)
- `npm publish`, `pnpm publish`
- DROP TABLE, TRUNCATE, DELETE sans WHERE

## Surcouche dev local

Le `CLAUDE.local.md` peut **ajouter** des risky_actions personnels (ex: "git push sur ma branche perso `victor/exp-*` requires confirm"). Jamais en retirer.
