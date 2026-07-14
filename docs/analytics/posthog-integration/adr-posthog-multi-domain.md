# ADR — Intégration PostHog dans espace-adherent (multi-domaine)

**Date** : 2026-07-14
**Statut** : DESIGN (révocable, non-LOCKED)
**Ticket** : RE-5165
**Reviewer** : Dimitri (relecture humaine PR)
**Spec parente** : `docs/analytics/posthog-integration/spec.md`
**Plan** : `docs/analytics/posthog-integration/implementation-plan.md`

## Contexte

Intégration PostHog dans le backend Symfony `espace-adherent` servi en mode
white-label multi-domaine sur 4 hostnames :
- `utilisateur.parti-renaissance.fr`
- `utilisateur.attalpresident.fr`
- `utilisateur.avecgabrielattal.fr`
- `utilisateur.nouvellerepublique.fr`

Dual-run avec Matomo custom pendant 4 semaines, dual-signal permanent avec
les hits Postgres (pattern déjà en place). PostHog Cloud EU (data residency EU).

## Décisions structurantes

### 1. Détection dynamique du `site` — SiteDetector fail-open

Mapping explicite `Request::getHost()` → `site` enum (4 marques). Fallback
**fail-open** (return null + log WARNING) car espace-adherent partage le
kernel Symfony avec plusieurs hosts (`admin_renaissance_host`, `api_renaissance_host`,
`national_event_host`, etc.) — fail-closed causerait 500 sur toutes les routes
hors périmètre PostHog (admin, api, webhooks, health).

### 2. Cookies consent scopés root-domain (4 cookies indépendants)

| Marque | Cookie | Scope |
|---|---|---|
| parti-renaissance | `pr_consent` | `.parti-renaissance.fr` |
| attalpresident | `ap_consent` | `.attalpresident.fr` (déjà en prod, migration idempotente) |
| avecgabrielattal | `aga_consent` | `.avecgabrielattal.fr` |
| nouvellerepublique | `nr_consent` | `.nouvellerepublique.fr` |

Gouvernance indépendante par marque (frontière CNIL). Bannière conditionnée
sur `cookie==null && !isAuth`.

### 3. Reverse proxy `/ingest/*` Symfony Controller (Option A)

`IngestProxyController` forward vers `eu.i.posthog.com` avec whitelist paths
v1.180+ (`e|decide|s|static|batch|array|flags|surveys|warehouse`), rate limit
600/min composite (IP), sanitize headers (drop Set-Cookie, garde Content-Encoding),
timeout 5s → 504 défensif. Contourne Safari ITP / Firefox ETP (first-party cookies).

### 4. Hash identity marque-specific (divergence intentionnelle doctrine cross-sites §4.2)

`distinct_id = SHA256("${SALT_MARQUE}:${email_norm}")` avec salt marque-specific :
- `attalpresident-2027`, `parti-renaissance-2027`, `avecgabrielattal-2027`, `nouvellerepublique-2027`

Format `SALT:email` avec `:` séparateur, salt devant, email normalisé
(`trim + strtolower`). **Byte-identique** au vrai code prod
`attalpresident.fr/lib/posthog/identity.ts` L22-38 (validé Python).

**Divergence vs doctrine cross-sites §4.2** (postulait salt global `renaissance-2027`
+ format `email + salt` sans séparateur) : les PRs migration #308/309/310 côté
attalpresident.fr **n'ont pas été mergées en prod**. Décision assumée :
s'aligner sur la réalité prod. Cross-brand se fait via `public_id` en `$set` +
view BQ `posthog_identity_bridge` (mart Renaissance, crm-integrations PR #175),
pas via `distinct_id` identique cross-domain.

### 5. Doctrine email 2 cas — mapping par cluster

| Cluster | Cas | Justification |
|---|---|---|
| Connexion | 1 | User a un compte |
| Adhésion | 1 | Compte créé au moment de l'adhésion |
| Don | 1 forcé | `donor_type: user|anonymous` en propriété — JAMAIS `$set.email` |
| Meeting national | 1 forcé | Jointure DWH server-only via inscription.email |
| Profil | 1 | Zone connectée |
| Newsletter | 2 | `$set.email` server-side autorisé (whitelist grep CI) |
| Pétition | 2 | `$set.email` server-side autorisé (whitelist grep CI) |

### 6. DPO validé 2026-07-14

Opt-in implicite au 1er login (couvert par CGU explicite) validé DPO Renaissance
(référence archive interne). Pattern cohérent avec Vox mobile PR #1825.

### 7. Feature flag `POSTHOG_ENABLED=false` par défaut

Toggle staging puis prod après validation manuelle 3 étapes (spec §22 activation).

## Alternatives écartées

- **`site` marque enum élargi (5 valeurs)** : rejeté — chaque marque garde sa
  gouvernance CNIL indépendante.
- **Regex host string en `->host('%pattern%')`** : rejeté — Symfony n'interprète
  pas les params host comme regex, il faut route param `{marque}` + requirements.
- **Fail-closed SiteDetector (throw)** : rejeté — casserait admin/api/webhooks.
- **Salt global `renaissance-2027`** : rejeté — divergence avec prod attalpresident.fr,
  cross-brand via mart BQ suffit.

## Réf. amont

- Spec + plan : `docs/analytics/posthog-integration/`
- crm-integrations : PRs #175 (view BQ), #176 (specs + plan)
- attalpresident.fr : PRs #308/#309/#310 (Phase 0 non mergées)
- Vox mobile : espace-militant PR #1825 (pattern DPO validation identique)
