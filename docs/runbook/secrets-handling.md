# Secrets handling

## Règles dures

- ❌ **Aucun secret committé** dans le repo (token, password, key, certificat). Audit pre-commit obligatoire.
- ❌ **Aucun Service Account JSON** GCP. Workload Identity Federation (WIF) pour CI/CD.
- ❌ **Pas de secret en variable d'env Claude Code** non scopée. Préférer 1Password CLI.

## Stratégie en couches

1. **1Password CLI (`op`)** — préférentiel pour dev local. Format de référence : `op://<vault>/<item>/<field>`.
2. **GitHub Secrets** — pour CI/CD. Configurés via `gh secret set`.
3. **GCP Secret Manager** — pour runtime sur Cloud Run / Cloud Functions. Accès via WIF.
4. **`.env.local` non commité** (gitignoré) — fallback dernier recours, à éviter si possible.

## Vérification au socle-init-local

`/socle-init-local` (Q2) demande "tu utilises 1Password CLI ?". Si oui, vérifie que `op signin` fonctionne et stocke les références dans `profile.local.yaml` :

```yaml
mcp_overrides:
  clickup_token_ref: "op://Victor/clickup-personal/token"
  figma_token_ref: "op://Victor/figma-personal/token"
```

## Dans `.mcp.json` projet

Format : `${OP_REF:op://...}` ou substitution équivalente. Jamais le token en clair.

## Si un secret leak

1. **Révoquer immédiatement** (interface du provider, ou rotation manuelle).
2. **Rebase + force-push** pour retirer du log git si le commit n'est pas encore pushé. Si pushé : history rewrite + alert tous les contributors.
3. **Audit** : grep `git log -p` pour vérifier qu'il n'y a pas d'autres occurrences.
4. **Lesson** dans `lessons-runtime.md` : pourquoi le pre-commit n'a pas attrapé.
