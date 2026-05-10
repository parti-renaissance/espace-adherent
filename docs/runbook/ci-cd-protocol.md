# CI/CD protocol

Auto-deploy staging sur merge `master`. Deploy prod sur tag GitHub release.

## Workflows attendus

- `.github/workflows/ci.yml` : lint + tests sur chaque PR. **Bloquant pour merge**.
- `.github/workflows/deploy-staging.yml` : déclenché sur push sur `master`. Deploy automatique vers staging.
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

## Optimisation des coûts CI

GitHub Actions facture aux minutes-runner consommées. Sur un repo actif (10+ PR par semaine, plusieurs workflows par push), une CI naïve peut coûter 200-400 €/mois. Les 7 patterns ci-dessous, dérivés des optimisations Sprint 12 Kiosque (2026-05-06), réduisent typiquement la facture de 40-60 % en gardant la même couverture.

### Pattern 1 — Path-based triggers (PR docs-only ne déclenchent pas le full CI)

Filtrer les PR par chemins touchés. Une modif `docs/**` ne lance pas lint+tests Python+Node — un workflow `docs-checks.yml` séparé fait juste les gates docs.

```yaml
on:
  pull_request:
    paths:
      - 'src/**'
      - 'tests/**'
      - 'pyproject.toml'        # ou package.json / composer.json selon stack
      - '.github/workflows/lint-test.yml'
  push:
    branches: [staging, canary, master]
```

**Effet** : ~30-40 % des PR Renaissance touchent uniquement `docs/`, `CLAUDE.md`, `.claude/skills/`. Économie directe sur ces PR — 0 minutes au lieu de 2-3 minutes par run.

**Bonne pratique** : lister explicitement le path du workflow lui-même (`'.github/workflows/lint-test.yml'`) pour qu'un changement du workflow déclenche bien sa propre exécution.

### Pattern 2 — Concurrency `cancel-in-progress`

Annule les runs précédents sur la même branche dès qu'un nouveau push arrive.

```yaml
concurrency:
  group: ${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: true   # POUR : lint, tests, docs-checks
```

**Distinction critique** :

| Type de workflow | `cancel-in-progress` | Raison |
|---|---|---|
| `lint`, `tests`, `docs-checks` | `true` | Re-exécutables, pas d'effet de bord. Si on push 5 fois, seul le dernier run compte. |
| `deploy-staging`, `deploy-canary`, `deploy-prod` | `false` | Doit terminer (migrations DB, push image, mise à jour service). Annulation = état incohérent. |
| `e2e-staging`, `smoke-test`, `migration` | `false` | Tests/migrations en cours doivent finir, sinon flakiness ou état partiel. |

**Effet** : sur les workflows en `true`, économie typique 40-80 % quand un dev pousse en rafale. Sur les workflows en `false`, garde-fou de fiabilité.

### Pattern 3 — Cache pip / npm / cache custom (mypy, etc.)

Caches officiels intégrés dans les actions `setup-*`, plus caches custom pour les outils qui en bénéficient.

```yaml
# Cache pip officiel (intégré à setup-python)
- uses: actions/setup-python@v5
  with:
    python-version: "3.11"
    cache: pip
    cache-dependency-path: pyproject.toml

# Cache custom mypy (gros gain : ~50s → ~5s sur strict)
- uses: actions/cache@v4
  with:
    path: .mypy_cache
    key: mypy-${{ runner.os }}-py3.11-${{ hashFiles('pyproject.toml', 'src/**/*.py') }}
    restore-keys: |
      mypy-${{ runner.os }}-py3.11-
```

**Cascade `restore-keys`** : si la clé exacte n'existe pas (ex: un `.py` a changé), récupérer un cache partiel matching le préfixe. mypy invalidera ses entries internement — ça reste plus rapide qu'une cold start.

**Effet** : install pip ~5s vs ~30s. mypy strict ~50s vs ~3 min. npm ci ~10s vs ~60s.

### Pattern 4 — Split job monolithique en jobs parallèles

Au lieu d'un seul job qui fait lint → typecheck → tests-unit → tests-integration → node-tests séquentiellement, en faire 5 jobs parallèles. Le total devient `max(durée_du_job_le_plus_lent)` au lieu de la somme.

**Avant** : 1 job séquentiel ~1 min 50.
**Après** : 5 jobs parallèles, le plus lent ~50s. Gain : -45 % sur le wall-clock.

**Effet sur la facture** : neutre côté minutes-runner (la somme reste la même). Mais **gain énorme en feedback dev** : 50s d'attente au lieu de 1 min 50. Le dev itère plus vite, pousse moins de runs ratés.

**Caveat** : chaque job repaie le coût de `actions/checkout` + `setup-*` (~10-20s par job). À ne faire que si les jobs prennent > 1 min chacun. Sinon, garder monolithique.

### Pattern 5 — Parallélisation des tests dans un même job (pytest-xdist)

Sur stack Python, paralléliser les tests unit sur les cores du runner.

```yaml
- run: pytest -m "not integration and not e2e" -n auto --dist=loadfile --tb=short
```

- `-n auto` : utilise tous les cores (typiquement 2 sur GHA standard runner).
- `--dist=loadfile` : un même fichier de tests reste dans 1 worker — évite race conditions sur fixtures `scope=module`.
- **SAFE pour unit-only**. Les tests integration partagent souvent une DB / ressources externes → garder séquentiels dans un autre job.

**Effet** : ~50 % plus rapide sur tests unit. Sur Kiosque : 25s → 7s.

**Équivalents par stack** :
- Node : `node --test --concurrency=N` ou `vitest --pool=threads`
- Symfony : `phpunit --processes=auto` (PHPUnit 10+)
- Go : `go test -parallel N` (déjà parallèle par défaut au niveau package)

### Pattern 6 — `timeout-minutes` sur chaque job

Garde-fou contre les runaway jobs (boucle infinie, deadlock test, install hung).

```yaml
jobs:
  lint:
    timeout-minutes: 5
  typecheck:
    timeout-minutes: 5
  tests-unit:
    timeout-minutes: 10
  tests-integration:
    timeout-minutes: 10
  e2e:
    timeout-minutes: 20
```

**Effet** : pas d'optim directe. Mais évite la catastrophe d'un job qui boucle 6h et consomme 360 minutes-runner pour rien. Toujours définir un timeout réaliste — si le job dépasse, c'est un bug à fixer.

**Calibrage** : prendre la durée historique p95 et ajouter ~50 %. Si tes tests prennent normalement 8 min, mettre `timeout-minutes: 12`.

### Pattern 7 — `npm ci --no-audit --no-fund` (Node-specific)

Désactive les calls réseau inutiles à l'install npm.

```yaml
- run: npm ci --no-audit --no-fund
```

**Effet** : ~3-5s gagnées par run. Marginal mais cumulé sur 100 runs/semaine, c'est ~10 min/semaine. Et ça réduit la dépendance au registry npm (moins de risque de fail réseau).

**Équivalent pip** : pas vraiment — `pip install` n'a pas d'audit/fund. Mais `pip install --no-cache-dir` est l'**inverse** (à éviter — désactive le cache pip local). Laisser le défaut.

## Discipline post-modification d'un workflow

**HARD-GATE — interdit de fermer la session sans avoir validé un workflow modifié** :

```bash
# 1. Lister les runs récents
gh run list --branch <branch> --limit 10

# 2. Si en cours, attendre la fin (utiliser Bash run_in_background avec
#    until-loop, pas un poll-loop court < 5 min qui burn le cache prompt)
gh run view <RUN_ID> --json status,conclusion

# 3. Si failure : voir la cause racine et fixer avant fin de session
gh run view <RUN_ID> --log-failed | tail -60
```

**Origine** : 3 workflows ont tourné cassés en boucle ~3 jours sur Kiosque (Sprint 10 → 11) parce que des sessions ont introduit `secrets.X` non-définis et n'ont jamais validé le run post-push. Lesson `2026-05-02` documentée dans Kiosque.

## Conventions auth GCP (si stack `gcp`)

- **Workload Identity Federation (WIF)** — pas de Service Account JSON dans les secrets.
- **Valeurs hardcoded** dans les workflows (`workload_identity_provider`, `service_account`), pas via `secrets.X` (sauf si vraiment dynamique selon environnement). Évite le piège du secret vide qui passe silencieusement.
- **OIDC Cloud Run privé** : ne **pas** utiliser `gcloud auth print-identity-token --audiences=...` (échoue en mode WIF). Utiliser `auth@v2` en mode `id_token` directement.

Cf. `secrets-handling.md` pour la gestion détaillée. Le runbook Kiosque `ci-cd-workflow-protocol.md` (130+ lignes) couvre les cas spécifiques GCP/Telegram qui ne sont pas standardisables ici.

## Best-effort sur les alertes (Telegram, Slack, etc.)

Un step d'alerte qui échoue (rate limit, message refusé, chat_id invalide) ne doit **pas** faire crasher le job principal — le rôle de monitoring/audit a déjà été rempli quand on en arrive à l'alerte.

```yaml
- name: Alert Telegram on failure
  if: failure()
  continue-on-error: true   # garantit que le job principal n'échoue pas si l'alerte fail
  run: |
    response=$(curl -sS -o /tmp/resp.json -w "%{http_code}" \
      "https://api.telegram.org/bot${BOT_TOKEN}/sendMessage" \
      --data-urlencode "chat_id=${CHAT_ID}" \
      --data-urlencode "text=${MSG}" || echo "curl_error")
    if [ "$response" != "200" ]; then
      echo "::warning::Alert non envoyée (HTTP $response)"
      exit 0  # best-effort, pas de fail propagé
    fi
```

## Synthèse — checklist d'audit d'un nouveau workflow

Avant de merger un nouveau workflow, vérifier :

- [ ] `paths:` sur les triggers PR (Pattern 1)
- [ ] `concurrency` avec `cancel-in-progress` correct selon le type de workflow (Pattern 2)
- [ ] `cache: pip|npm` dans les `setup-*` + cache custom si outil le permet (Pattern 3)
- [ ] Jobs séparés pour lint / typecheck / tests si chacun > 1 min (Pattern 4)
- [ ] Tests parallélisés dans le job où c'est sûr (Pattern 5)
- [ ] `timeout-minutes` sur chaque job (Pattern 6)
- [ ] `--no-audit --no-fund` si npm (Pattern 7)
- [ ] `continue-on-error: true` sur les steps d'alerte
- [ ] WIF hardcoded si stack GCP, pas de SA JSON
- [ ] `gh run view` après le premier push (HARD-GATE)
