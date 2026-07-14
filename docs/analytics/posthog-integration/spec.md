# Design — intégration PostHog dans `espace-adherent` (Symfony multi-domaine parti-renaissance / attalpresident / avecgabrielattal / nouvellerepublique)

**Date** : 2026-07-14
**Auteur** : Victor Cohen (via agent Claude Opus 4.7 harness-solo)
**Repo cible** : `parti-renaissance/espace-adherent` (site principal Renaissance, Symfony 7.4 + PHP 8.5 + Twig + Webpack + Alpine.js + Tailwind + Sentry)
**Repos impactés parallèles** : `parti-renaissance/crm-integrations` (doctrine + spec cross-sites update pour multi-domain §3.2), spec Phase 2 rollout (voir §17).
**Reviewers humains attendus** : dev backend Symfony (reprise), dev front-end (assets JS + Twig), Emilien Vandevelde (relecture finale)
**Statut** : DESIGN (révocable, non-LOCKED)
**Ticket parent** : RE-5165 (rollout PostHog Renaissance — parent shared avec espace-militant PR #1825)

**Doctrine parente** :
- Spec cross-sites : `docs/superpowers/specs/2026-07-01-posthog-cross-sites-design.md` (§3-§8 web, §11 sécurité, §5.2 cookie consent multi-marque)
- Nomenclature globale : `docs/posthog-nomenclature-globale.md` (§2 enum `site`, §3 doctrine email 2 cas, §4 catalogue events communs)
- ADR-006 : rétention illimitée mart `Custom_PostHog` (LOCKED)
- ADR-011 : activation PostHog `attalpresident.fr` (base pour extension multi-domain)
- ADR-013 : salt global `renaissance-2027` (doctrine cross-sites §4.2) — **divergence explicite** espace-adherent : salts marque-specific `SALT:email` cf. §2.4 note.

---

## 1. Contexte et motivation

Le repo `espace-adherent` est le **backend Symfony historique** qui sert **plusieurs marques Renaissance** en mode **white-label multi-domaine** : le même code applicatif est servi sur 4 hostnames distincts, la détection de la marque se fait à runtime via `Request::getHost()` et injecte le contexte (thème, marque, cookies) approprié.

Domaines actifs (host mapping constaté 2026-07-14 sur `%user_vox_host%` config Symfony) :

| Hostname | Marque | Root domain (cookie scope) |
|---|---|---|
| `utilisateur.parti-renaissance.fr` | parti-renaissance | `.parti-renaissance.fr` |
| `utilisateur.attalpresident.fr` | attalpresident | `.attalpresident.fr` |
| `utilisateur.avecgabrielattal.fr` | avecgabrielattal | `.avecgabrielattal.fr` |
| `utilisateur.nouvellerepublique.fr` | nouvellerepublique | `.nouvellerepublique.fr` |

Configuration Symfony actuelle (`.env` `USER_VOX_HOST`) définit **1 valeur unique** — pour matcher 4 marques via un seul backend, la migration doit passer la variable en pattern regex compatible Symfony route `host: ...` requirements. Deux approches équivalentes :

- (A) `USER_VOX_HOST_REGEX = utilisateur\.(parti-renaissance|attalpresident|avecgabrielattal|nouvellerepublique)\.fr` référencé dans les 16 routes `#[Route(host: '%user_vox_host_regex%', requirements: ['_host_pattern' => ...])]`
- (B) Une constante `HostPatternProvider` service Symfony qui expose le regex + s'injecte via attribut personnalisé dans les routes.

**Reco (A)** : moins de refactor, transparent pour les 16 routes existantes. Migration prévue en Task Phase 1 (commit dédié `chore(routing): migrate USER_VOX_HOST vers regex multi-marque`).

Les 7 parcours utilisateurs suivants sont **actifs sur les 4 hostnames** avec le même code Twig/Controller :

1. **Connexion** (`/connexion`, magic link, mot de passe reset, logout)
2. **Adhésion** (`/adhesion` + variantes referrer, paiement, confirmation)
3. **Don** (`/don`, `/grands-donateurs`, paiement, `/don/merci`)
4. **Meeting national** (`/grand-rassemblement/{slug}`, inscription, edit, confirm, paiement)
5. **Profil adhérent connecté** (`/parametres/mon-compte`, mes événements, consultations)
6. **Newsletter** (POST `/api/newsletter`, confirm token)
7. **Pétition** (validate token)

Cible : instrumenter PostHog sur les 4 marques simultanément, avec **détection dynamique du `site`**, **cookie consent scopé root-domain par marque**, **reverse proxy `/ingest/*` par hostname** pour éviter les blocages Safari ITP / Firefox ETP, **respect de la doctrine cross-sites §4** avec divergence documentée sur le hash email — `distinct_id = SHA256("${SALT_SITE}:${email}")` **marque-specific** (cf. §2.4 et §7.1), ré-jonction cross-brand via `public_id` en `$set` — et **respect de la doctrine email 2 cas** (Cas 1 pour tout ce qui requiert un compte, Cas 2 pour newsletter et pétition).

Cette spec est le **livrable d'entrée pour le dev backend et le dev front-end** qui reprendront la PR, l'amélioreront si besoin, et feront merger. Elle sert aussi de **document de référence pour Emilien Vandevelde** (relecture finale) et de **brief DPO** (validation cookie consent multi-marque déjà OK selon confirmation user 2026-07-14).

## 2. Décisions cadrées

### 2.1 Détection dynamique du `site` (nouvelle mécanique)

**Décision** : un service Symfony `SiteDetector` mappe `Request::getHost()` → `site` (enum nomenclature §2). Un `SiteContext` service holds la valeur pour la durée de la requête. Un `SiteContextTwigExtension` expose `posthog_site`, `posthog_consent_cookie_name`, `posthog_consent_cookie_domain` en variables Twig globales.

Mapping :

```php
private const HOSTNAME_SITE_MAP = [
    'utilisateur.parti-renaissance.fr'  => 'parti-renaissance',
    'utilisateur.attalpresident.fr'     => 'attalpresident',
    'utilisateur.avecgabrielattal.fr'   => 'avecgabrielattal',
    'utilisateur.nouvellerepublique.fr' => 'nouvellerepublique',
];
```

**Fail-closed sur hostname non-mappé** : plus de fallback silent `parti-renaissance`. Un hostname absent du mapping (nouvelle preview Cloud Run non prévue, `admin.attalpresident.fr`, `dev.parti-renaissance.fr`, etc.) log `CRITICAL` Sentry + **throw** — empêche le data leakage silent (event routé vers la mauvaise marque, cookie consent posé sur le mauvais scope).

```php
public function detectFromRequest(Request $request): string
{
    $host = strtolower($request->getHost());
    if (isset(self::HOSTNAME_SITE_MAP[$host])) {
        return self::HOSTNAME_SITE_MAP[$host];
    }
    // Fail-closed : log CRITICAL + throw. Empêche le data leakage silent
    // si un nouveau hostname arrive sans mapping explicite (ex: preview
    // Cloud Run non prévu, admin.attalpresident.fr, etc.).
    $this->logger->critical(
        'PostHog SiteDetector: unmapped hostname — request refused',
        ['hostname' => $host],
    );
    throw new \RuntimeException("Hostname non autorisé pour PostHog: $host");
}
```

**Alternative safer** : retourner `null` que le caller doit gérer explicitement (skip PostHog init au lieu de crash 500). Utile en dev/preview où on tolère l'absence de tracking. À trancher dans la PR d'implémentation.

**Alternative écartée** : regex sur suffixe (`.attalpresident.fr` → `attalpresident`). Rejetée car moins explicite, silently accepte des sous-domaines non prévus (`admin.attalpresident.fr`, `dev.attalpresident.fr`), et le mapping explicite est plus safe pour l'audit sécurité.

### 2.2 Cookie consent scopé root-domain par marque

**Décision** : un cookie consent par marque, scopé au root domain correspondant (partagé cross-sous-domaines de la même marque). Nommage cohérent avec la spec cross-sites §5.2 (pattern `ap_consent` déjà en prod attalpresident.fr).

| Marque | Nom cookie | Scope `domain=` | Path | Max-Age | Statut |
|---|---|---|---|---|---|
| parti-renaissance | `pr_consent` | `.parti-renaissance.fr` | `/` | 13 mois (CNIL max) | Nouveau |
| attalpresident | `ap_consent` | `.attalpresident.fr` | `/` | 13 mois | **Déjà en prod** (PRs #308/#309/#310) — migration idempotente obligatoire |
| avecgabrielattal | `aga_consent` | `.avecgabrielattal.fr` | `/` | 13 mois | Nouveau |
| nouvellerepublique | `nr_consent` | `.nouvellerepublique.fr` | `/` | 13 mois | Nouveau |

Valeurs du cookie (payload compact URL-safe) :

- `1` — consent `granted` (opt-in)
- `0` — consent `refused` (opt-out)
- **absent** — état `undefined`, bannière à afficher

**Migration idempotente `ap_consent`** (bloquant, ne pas oublier) : si le cookie `ap_consent` existe déjà (valeur `0` ou `1`), le code Symfony/JS **doit** lire son état actuel et l'appliquer sans le réécrire. Aucune régression sur les décisions déjà prises par les users sur `www.attalpresident.fr` ou `app.attalpresident.fr` (spec cross-sites §5.2 + Phase 0 handoff PRs #309/#310 mergées côté attalpresident).

**Gouvernance indépendante par marque** : un user qui a refusé sur `.attalpresident.fr` peut avoir accepté sur `.parti-renaissance.fr`, et vice-versa. Chaque marque = frontière juridique CNIL séparée (l'exemption mesure d'audience anonyme est appréciée par site).

**Attributes cookies** :

- `Secure` (HTTPS obligatoire, aucune exception).
- `HttpOnly=false` : le cookie doit être lisible par le JS pour piloter `posthog.opt_in_capturing()` / `opt_out_capturing()`.
- `SameSite=Lax` : permet la navigation depuis un lien externe (email de campagne → landing marque) sans casser le consent.

### 2.3 Reverse proxy PostHog `/ingest/*` par hostname (Option A tranchée)

**Décision** : Symfony Controller dédié qui forward `/ingest/{path:.*}` vers `eu.i.posthog.com/{path}`. Chaque hostname (utilisateur.marque.fr) porte son propre proxy, avec `Host` header réécrit pour PostHog. Configuration côté SDK JS : `api_host: '/ingest'` (relatif, pas d'absolu), le PostHog SDK routera automatiquement vers `https://utilisateur.parti-renaissance.fr/ingest/` (ou l'équivalent selon marque).

Bénéfices :

- **Contourne les blocages Safari ITP / Firefox ETP** : PostHog est appelé sur le domain de la marque (first-party), pas sur `eu.i.posthog.com` (third-party).
- **Aucune configuration DNS** ou CNAME cross-domain requise.
- **Cookies PostHog first-party** — le SDK peut poser `ph_*` cookies sur le domain de la marque.

Implémentation :

```php
#[Route(
    '/ingest/{path}',
    name: 'app_posthog_ingest_proxy',
    requirements: ['path' => '.+'],
    methods: ['GET', 'POST', 'OPTIONS'],
)]
public function proxy(string $path, Request $request): Response
{
    $target = sprintf('https://eu.i.posthog.com/%s', $path);
    $response = $this->httpClient->request(
        $request->getMethod(),
        $target,
        [
            'query'   => $request->query->all(),
            'body'    => $request->getContent(),
            'headers' => $this->forwardHeaders($request), // filtre X-Forwarded-*, User-Agent
            'timeout' => 5.0,
        ],
    );
    return new Response(
        $response->getContent(false),
        $response->getStatusCode(),
        $response->getHeaders(false),
    );
}
```

**Sécurité** : path whitelist strict aux endpoints PostHog publics (`e/`, `decide/`, `s/`, `static/`, `batch/`, `array/`). Tout autre path retourne 404. Interdit `capture/` (server-side write reservé) et `api/` (admin).

**Rate-limit / abuse** : appliquer le rate-limit standard Symfony (`framework.rate_limiter`) au taux de PostHog EU (~10 req/s par IP). Cas de spam bots : loggé Sentry, filtré par UA `is_bot`.

**Timeout défensif** : 5 secondes. Si PostHog EU répond lent, on tombe en 504 et le SDK JS retry. Pas de risque bloquant sur le rendu Twig.

**Cache HTTP** : aucun cache sur les endpoints d'ingest (POST données). Les endpoints `static/` de PostHog (bundle SDK) sont déjà cachés par leur CDN — proxy passthrough.

### 2.4 Doctrine identity (rappel spec cross-sites §4)

Doctrine web appliquée avec **divergence documentée** sur le format hash (cf. bloc dédié infra) :

- **Boot anonyme** : SDK auto-génère `distinct_id` anonyme, persisté cookie `ph_*` first-party (grâce au proxy §2.3).
- **Post-login** : `posthog.identify(hash_email, { $set: {...}, $set_once: {...} })` où `hash_email = SHA256("${SALT}:${email_norm}")` avec `SALT` **marque-specific** (`attalpresident-2027`, `parti-renaissance-2027`, `avecgabrielattal-2027`, `nouvellerepublique-2027`), calculé côté server (via `EventSubscriber` post-login, cf. §8.1) et injecté dans un `<script>` inline server-rendered → transmis au JS PostHog pour l'`identify()`.
- **Cross-brand via `public_id`** : le `distinct_id` diffère cross-domain (salt marque-specific), donc **person PostHog distincte par marque**. La ré-jonction cross-brand se fait via `public_id` en `$set` + view BQ `posthog_identity_bridge` (mergée PR #175), pas via distinct_id identique.
- **`public_id`** en `$set` + `register_once` — pivot cross-brand mart BQ. Le champ existe déjà côté `Adherent` entity (`PublicIdTrait`, 7 chars, exposé via `getPublicId()`) — aucune migration back-end requise.

Le calcul du hash côté server présente 2 avantages vs client-side hashing :

1. **L'email en clair n'est jamais envoyé au navigateur** dans un contexte tracking — le server envoie déjà le hash prêt à consommer.
2. **Cohérence cross-plateforme** avec la réalité prod attalpresident.fr (`lib/posthog/identity.ts`) et éventuellement future adhésion mobile.

> **Divergence vs doctrine cross-sites §4.2** : la doctrine postulait `distinct_id = SHA256(email + "renaissance-2027")` (salt global unique). La réalité prod attalpresident.fr est `SHA256("${SALT}:${email}")` avec `SALT` marque-specific (`attalpresident-2027`, etc.). Décision espace-adherent : **s'aligner sur la réalité prod** (salts marque-specific, format `SALT:email` avec `:` séparateur). Cross-brand se fait via `public_id` en `$set` + view BQ `posthog_identity_bridge` (mergée PR #175), pas via `distinct_id` identique cross-domain. Follow-up : update spec cross-sites §4.2 pour aligner la doctrine sur la réalité (mini-PR crm-integrations séparée).

### 2.5 Doctrine email 2 cas (rappel §4.3 + `docs/posthog-nomenclature-globale.md` §3)

Chaque cluster de parcours est mappé à un cas :

| Cluster | Cas | Justification |
|---|---|---|
| Connexion | 1 | L'utilisateur a un compte |
| Adhésion | 1 | Compte créé au moment de l'adhésion |
| Don | 1 forcé (décision §8.3) | Dons anonymes non trackés avec `$set.email` — email jamais envoyé PostHog |
| Meeting national | 1 forcé (décision §8.4) | Email server-only, jointure DWH via `national_event_inscriptions.email` |
| Profil | 1 | Zone connectée par définition |
| Newsletter | 2 | Form sans compte, `$set.email` server-side autorisé |
| Pétition | 2 | Form sans compte, `$set.email` server-side autorisé |

Grep CI bloquant (spec §5 de la nomenclature globale) : aucun `posthog.capture(...email...)` ou `posthog.identify(...@...)` côté client JS. Le grep tolère `$set.email` dans les templates Twig **uniquement** pour les 2 events autorisés (`newsletter_submitted_server`, `petition_signed_server`) — la liste blanche est vérifiée par le workflow CI copié de `templates/workflows/lint-posthog-privacy.yml` de crm-integrations, adaptée aux extensions PHP/Twig/JS.

### 2.6 Consent doctrine (rappel spec cross-sites §5, DPO validé 2026-07-14)

**DPO Renaissance a validé le pattern opt-in implicite via CGU 2026-07-14 pour l'ensemble des surfaces PostHog Renaissance (mobile + web)** — couvre les 4 marques espace-adherent, la bannière consent, le bridge auth ↔ consent, et l'exemption mesure d'audience anonyme par site.

- **Boot anonyme (déconnecté)** : bannière `PostHogConsentBanner` non-dismissable, 3 boutons Accepter / Refuser / Gérer. En attente de décision, PostHog est en `opt_out_capturing()` (aucun event envoyé).
- **Post-login** : bridge auth ↔ consent implicite (couvert par CGU, validé DPO 2026-07-14 pour l'ensemble des surfaces Renaissance web + Vox mobile — même pattern à réutiliser). Si `cookie=undefined` et `isAuth=true`, on set le cookie à `1` (granted implicit) automatiquement + `posthog.opt_in_capturing()`.
- **Refused persistent** : un user qui a refusé (cookie=`0`) reste opt-out même après login (RGPD Art. 21 droit de retrait respecté). Le bridge auth ne peut pas écraser `refused`.
- **Section Réglages > Confidentialité** dans `/parametres/mon-compte` : toggle "Analyse d'usage anonymisée" pour re-basculer granted ↔ refused à tout moment.

### 2.7 App Environnement + release version

Injection des super-properties côté server (Twig var) :

- `environment` : depuis `%env(APP_ENVIRONMENT)%` ou `%kernel.environment%` — valeurs `production` / `staging` / `preview` / `dev`.
- `deploy_sha` : depuis `%env(DEPLOY_SHA)%` ou fallback `%env(default::GITHUB_SHA)%` — 7 premiers chars.
- `deploy_version` : depuis `%env(APP_VERSION)%` — tag semver ou `null`.

Ces variables sont déjà partiellement en place pour Sentry (`sentry.php` : `release`, `environment`). Réutiliser les mêmes DI parameters + Twig extension.

### 2.8 Pas d'ATT ni bannière iOS (web uniquement)

espace-adherent est **web only** — aucun problème Apple Review, aucun besoin de `expo-tracking-transparency`. Le web utilise cookies + bannière classique.

## 3. Architecture des modules

### 3.1 Arborescence côté `espace-adherent` (Symfony)

```
src/Analytics/PostHog/
├── SiteDetector.php               # mapping hostname → site enum
├── SiteContext.php                # holds site + cookie_name + cookie_domain, injecté par listener
├── SiteContextListener.php        # EventListener kernel.request, priority=250
├── PostHogService.php             # compute hash_email, register super-properties JSON, cache par request
├── HashEmailService.php           # SHA256("${SALT_SITE}:${email_norm}") avec SALT marque-specific (cf. §7.1), unit-testable, injectable
├── ConsentCookieHelper.php        # read/write cookie consent scopé domaine
├── IngestProxyController.php      # reverse proxy /ingest/{path} → eu.i.posthog.com
├── Twig/
│   └── PostHogTwigExtension.php   # {{ posthog_site }}, {{ posthog_consent_cookie_name }}, posthog_snippet(), posthog_super_properties(), posthog_identify()
├── Events/
│   ├── PostHogEventName.php       # enum PHP 8.1+ des 30 events custom (Renaissance web)
│   └── PostHogPayload.php         # value-objects par event, sérialisation JSON typée
├── EventSubscriber/
│   └── AuthEventSubscriber.php    # LoginSuccessEvent, LoginFailureEvent, LogoutEvent → login_succeeded / login_failed / logout_completed
└── Controller/
    └── ConsentSettingsController.php  # POST /parametres/confidentialite (toggle granted↔refused)

assets/analytics/posthog/
├── posthog-init.js                # entry point : boot SDK, config, consent state
├── posthog-capture.js             # wrapper capture() typé (mirror PostHogEventName enum PHP)
├── posthog-consent.js             # read/write cookie consent, bridge banner ↔ SDK
├── posthog-consent-banner.js      # Alpine.js component monté depuis Twig macro
├── posthog-identify.js            # applique payload identify server-generated (hash_email + super-properties)
└── posthog-web-vitals.js          # tracking auto pageviews + web vitals (optional Phase 1)

templates/analytics/posthog/
├── _snippet.html.twig             # <script> inline server-rendered init (posthog_site, super_props, identify_payload)
├── _consent_banner.html.twig      # macro consent banner (Alpine.js x-data) — inséré dans base_renaissance
└── _consent_settings_row.html.twig  # toggle Réglages > Confidentialité

templates/renaissance/parametres/
└── confidentialite.html.twig      # nouvel écran Réglages > Confidentialité (contient le toggle + explication CNIL/RGPD)

config/routes/analytics.yaml       # routes ingest proxy + confidentialite settings
config/packages/prod/posthog.yaml  # env vars POSTHOG_API_KEY, POSTHOG_HOST, POSTHOG_ENABLED (default false), salts marque-specific hardcodés dans HashEmailService (§7.1)

docs/adrs/
└── posthog-multi-domain.md        # ADR local (décisions structurantes multi-domain)

docs/analytics/
└── posthog-events-parti-renaissance.md  # taxonomie MVP events web + mapping hits + owners PO

.github/workflows/
└── lint-posthog-privacy.yml       # grep bloquant email clair côté client (PHP + Twig + JS)

tests/Analytics/PostHog/
├── SiteDetectorTest.php           # unit test mapping hostname → site
├── HashEmailServiceTest.php       # snapshot SHA256(test@example.com + salt)
├── ConsentCookieHelperTest.php    # scopes + migration idempotente ap_consent
├── IngestProxyControllerTest.php  # whitelist paths + forward correct + status codes
└── PostHogServiceTest.php         # super-properties builder, cache par request
```

### 3.2 Points d'insertion dans les templates Twig existants

| Fichier existant | Modification |
|---|---|
| `templates/base_renaissance.html.twig` (bloc `{% block analytics %}` L38-60) | Ajouter avant Matomo : `{{ include('analytics/posthog/_snippet.html.twig') }}` (server-rendered init + identify si `app.user`). Matomo reste inchangé pendant dual-run 4 semaines (cf. §3.3). |
| `templates/base_renaissance.html.twig` (fin `<body>`) | Ajouter `{{ include('analytics/posthog/_consent_banner.html.twig') }}` conditionnel (bannière si cookie=undefined). |
| `templates/renaissance/parametres/_navigation.html.twig` (menu profil) | Ajouter entrée "Confidentialité" pointant vers `/parametres/confidentialite`. |
| `assets/bootstrap.js` L18-30 (init Sentry) | Ajouter `import { initPostHog } from './analytics/posthog/posthog-init'; initPostHog();` **après** Sentry init. |
| `webpack.common.js` | Ajouter entry `analytics/posthog/posthog-init.js` compilé dans le bundle `bootstrap` (pas de chunk séparé pour éviter round-trips). |
| `.env` + `config/services.php` | Ajouter `USER_VOX_HOST_REGEX` (pattern regex Symfony host matching cf. §1). Migration des 16 routes existantes du repo pour utiliser ce pattern au lieu de `USER_VOX_HOST` unique (commit dédié en Task 0 de la séquence). |

### 3.3 Dual-run Matomo (rappel spec cross-sites §10.7 — appliqué à web aussi)

Matomo custom existant dans `base_renaissance.html.twig` L39-60 laissé **inchangé** pendant 4 semaines de dual-run. PR ultérieure S+4 supprimera Matomo si les critères GO ci-dessous sont validés :

- PostHog voit ≥ le trafic Matomo sur `$pageview` (native SDK) + `login_succeeded` + `donation_completed` + `adhesion_completed`.
- 0 erreur Sentry lié `posthog-js` sur 7 jours consécutifs.
- `posthog.identify()` observé en prod sur ≥ 80 % des sessions authentifiées (KPI bridge auth ↔ consent).

## 4. Detection dynamique du `site`

### 4.1 SiteDetector

```php
<?php declare(strict_types=1);

namespace App\Analytics\PostHog;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

final class SiteDetector
{
    private const HOSTNAME_SITE_MAP = [
        'utilisateur.parti-renaissance.fr'  => 'parti-renaissance',
        'utilisateur.attalpresident.fr'     => 'attalpresident',
        'utilisateur.avecgabrielattal.fr'   => 'avecgabrielattal',
        'utilisateur.nouvellerepublique.fr' => 'nouvellerepublique',
    ];

    public function __construct(private readonly LoggerInterface $logger) {}

    public function detectFromRequest(Request $request): string
    {
        $host = strtolower($request->getHost());
        if (isset(self::HOSTNAME_SITE_MAP[$host])) {
            return self::HOSTNAME_SITE_MAP[$host];
        }
        // Fail-closed (cf. §2.1) : log CRITICAL + throw. Aucun fallback silent.
        $this->logger->critical(
            'PostHog SiteDetector: unmapped hostname — request refused',
            ['hostname' => $host],
        );
        throw new \RuntimeException("Hostname non autorisé pour PostHog: $host");
    }

    public static function getCookieConfig(string $site): array
    {
        return match ($site) {
            'attalpresident'      => ['name' => 'ap_consent',  'domain' => '.attalpresident.fr'],
            'parti-renaissance'   => ['name' => 'pr_consent',  'domain' => '.parti-renaissance.fr'],
            'avecgabrielattal'    => ['name' => 'aga_consent', 'domain' => '.avecgabrielattal.fr'],
            'nouvellerepublique'  => ['name' => 'nr_consent',  'domain' => '.nouvellerepublique.fr'],
            default               => throw new \InvalidArgumentException("Unknown site: $site"),
        };
    }
}
```

### 4.2 SiteContext + Listener

`SiteContext` est un service à scope `service` (partagé) mais réinitialisé à chaque requête via un `EventListener` sur `kernel.request` priorité 250 (juste après le firewall Symfony, avant les controllers).

```php
final class SiteContext
{
    private ?string $site = null;

    public function setSite(string $site): void { $this->site = $site; }
    public function getSite(): string
    {
        if ($this->site === null) {
            // Cohérent avec SiteDetector fail-closed (§2.1) — pas de fallback silent.
            throw new \RuntimeException('SiteContext non initialisé (SiteContextListener non déclenché ?)');
        }
        return $this->site;
    }
    public function getCookieConfig(): array { return SiteDetector::getCookieConfig($this->getSite()); }
}
```

```php
final class SiteContextListener
{
    public function __construct(
        private readonly SiteDetector $detector,
        private readonly SiteContext $context,
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) return;
        $this->context->setSite($this->detector->detectFromRequest($event->getRequest()));
    }
}
```

### 4.3 PostHogTwigExtension

Expose 3 variables + 3 fonctions Twig :

- `posthog_site` — string (super-property `site`)
- `posthog_consent_cookie_name` — string (nom du cookie de la marque)
- `posthog_consent_cookie_domain` — string (root domain du cookie)
- `posthog_super_properties()` — retourne l'array super-properties auto à sérialiser en JSON (`site`, `platform`, `environment`, `deploy_sha`, `deploy_version`, `locale`, `is_bot`)
- `posthog_identify_payload(user)` — retourne `{hash_email, public_id, identified_from_site, identified_at}` si `user != null`, sinon `null`
- `posthog_snippet()` — retourne le HTML complet du bloc `<script>` init (utilisé dans `_snippet.html.twig`)

## 5. Cookie consent multi-marque

### 5.1 État machine (identique doctrine cross-sites §5.2)

Persisté dans le cookie `{ap|pr|aga|nr}_consent` :

- **`undefined`** (cookie absent) → bannière à afficher, PostHog `opt_out_capturing()`
- **`1`** (granted) → PostHog `opt_in_capturing()`
- **`0`** (refused) → PostHog `opt_out_capturing()` permanent (survit logout, RGPD Art. 21)

État `implicit` (opt-in via login couvert par CGU) : représenté par `1` avec une propriété PostHog `consent_source: "implicit_login"` capturée dans l'event `consent_granted`. Pas de valeur cookie distincte — simplifie le pattern (cookie boolean seul).

### 5.2 ConsentCookieHelper

```php
final class ConsentCookieHelper
{
    public function __construct(private readonly SiteContext $context) {}

    public function read(Request $request): ?bool
    {
        $config = $this->context->getCookieConfig();
        $raw = $request->cookies->get($config['name']);
        return match ($raw) { '1' => true, '0' => false, default => null };
    }

    public function write(bool $granted): Cookie
    {
        $config = $this->context->getCookieConfig();
        return Cookie::create(
            name: $config['name'],
            value: $granted ? '1' : '0',
            expire: strtotime('+13 months'),
            path: '/',
            domain: $config['domain'],
            secure: true,
            httpOnly: false,  // JS doit lire pour piloter PostHog SDK
            sameSite: 'lax',
        );
    }
}
```

### 5.3 Migration idempotente `ap_consent`

Le cookie `ap_consent` existe **déjà en prod** sur `.attalpresident.fr` (posé par les PRs handoff #308/#309/#310 côté attalpresident.fr). Le code espace-adherent servi sur `utilisateur.attalpresident.fr` **doit** :

1. **Lire le cookie existant sans le réécrire** au boot.
2. **Appliquer la valeur lue** au SDK PostHog (opt-in ou opt-out).
3. **Ne créer/réécrire le cookie QUE** si l'user prend une nouvelle décision explicite (clique Accepter/Refuser dans la bannière ou toggle Réglages) — dans ce cas, le nouveau cookie a le même nom + même domain + même path que l'existant, donc écrase de manière transparente.

Test d'intégration obligatoire dans la PR : Behat scenario "Given the user visits `utilisateur.attalpresident.fr` with existing `ap_consent=0`, When the page loads, Then PostHog is opt_out, Then the cookie value is unchanged, Then no consent banner is shown".

**Cas cross-marque non-couverts par le pattern** (à documenter explicitement pour Fontaine + audit user) :

1. **Magic link email cross-marque** : email envoyé sur `campagne-attal.com/inscription?token=X` → landing `utilisateur.attalpresident.fr` (`ap_consent` déjà posé) → OAuth callback redirect vers `utilisateur.parti-renaissance.fr` → `pr_consent` absent → bannière re-affichée. Le user PENSE avoir donné consent une fois pour Renaissance ; en réalité il doit re-cliquer sur chaque root domain marque. **Design intentionnel** (frontière juridique par marque) — communiqué DPO validé 2026-07-14.

2. **`SameSite=Lax` + POST cross-origin** : Chrome depuis 2020 casse les cookies sur les POST cross-origin en mode Lax. Cas Renaissance : callback Paybox `POST /paiement/callback` = "top-level navigation" acceptée par Lax. Test Behat obligatoire : "Given un user avec `ap_consent=1`, When il complète un paiement Paybox via POST 302 cross-origin, Then `ap_consent` reste lisible à l'arrivée".

### 5.4 Bannière consent (Twig macro + Alpine.js)

Bannière montée dans `base_renaissance.html.twig` en fin de `<body>` via `{% include %}` conditionnel côté server (`if consent_cookie_state == null`) — évite le flash "banner puis disparaît" à l'hydratation JS. 3 boutons Accepter / Refuser / Gérer, Alpine.js pilote le POST vers un endpoint (`POST /parametres/confidentialite`) qui set le cookie server-side + capture `consent_granted` / `consent_refused` server-side.

Wireframe (déjà éprouvé sur Vox mobile) :

```
┌─────────────────────────────────────────────────────────┐
│ Analyse d'usage anonymisée                              │
│                                                          │
│ Nous mesurons l'usage du site pour l'améliorer. Aucune  │
│ donnée personnelle sensible n'est collectée. Vous       │
│ pouvez changer d'avis à tout moment dans                │
│ Réglages > Confidentialité.                             │
│                                                          │
│ [ Accepter ]   [ Refuser ]   [ Gérer >>                 │
│                              (Réglages) ]               │
└─────────────────────────────────────────────────────────┘
```

Sur clic "Gérer", redirection vers `/parametres/confidentialite` (si connecté) OU vers `/parametres/confidentialite-public` (route publique équivalente pour anonymes).

### 5.5 Bridge auth ↔ consent implicit

Server-side dans `AuthEventSubscriber::onLoginSuccess()` (cf. §8.1), post-authentification firewall :

```php
$state = $this->consentHelper->read($request);
if ($state === null) {
    // 1er login, aucune décision explicite → implicit granted
    $response->headers->setCookie($this->consentHelper->write(true));
    $this->postHogService->captureServerSide('consent_granted', [
        'source' => 'implicit_login',
        'consent_version' => '1',
    ]);
}
// Si $state === true ou false : rien à faire (respecte l'état explicit)
```

Client-side, la lecture du cookie **doit** matcher le state server pour la 1re requête (sinon flash SDK opt_out → opt_in). Le snippet `_snippet.html.twig` injecte le state initial dans une variable JS :

```html
<script>
  window.__POSTHOG_CONSENT__ = {{ posthog_consent_state|json_encode|raw }};
</script>
```

## 6. Reverse proxy `/ingest/*`

### 6.1 Configuration + whitelist

```yaml
# config/packages/prod/posthog.yaml
posthog:
  api_key: '%env(POSTHOG_API_KEY)%'
  api_host: 'https://eu.i.posthog.com'
  # Salts marque-specific hardcodés dans HashEmailService (cf. §7.1), pas d'env var globale
  enabled: '%env(bool:default::POSTHOG_ENABLED)%'  # default false, toggle explicite pour activation

# config/routes/analytics.yaml
app_posthog_ingest_proxy:
  path: /ingest/{path}
  controller: App\Analytics\PostHog\IngestProxyController
  requirements:
    path: '^(e|decide|s|static|batch|array|flags|surveys|warehouse)(/.*)?$'
  methods: [GET, POST, OPTIONS]
```

Path whitelist (regex) : `e`, `decide`, `s`, `static`, `batch`, `array`, `flags`, `surveys`, `warehouse`. Tout autre path → 404 automatique par Symfony. Note : `flags` et `surveys` (feature flags v2 + in-app surveys), `warehouse` (Data Warehouse queries) — routes récentes SDK PostHog v1.150+. Sans, feature flags cassés silencieusement.

### 6.2 IngestProxyController (extrait)

Utilise `Symfony\Contracts\HttpClient\HttpClientInterface` (via `symfony/http-client`, déjà présent).

```php
final class IngestProxyController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%posthog.api_host%')]
        private readonly string $apiHost,
    ) {}

    public function __invoke(string $path, Request $request): Response
    {
        $target = sprintf('%s/%s', $this->apiHost, $path);
        try {
            $upstream = $this->httpClient->request(
                $request->getMethod(),
                $target,
                [
                    'query'   => $request->query->all(),
                    'body'    => $request->getContent(),
                    'headers' => $this->forwardableHeaders($request),
                    'timeout' => 5.0,
                    'max_redirects' => 0, // pas de redirect côté PostHog
                ],
            );
            $content = $upstream->getContent(throw: false);
            $status  = $upstream->getStatusCode();
            $headers = $this->sanitizeResponseHeaders($upstream->getHeaders(throw: false));
            return new Response($content, $status, $headers);
        } catch (TransportException $e) {
            $this->logger->warning('PostHog proxy timeout', ['exception' => $e]);
            return new Response('', 504);
        }
    }

    private function forwardableHeaders(Request $request): array
    {
        return [
            'User-Agent'      => $request->headers->get('User-Agent', ''),
            'Content-Type'    => $request->headers->get('Content-Type', 'application/json'),
            'Accept'          => $request->headers->get('Accept', '*/*'),
            'Accept-Encoding' => $request->headers->get('Accept-Encoding', 'gzip'),
            // JAMAIS forward Cookie, Authorization, X-Forwarded-* (fuite données)
        ];
    }

    private function sanitizeResponseHeaders(array $headers): array
    {
        // Retirer : Set-Cookie (PostHog ne pose pas de cookie server-side dans notre config),
        // Content-Encoding (Symfony gère), Transfer-Encoding.
        unset($headers['set-cookie'], $headers['content-encoding'], $headers['transfer-encoding']);
        return $headers;
    }
}
```

### 6.3 Rate limiting

```yaml
# config/packages/rate_limiter.yaml (créer si absent)
framework:
  rate_limiter:
    posthog_ingest:
      policy: token_bucket
      limit: 600
      rate: { interval: '1 minute' }
```

Appliqué keyed par tuple `(distinct_id, IP)` — évite les 429 en cascade sur NAT d'entreprise / wifi public partagé. Distinct_id récupéré depuis header `X-PostHog-Distinct-Id` (injecté par SDK client) OU cookie `ph_*` first-party. Excès → 429 Too Many Requests.

### 6.4 Logging Sentry

Erreurs proxy (timeout, 5xx upstream, path invalide) loggées Sentry avec breadcrumb `posthog_ingest_proxy`. Seuil de silence : n'alerter que si taux d'erreur > 1 % sur 5 minutes (à configurer côté Sentry alerting rules, hors Symfony code).

## 7. Modèle identité

### 7.1 Distinct_id — hash email calculé server-side

Salt **marque-specific** aligné sur la réalité prod attalpresident.fr (`lib/posthog/identity.ts` L22-38). Format `SHA256("${SALT}:${email_norm}")` — SALT devant, `:` séparateur, email normalisé trim+lowercase. Divergence explicite vs doctrine §4.2 (cf. §2.4).

```php
final class HashEmailService
{
    private const SALT_BY_SITE = [
        'attalpresident'      => 'attalpresident-2027',
        'parti-renaissance'   => 'parti-renaissance-2027',
        'avecgabrielattal'    => 'avecgabrielattal-2027',
        'nouvellerepublique'  => 'nouvellerepublique-2027',
    ];

    public function __construct(private readonly SiteContext $context) {}

    public function hash(string $email): string
    {
        $site = $this->context->getSite();
        $salt = self::SALT_BY_SITE[$site] ?? throw new \RuntimeException("No salt for site: $site");
        return hash('sha256', $salt . ':' . $this->normalize($email));
    }

    private function normalize(string $email): string
    {
        return strtolower(trim($email));
    }
}
```

**Test snapshot obligatoire par marque** (unit test) — 4 assertions, hash `attalpresident` matche **byte-à-byte** le hash produit par `lib/posthog/identity.ts` prod (JS ↔ PHP cross-plateforme) :

```php
public function testKnownHashPerSite(): void
{
    $email = 'test@example.com';
    $expected = [
        'attalpresident'      => 'c8be02d2a41f9f84e80335c10ba29dddc09d94645cfbecf81a88161d86a3eda0',
        'parti-renaissance'   => 'f1bfce1212e9adc7c7e789acc6727ef278c48618c2fb3b99580fde3c891b87ea',
        'avecgabrielattal'    => 'ebc164cb050861a4297a5e658fbfabeb3e051770bcd659ea452ec80793ee8a9d',
        'nouvellerepublique'  => '1d07c9a32f4cf1a8d2542334ec6dc7fabedf9b69487969e9c4f9909bd98ad4f1',
    ];
    foreach ($expected as $site => $hash) {
        $ctx = new SiteContext(); $ctx->setSite($site);
        $service = new HashEmailService($ctx);
        $this->assertSame($hash, $service->hash($email), "Site: $site");
    }
}
```

Le test snapshot `attalpresident` garantit que le hash produit côté espace-adherent est **byte-identique** à celui produit par le web attalpresident.fr (`lib/posthog/identity.ts` prod) — condition de la ré-jonction cross-plateforme PostHog sur cette marque. Cross-brand se fait via `public_id` en `$set` + view BQ `posthog_identity_bridge` (mergée PR #175), PAS via distinct_id identique cross-domain.

### 7.2 Payload identify server-generated

Le PHP construit le payload `identify` complet au moment du rendering Twig (si `app.user` existe), sérialisé en JSON dans le `<script>` inline :

```php
public function buildIdentifyPayload(?Adherent $user): ?array
{
    if ($user === null || $user->getEmailAddress() === null) return null;
    return [
        'distinct_id' => $this->hashEmailService->hash($user->getEmailAddress()),
        '$set' => [
            'public_id' => $user->getPublicId(),
        ],
        '$set_once' => [
            'identified_from_site' => $this->siteContext->getSite(),
            'identified_at'        => (new \DateTimeImmutable())->format(DATE_ATOM),
        ],
    ];
}
```

Le JS `posthog-identify.js` consomme :

```javascript
if (window.__POSTHOG_IDENTIFY__) {
    posthog.identify(
        window.__POSTHOG_IDENTIFY__.distinct_id,
        {
            $set: window.__POSTHOG_IDENTIFY__.$set,
            $set_once: window.__POSTHOG_IDENTIFY__.$set_once,
        }
    );
}
```

> **Trade-off sécurité `window.__POSTHOG_IDENTIFY__.distinct_id`** :
> Le hash sérialisé dans le HTML est théoriquement accessible à tout script tiers de la page. Le salt marque-specific + email en dictionnaire = pseudonymisation Art. 4(5) RGPD **cassable en bruteforce** sur une liste d'emails Renaissance. Le mart BQ est protégé (IAM), mais le DOM est en clair.
>
> **Mitigations appliquées** :
> 1. Le `<script>` inline injectant `window.__POSTHOG_IDENTIFY__` est rendu **uniquement si consent granted** (`posthog_consent_state === true`). Un user opt-out n'expose pas son hash.
> 2. Aucun script tiers marketing/analytics tiers n'est chargé côté espace-adherent (grep confirmé : Sentry + Matomo dual-run 4 sem uniquement).
> 3. Follow-up Phase 2 : passer identify server-only via `PostHogService::captureServerSide('identify_bootstrap', ...)` sans exposer le hash côté JS. Le SDK client rejoint via cookie `ph_*` first-party posé par le proxy. Coût dev ~1 jour. À planifier après validation dual-run.

### 7.3 Logout

Server-side dans `AuthEventSubscriber::onLogout()` (`LogoutEvent` dispatché par le firewall Symfony 7.4) :

```php
public function onLogout(LogoutEvent $e): void
{
    $this->postHogService->captureServerSide(PostHogEventName::LOGOUT_COMPLETED, []);
    // Client-side, JS appellera posthog.reset() sur la prochaine page (post-logout redirect)
}
```

Le JS `posthog-init.js` détecte la présence d'un flag session `__POSTHOG_RESET__` posé server-side sur la redirect post-logout :

```javascript
if (sessionStorage.getItem('__posthog_reset__') === '1') {
    posthog.reset();
    sessionStorage.removeItem('__posthog_reset__');
}
```

Bien qu'un peu artisanal, ce pattern évite d'appeler `reset()` au boot de chaque page (perte d'identifiant en cas de F5).

## 8. Instrumentation par cluster (mapping code → events)

### 8.1 Cluster Connexion

Wire via un **EventSubscriber** dédié `AuthEventSubscriber` (pattern Symfony 7.4 standard, cohérent avec `UserActionHistorySubscriber` existant du repo). PAS de wire dans le controller Renaissance login (simple form renderer, aucun post-login handling) — le login est traité par le **firewall Symfony 7.4** qui dispatch `LoginSuccessEvent`, `LoginFailureEvent`, `LogoutEvent`.

| Trigger | Event PostHog | Symfony Event | Placement |
|---|---|---|---|
| Firewall auth success | `login_succeeded` | `Symfony\Component\Security\Http\Event\LoginSuccessEvent` | `AuthEventSubscriber::onLoginSuccess()` |
| Firewall auth failure | `login_failed` (`reason=bad_credentials`) | `LoginFailureEvent` | `AuthEventSubscriber::onLoginFailure()` |
| Firewall logout | `logout_completed` | `LogoutEvent` | `AuthEventSubscriber::onLogout()` |
| Password reset request | `password_reset_requested` | GET/POST `/mot-de-passe-oublie` succès | `Renaissance\SecurityController::retrieveForgotPasswordAction()` (post-flush) |
| Password reset completed | `password_reset_completed` | GET/POST `/changer-mot-de-passe/{uuid}/{token}` succès | `Renaissance\SecurityController::resetPasswordAction()` (post-flush) |
| Magic link request | `magic_link_requested` | POST `/demander-un-lien-magique` succès | `MagicLinkController::getMagicLinkAction()` |
| Magic link login | `magic_link_login_succeeded` | Firewall auth via magic link | `AuthEventSubscriber::onLoginSuccess()` détecte la méthode d'auth |

```php
final class AuthEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly PostHogService $postHog) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
            LogoutEvent::class       => 'onLogout',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $e): void
    {
        $method = $this->detectAuthMethod($e); // form|magic-link|oauth
        $event  = $method === 'magic-link'
            ? PostHogEventName::MAGIC_LINK_LOGIN_SUCCEEDED
            : PostHogEventName::LOGIN_SUCCEEDED;
        $this->postHog->captureServerSide($event, ['method' => $method]);
    }

    public function onLoginFailure(LoginFailureEvent $e): void
    {
        $this->postHog->captureServerSide(
            PostHogEventName::LOGIN_FAILED,
            ['reason' => $this->classifyError($e->getException())],
        );
    }

    public function onLogout(LogoutEvent $e): void
    {
        $this->postHog->captureServerSide(PostHogEventName::LOGOUT_COMPLETED, []);
    }
}
```

Toutes ces captures fired **server-side** via `PostHogService::captureServerSide()` — cohérent avec les events du même cluster côté web (spec cross-sites §4.3 point 4).

**Race condition connue** — l'event server-side `login_succeeded` est fired **avant** que le client JS ait pu appeler `identify()` (le SDK boot au 1er render page post-login). Si le user quitte la page pendant le redirect (302 typique post-login), les events anonymes de la session pre-login (`$pageview`, `$autocapture` sur le form login) restent sous l'anonymous distinct_id et ne sont pas mergés à la person hash_email. Perte marginale de tracking (~ quelques events par login). Non-bloquant. Pas de mitigation prévue Phase 1.

### 8.2 Cluster Adhésion

| Trigger | Event | Cas | Payload spécifique |
|---|---|---|---|
| Affichage `/adhesion` (GET) | `adhesion_started` | 1 | `has_referrer_pid: bool` |
| Submit `/adhesion` (POST étape 1) | `adhesion_form_submitted` | 1 | `step: "info_personnelle"` |
| Redirect `/paiement/{uuid}` | `adhesion_payment_initiated` | 1 | `payment_provider: "paybox"` |
| Callback paiement succès | `adhesion_completed` | 1 | `amount_eur: int`, `payment_method: string`, `is_first_adhesion: bool` |
| Callback paiement refus | `adhesion_payment_failed` | 1 | `reason: "declined" \| "cancelled" \| "timeout"` |
| GET `/adhesion/felicitations` | `adhesion_finish_page_viewed` | 1 | (aucun) |

Wire dans `AdhesionController::__invoke()` (`src/Controller/Renaissance/Adhesion/AdhesionController.php`), `Renaissance\Adhesion\PaymentController`, `Renaissance\Adhesion\FinishController`.

### 8.3 Cluster Don

| Trigger | Event | Cas | Payload |
|---|---|---|---|
| GET `/don` | `donation_started` | 1 | `is_grand_donateur: bool` |
| Submit `/don` étape 1 | `donation_form_submitted` | 1 ou 2 | `amount_eur`, `is_recurring: bool` |
| Redirect payment | `donation_payment_initiated` | 1 ou 2 | `payment_provider: "paybox"` |
| Callback succès | `donation_completed` | 1 ou 2 | `amount_eur`, `is_recurring`, `donor_type: "user" \| "anonymous"` |
| Callback refus | `donation_payment_failed` | 1 ou 2 | `reason` |

**Décision** : Cas 1 forcé (aucun `$set.email` côté client, aucun `$set.email` côté server pour ce cluster). Les dons anonymes sans compte ne sont pas trackés avec `$set.email` — ils remontent en `donation_completed` avec `donor_type: "anonymous"` en propriété, l'email n'est PAS envoyé à PostHog. Cross-jointure BQ possible via `donations.donator_uuid` côté DWH Renaissance.

Wire dans `Renaissance\Donation\DonationController`, `Renaissance\Payment\PaymentController`, `Renaissance\Donation\FinishController`.

### 8.4 Cluster Meeting national

| Trigger | Event | Cas | Payload |
|---|---|---|---|
| GET `/grand-rassemblement/{slug}` | `national_event_page_viewed` | 1 ou 2 | `event_slug`, `event_uuid`, `has_referrer_pid: bool` |
| Submit inscription (POST) | `national_event_inscription_submitted` | 1 ou 2 | `event_uuid`, `is_paid: bool` |
| Confirm inscription | `national_event_inscription_confirmed` | 1 ou 2 | `event_uuid`, `inscription_uuid` |
| Payment callback | `national_event_payment_completed` | idem | `event_uuid`, `amount_eur`, `payment_method` |
| Edit inscription | `national_event_inscription_edited` | idem | `event_uuid` |

**Décision** : Cas 1 forcé — l'inscription meeting national requiert de facto une identification email en clair (feedback participant, envoi consignes sécurité, ticket). L'email est en cookie session serveur seul, pas envoyé PostHog. Events tracked : `national_event_inscription_submitted` avec `inscription_uuid` en propriété, jointure DWH via `national_event_inscriptions.email` (server-only column, hors PostHog).

Wire dans `Renaissance\NationalEvent\InscriptionController`, `Renaissance\NationalEvent\ConfirmInscriptionController`, `Renaissance\NationalEvent\EditInscriptionController`, `Renaissance\NationalEvent\PaymentStatusController`.

### 8.5 Cluster Profil

| Trigger | Event | Cas | Payload |
|---|---|---|---|
| GET `/parametres/mon-compte` | `profile_page_viewed` | 1 | (aucun) |
| POST `/parametres/mon-compte` succès | `profile_updated` | 1 | `fields_changed: string[]` (noms techniques, pas les valeurs) |
| GET `/espace-adherent/evenements/mes-evenements` | `my_events_page_viewed` | 1 | (aucun) |
| Inscription événement local | `local_event_registered` | 1 | `event_uuid`, `event_type` |
| GET `/espace-adherent/consultations` | `consultation_list_viewed` | 1 | (aucun) |
| Submit consultation | `consultation_answered` | 1 | `consultation_uuid` |

Wire dans `Renaissance\Adherent\ProfileController`, `Renaissance\Adherent\EventController`, `Renaissance\Consultation\ListController`.

### 8.6 Cluster Newsletter (Cas 2, server-side)

| Trigger | Event | Cas | Payload |
|---|---|---|---|
| POST `/api/newsletter` succès | `newsletter_submitted_server` | **2** | `$set: { email }`, `postal_code_prefix`, `source_page` |
| GET `/newsletter/confirmation/{uuid}/{token}` | `newsletter_confirmed_server` | **2** | `$set: { email }` (email déjà validé) |

`$set.email` autorisé par la doctrine §3 nomenclature globale (form sans compte) — cet event est whitelist dans le grep CI privacy.

Wire dans `Renaissance\Newsletter\SaveNewsletterController`, `Renaissance\Newsletter\ConfirmNewsletterController`.

### 8.7 Cluster Pétition (Cas 2, server-side)

| Trigger | Event | Cas | Payload |
|---|---|---|---|
| GET `/petition/validate/{uuid}/{token}` | `petition_signed_server` | **2** | `$set: { email }`, `petition_uuid` |

Wire dans `Renaissance\Petition\SignatureValidateController`.

## 9. Registry events (source unique de vérité)

30 events custom Renaissance web + événements PostHog natifs (`$pageview`, `$web_vitals`, `$autocapture` selon config).

### 9.1 Enum PHP `PostHogEventName`

```php
enum PostHogEventName: string
{
    // Consent
    case CONSENT_GRANTED = 'consent_granted';
    case CONSENT_REFUSED = 'consent_refused';
    case CONSENT_DISMISSED = 'consent_dismissed';

    // Auth
    case LOGIN_SUCCEEDED = 'login_succeeded';
    case LOGIN_FAILED = 'login_failed';
    case LOGOUT_COMPLETED = 'logout_completed';
    case PASSWORD_RESET_REQUESTED = 'password_reset_requested';
    case PASSWORD_RESET_COMPLETED = 'password_reset_completed';
    case MAGIC_LINK_REQUESTED = 'magic_link_requested';
    case MAGIC_LINK_LOGIN_SUCCEEDED = 'magic_link_login_succeeded';

    // Adhésion
    case ADHESION_STARTED = 'adhesion_started';
    case ADHESION_FORM_SUBMITTED = 'adhesion_form_submitted';
    case ADHESION_PAYMENT_INITIATED = 'adhesion_payment_initiated';
    case ADHESION_COMPLETED = 'adhesion_completed';
    case ADHESION_PAYMENT_FAILED = 'adhesion_payment_failed';
    case ADHESION_FINISH_PAGE_VIEWED = 'adhesion_finish_page_viewed';

    // Don
    case DONATION_STARTED = 'donation_started';
    case DONATION_FORM_SUBMITTED = 'donation_form_submitted';
    case DONATION_PAYMENT_INITIATED = 'donation_payment_initiated';
    case DONATION_COMPLETED = 'donation_completed';
    case DONATION_PAYMENT_FAILED = 'donation_payment_failed';

    // Meeting national
    case NATIONAL_EVENT_PAGE_VIEWED = 'national_event_page_viewed';
    case NATIONAL_EVENT_INSCRIPTION_SUBMITTED = 'national_event_inscription_submitted';
    case NATIONAL_EVENT_INSCRIPTION_CONFIRMED = 'national_event_inscription_confirmed';
    case NATIONAL_EVENT_PAYMENT_COMPLETED = 'national_event_payment_completed';
    case NATIONAL_EVENT_INSCRIPTION_EDITED = 'national_event_inscription_edited';

    // Profil
    case PROFILE_PAGE_VIEWED = 'profile_page_viewed';
    case PROFILE_UPDATED = 'profile_updated';
    case MY_EVENTS_PAGE_VIEWED = 'my_events_page_viewed';
    case LOCAL_EVENT_REGISTERED = 'local_event_registered';
    case CONSULTATION_LIST_VIEWED = 'consultation_list_viewed';
    case CONSULTATION_ANSWERED = 'consultation_answered';

    // Newsletter (Cas 2)
    case NEWSLETTER_SUBMITTED_SERVER = 'newsletter_submitted_server';
    case NEWSLETTER_CONFIRMED_SERVER = 'newsletter_confirmed_server';

    // Pétition (Cas 2)
    case PETITION_SIGNED_SERVER = 'petition_signed_server';
}
```

### 9.2 Miroir JS

`assets/analytics/posthog/posthog-capture.js` expose `POSTHOG_EVENTS` — dict const clé/valeur identique à l'enum PHP (généré à la main + test structurel qui vérifie la cohérence 1:1 : `tests/Analytics/PostHog/PostHogRegistryConsistencyTest.php`).

### 9.3 Payload PHP typé

Value-objects par event dans `App\Analytics\PostHog\Events\` :

```php
final readonly class ProfileUpdatedPayload
{
    public function __construct(
        /** @var list<string> */
        public array $fieldsChanged,
    ) {}

    public function toArray(): array
    {
        return ['fields_changed' => $this->fieldsChanged];
    }
}
```

Le `PostHogService::captureServerSide(PostHogEventName $event, ?PostHogPayloadInterface $payload)` sérialise en JSON et POST vers `eu.i.posthog.com/capture` avec le `distinct_id` du user courant (ou anonymous si pas connecté).

## 10. Super-properties (rappel doctrine cross-sites §6)

### 10.1 Auto (register au boot, envoyées avec CHAQUE event)

| Nom | Valeur exemple | Source |
|---|---|---|
| `site` | `parti-renaissance` | `SiteContext::getSite()` |
| `platform` | `web` \| `mobile-web` | User-Agent detection JS (`window.matchMedia('(max-width: 768px)')`) |
| `hostname` | `utilisateur.parti-renaissance.fr` | PostHog SDK `$host` auto |
| `environment` | `production` \| `staging` \| `preview` \| `dev` | `%env(APP_ENVIRONMENT)%` |
| `deploy_sha` | 7 chars git SHA | `%env(DEPLOY_SHA)%` |
| `deploy_version` | tag semver ou `null` | `%env(APP_VERSION)%` |
| `locale` | `fr-FR` | `Accept-Language` server-side |
| `is_bot` | `false` (défaut) | Server-side UA heuristic |

### 10.2 Post-login (`$set` + `$set_once`)

| Nom | Type | Note |
|---|---|---|
| `public_id` (`$set`) | string (7 chars) | Réappliqué à chaque login (peut avoir été mis à jour BO) |
| `identified_from_site` (`$set_once`) | string enum `site` | Immutable |
| `identified_at` (`$set_once`) | ISO 8601 | Immutable |

### 10.3 Autocapture PostHog

Config recommandée `autocapture: { captureLifecycleEvents: true, captureScreens: true, captureTouches: false }`. Autocapture donne gratuitement `$pageview`, `$autocapture` (clics sur boutons/liens taggés), et lifecycle events.

`data-ph-no-capture` posé sur tous les inputs sensibles (mot de passe, IBAN, numéro adhérent) — respect doctrine §11 spec cross-sites.

## 11. Testing

### 11.1 PHPUnit — 5 tests critiques initiaux

- `SiteDetectorTest` : mapping 4 hostnames + fallback + WARNING log.
- `HashEmailServiceTest` : snapshot `test@example.com` → hash figé + normalisation trim/lowercase.
- `ConsentCookieHelperTest` : scopes cookie par marque + migration idempotente `ap_consent` (given cookie existant, when read, then no re-write).
- `IngestProxyControllerTest` : whitelist paths (200/404), forward correct, headers sanitizés, timeout → 504, **régression sanitizer** — vérifie qu'un `Set-Cookie` custom PostHog EU (`x-posthog-set-cookie`, `posthog-session-cookie`, ou tout autre header cookie-related non explicitement whitelisté) est également stripped par `sanitizeResponseHeaders()`.
- `PostHogServiceTest` : super-properties builder, cache par request, event capture server-side POST correct.

Coverage cible Phase 1 : **90 %** sur `src/Analytics/PostHog/` (services purs, fichiers < 150 LOC chacun, testabilité maximale). 60 % initial était trop bas pour un composant qui pilote le mart BQ Renaissance.

### 11.2 Behat — 3 scenarios d'intégration

- `analytics_consent_banner.feature` : bannière affichée si cookie absent, clics Accepter/Refuser posent le cookie correct, migration idempotente `ap_consent=0`.
- `analytics_multi_domain.feature` : requête sur `utilisateur.parti-renaissance.fr` → `posthog_site='parti-renaissance'` en Twig, requête sur `utilisateur.attalpresident.fr` → `posthog_site='attalpresident'`.
- `analytics_ingest_proxy.feature` : POST `/ingest/e/` forward vers PostHog EU avec les bons headers + status, GET `/ingest/interdit` renvoie 404.

## 12. CI/CD

### 12.1 Workflow `.github/workflows/lint-posthog-privacy.yml`

Adapté du template crm-integrations (déjà utilisé sur Vox mobile) — extensions supplémentaires PHP/Twig/JS :

- Grep bloquant `posthog\.capture\([^)]*email` sur `*.js`, `*.ts`, `*.php`, `*.twig`.
- Grep bloquant `posthog\.identify\([^)]*@` (adresse email littérale).
- Grep bloquant `\$set[^)]*email` **hors** liste blanche (whitelist server-side stricte : `newsletter_submitted_server`, `newsletter_confirmed_server`, `petition_signed_server`).
- Verify cohérence enum PHP ↔ dict JS : chaque `POSTHOG_EVENTS.X` utilisé doit exister dans `PostHogEventName::X`.

Bypass label GitHub : `posthog-privacy-ok` (justification commentaire PR).

### 12.2 CI existants inchangés

Pipelines PHPUnit + Behat + PHPStan + build-prod (webpack) continuent normalement. Ajoutent les 5 tests PHPUnit + 3 Behat scenarios ci-dessus.

## 13. Séquence commits atomiques (~18 commits)

1. `chore(deps): install posthog-js@^1.150 (SDK web officiel)`
2. `chore(env): variables POSTHOG_API_KEY + POSTHOG_HOST + hash_email_salt + POSTHOG_ENABLED (bool default false) + config/packages/prod/posthog.yaml`
3. `feat(analytics): SiteDetector + SiteContext + SiteContextListener + tests unitaires`
4. `feat(analytics): HashEmailService + snapshot test cross-plateforme (test@example.com)`
5. `feat(analytics): ConsentCookieHelper + tests migration idempotente ap_consent`
6. `feat(analytics): IngestProxyController + whitelist paths + rate-limit + tests`
7. `feat(analytics): PostHogService + Twig extension (posthog_site, posthog_super_properties, posthog_identify_payload)`
8. `feat(analytics): enum PHP PostHogEventName + payloads value-objects typés + registry consistency test`
9. `feat(analytics): assets/analytics/posthog/ (init, capture, consent, identify) + webpack entry`
10. `feat(consent): templates/_snippet.html.twig + _consent_banner.html.twig + Alpine.js component`
11. `feat(consent): ConsentSettingsController + POST /parametres/confidentialite + template écran Réglages`
12. `feat(analytics): wire login/logout/magic-link/password-reset events (AuthEventSubscriber + MagicLinkController + SecurityController password reset flows)`
13. `feat(analytics): wire adhesion events (AdhesionController + Paiement + FinishController)`
14. `feat(analytics): wire donation events (DonationController + Payment + Finish)`
15. `feat(analytics): wire national event events (Inscription + Confirm + Edit + Payment)`
16. `feat(analytics): wire profile events (ProfileController + EventController + Consultation)`
17. `feat(analytics): wire newsletter + petition server-side events ($set.email Cas 2)`
18. `feat(twig): mount posthog snippet + banner dans base_renaissance.html.twig (activation finale, POSTHOG_ENABLED=false par défaut)`
19. `ci(privacy): workflow lint-posthog-privacy adapté PHP/Twig/JS + whitelist Cas 2`
20. `docs(analytics): ADR local posthog-multi-domain + taxonomie posthog-events-parti-renaissance.md`

Total ~20 commits. Marge d'ajustement ±2 selon besoins reprise dev.

**Activation feature-flag** — le commit 18 pose PostHog en mode `POSTHOG_ENABLED=false` par défaut sur tous les environnements. Toggle explicit `POSTHOG_ENABLED=true` staging puis prod après validation manuelle (les 4 hostnames répondent au bon `site`, cookie consent posé correctement, ingest proxy 200). Rollback via toggle env var sans revert code.

## 14. Documentation code

### 14.1 Headers JSDoc/PHPdoc sur chaque fichier critique

```php
/**
 * services/analytics/posthog/SiteDetector.php
 *
 * Rôle : détecte la marque Renaissance depuis Request::getHost() → site enum.
 *
 * 4 marques mappées explicitement (nomenclature §2 crm-integrations). Fallback
 * `parti-renaissance` avec WARNING Sentry en cas de hostname non-mappé.
 *
 * Cf. spec cross-sites §2.1 (multi-domain), ADR local posthog-multi-domain.
 * Reviewers : dev backend, Emilien Vandevelde.
 */
```

Idem sur `HashEmailService`, `ConsentCookieHelper`, `IngestProxyController`, `PostHogService`.

### 14.2 Tests documentés Arrange/Act/Assert

Chaque test PHPUnit porte des commentaires clairs délimitant `// Arrange`, `// Act`, `// Assert`. Behat scenarios en français, gherkin lisible par un PO non-dev.

### 14.3 Body PR CHANGELOG structuré (obligatoire)

Sections attendues : Résumé / Décisions structurantes / Impact code / Tests / Checkpoints reviewer par role / Follow-ups.

## 15. Review humaine

### 15.1 Reviewer backend (Symfony PHP)

- Valider `SiteContextListener` priority (250) et thread-safety si le kernel est réutilisé (Symfony HttpKernel — safe par design).
- Valider `IngestProxyController` sanitisation headers (fuite éventuelle X-Forwarded-For vers PostHog EU ?).
- Valider `HashEmailService` snapshot cross-plateforme match doctrine.
- Valider migration idempotente `ap_consent` par Behat scenario.
- Valider absence PII dans les logs Sentry (email jamais loggé, `hash_email` OK).

### 15.2 Reviewer front-end (JS + Twig)

- Valider absence flash bannière ↔ SDK state (server-side detection préventive).
- Valider `posthog-init.js` init order : Sentry init AVANT PostHog (Sentry doit capturer d'éventuelles erreurs PostHog).
- Valider Alpine.js component `posthog-consent-banner.js` non-intrusif (pas de bloc scroll global).
- Valider `data-ph-no-capture` posé sur tous les inputs sensibles (mot de passe, IBAN, RIB, numéro adhérent).

### 15.3 Reviewer final (Emilien Vandevelde)

- Valider **conformité doctrine cross-sites §4-5-6-7-11** — aucune divergence non-documentée par ADR.
- Valider **doctrine email Cas 1/Cas 2** appliquée sur les 7 clusters de parcours (audit sur les 30 events du registry).
- Valider **structure PR** : commits atomiques, CHANGELOG PR complet, checkpoints reviewer cochés.
- Valider **rollback plan** : `git revert` de la PR restaure Matomo + zéro tracking sans casser aucune route.

**Limite du rollback** : les cookies consent `pr_consent` / `ap_consent` / `aga_consent` / `nr_consent` posés chez les users **survivent au `git revert`** (13 mois TTL). Après revert, du code Matomo se réactive mais les cookies orphelins persistent. Non bloquant (Matomo n'utilise pas ces cookies) mais à noter. Purge explicite via endpoint transient `/analytics-cookies-purge` prévu en Phase 2 si besoin.

## 16. Deploy checklist (hors PR, actions user/infra)

- [ ] DPA PostHog Inc. signé côté parti-renaissance (déjà OK selon ADR-011, à confirmer étendu aux 4 marques).
- [ ] Politique de confidentialité mise à jour sur les 4 sites — ligne "analyse d'usage anonymisée" DPO validée 2026-07-14.
- [ ] PIA/DPIA : confirmation DPO que le périmètre couvre les 4 marques via espace-adherent (avenant si nécessaire, coordination follow-up crm-integrations).
- [ ] Env vars déployées prod + staging : `POSTHOG_API_KEY` (par environnement), `POSTHOG_HOST=https://eu.i.posthog.com`, `POSTHOG_ENABLED=false` par défaut (toggle explicite pour activation).
- [ ] Domaines actifs déployés : vérifier que `utilisateur.parti-renaissance.fr`, `utilisateur.attalpresident.fr`, `utilisateur.avecgabrielattal.fr`, `utilisateur.nouvellerepublique.fr` répondent au même backend Symfony.
- [ ] Sentry alerting rules : configurer alertes sur `posthog_ingest_proxy` (taux d'erreur > 1 % sur 5 min).
- [ ] Batch Export PostHog Cloud EU → mart BQ `Custom_PostHog` : déjà en place, aucune action Vox.
- [ ] Post-observation 4 semaines : PR ultérieure `chore(matomo): remove after posthog dual-run` selon critères GO §3.3.

> **ADR-006 LOCKED (rétention illimitée mart BQ) rappel** — exige explicitement une PIA/DPIA signée AVANT l'activation Batch Export PostHog vers `Custom_PostHog`. Confirmation user 2026-07-14 : PIA/DPIA déjà signée DPO pour périmètre 4 marques Renaissance web + Vox mobile. Référence archive DPO Renaissance interne.

## 17. Divergences doctrine crm-integrations (ADRs à créer / spec updates)

Aucun bump de l'enum `site` requis (les 4 marques sont déjà couvertes par la nomenclature §2, la 5e valeur `app-mobile` déjà mergée pour Vox). Aucun ADR crm-integrations upgrade requis à priori.

**1 update spec cross-sites §3.2bis** (Reverse proxy multi-domaine espace-adherent — pattern Symfony Controller adopté) + **1 update spec cross-sites §4.2** (aligner doctrine hash `SALT:email` marque-specific sur la réalité prod, cf. §2.4 note divergence) — mini-PR crm-integrations séparée en follow-up. **1 ADR local espace-adherent** (`docs/adrs/posthog-multi-domain.md`, créé dans la PR d'implémentation côté aval, pattern identique à `espace-militant:docs/adrs/mobile-posthog-integration.md`).

## 18. Follow-ups (hors périmètre de cette PR)

### Immédiats (à ouvrir en même temps que la PR)

- **Backend Renaissance** : aucun (`public_id` déjà exposé côté PHP via `PublicIdTrait`, l'email est accessible via `Adherent::getEmailAddress()`) — Phase 0 utile OK by default sur ce repo.
- **crm-integrations** : mini-PR update spec cross-sites §3.2bis (Symfony reverse proxy pattern).
- **DPO** : confirmation avenant PIA/DPIA périmètre 4 marques via espace-adherent (rappel `pr_consent`/`aga_consent`/`nr_consent` sont nouveaux cookies).

### Phase 2 (post-observation 4 sem)

- **espace-adherent** : PR ultérieure `chore(matomo): remove after dual-run` (suppression code Matomo custom `base_renaissance.html.twig` L39-60 + assets Matomo si présents).
- **espace-adherent** : Phase 2 bouton "Supprimer mes données analytics" dans `/parametres/confidentialite` (droit à l'oubli in-app, D4 déjà tracé côté Vox mobile).
- **espace-adherent** : élargir couverture PHPUnit à 80 % + tests intégration Behat sur les 7 clusters.
- **espace-adherent** : améliorer autocapture UI (posts sensibles `data-ph-no-capture` audit complet).

### Phase 3 (mart BQ Renaissance)

- Aucune action supplémentaire attendue côté crm-integrations : la view `posthog_identity_bridge` (créée dans PR #175) traite déjà les 4 marques web (pivot `public_id` + `hash_email`) — ajouter espace-adherent au mart ne nécessite aucune modification SQL.

### Follow-up sécurité CSP

Si l'équipe sécu ajoute un `Content-Security-Policy` header strict post-merge, le `<script>` inline `window.__POSTHOG_IDENTIFY__` requiert `unsafe-inline` ou un nonce généré server-side. Audit CSP avant deploy prod : `curl -I https://utilisateur.parti-renaissance.fr/ | grep -i content-security-policy`. Si header présent, ajouter `nonce="{{ csp_nonce }}"` sur le `<script>` inline.

## 19. Annexes

### 19.1 Snapshot hash email cross-plateforme (unit test référence)

Salt **marque-specific** aligné sur la réalité prod attalpresident.fr (`lib/posthog/identity.ts` L22-38). Format `SHA256("${SALT}:${email_norm}")` avec `email_norm = lowercase(trim(input))`.

```
INPUT  : "test@example.com"

Site: attalpresident
  SALT : "attalpresident-2027"
  HASH : c8be02d2a41f9f84e80335c10ba29dddc09d94645cfbecf81a88161d86a3eda0

Site: parti-renaissance
  SALT : "parti-renaissance-2027"
  HASH : f1bfce1212e9adc7c7e789acc6727ef278c48618c2fb3b99580fde3c891b87ea

Site: avecgabrielattal
  SALT : "avecgabrielattal-2027"
  HASH : ebc164cb050861a4297a5e658fbfabeb3e051770bcd659ea452ec80793ee8a9d

Site: nouvellerepublique
  SALT : "nouvellerepublique-2027"
  HASH : 1d07c9a32f4cf1a8d2542334ec6dc7fabedf9b69487969e9c4f9909bd98ad4f1
```

**Divergence explicite vs doctrine §4.2** (bloc autorisé — cf. §2.4 note) : la doctrine cross-sites postulait un salt global `renaissance-2027` sans séparateur (`SHA256(email + salt)`). La réalité prod attalpresident.fr — issue de `lib/posthog/identity.ts` L22-38 — utilise (1) un salt marque-specific et (2) un format `SHA256("${SALT}:${email}")` avec `:` séparateur, salt devant. Les PRs migration #308-#310 vers salt global renaissance-2027 n'ont pas été mergées en prod. Décision espace-adherent : **s'aligner sur la réalité prod** (retro-compat immédiate `attalpresident.fr`).

Le hash `attalpresident` est **byte-identique** à celui produit par :

- Web attalpresident.fr SDK JS (`lib/posthog/identity.ts` prod)
- Server-side espace-adherent PHP servi sur `utilisateur.attalpresident.fr` (cette PR)

Les hashes des 3 autres marques (`parti-renaissance`, `avecgabrielattal`, `nouvellerepublique`) sont **nouveaux** — aucun pré-existant en prod à croiser. Cross-brand identité se fait via `public_id` en `$set` + view BQ `posthog_identity_bridge` (mergée PR #175), pas via distinct_id.

**Vox mobile utilise `distinct_id = user_uuid`, hors doctrine — cf. spec §10.8.**

### 19.2 Query BQ jointure adhérent × événements web multi-marque

```sql
SELECT
  a.public_id,
  a.email_address,
  e.properties.site AS marque,
  e.event,
  COUNT(*) AS event_count,
  MIN(e.timestamp) AS first_seen,
  MAX(e.timestamp) AS last_seen
FROM `re-crm-integrations.Custom_PostHog.events` e
JOIN `re-crm-integrations.Custom_PostHog.v_posthog_identity_bridge` b
  ON b.person_id = e.person_id_resolved  -- via mart posthog_acquisition
JOIN `re-crm-integrations.Analytics.adherent_enriched` a
  ON a.public_id = b.public_id
WHERE e.properties.site IN ('parti-renaissance', 'attalpresident', 'avecgabrielattal', 'nouvellerepublique')
  AND DATE(e.timestamp) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
GROUP BY 1, 2, 3, 4
ORDER BY event_count DESC
```

### 19.3 Query BQ comparaison Matomo ↔ PostHog dual-run

```sql
WITH matomo_daily AS (
  SELECT DATE(hit_date) AS day,
         action_name,
         COUNT(*) AS matomo_hits
  FROM `en-marche-prod.Matomo.hits_raw`
  WHERE site_id IN (6, 10)  -- espace-adherent prod / staging
  GROUP BY 1, 2
),
posthog_daily AS (
  SELECT DATE(timestamp) AS day,
         event AS action_name,
         COUNT(*) AS posthog_events
  FROM `re-crm-integrations.Custom_PostHog.events`
  WHERE properties.site IN ('parti-renaissance', 'attalpresident', 'avecgabrielattal', 'nouvellerepublique')
    AND event IN ('$pageview', 'login_succeeded', 'donation_completed', 'adhesion_completed')
  GROUP BY 1, 2
)
SELECT
  COALESCE(m.day, p.day) AS day,
  COALESCE(m.action_name, p.action_name) AS action_name,
  m.matomo_hits,
  p.posthog_events,
  ROUND(SAFE_DIVIDE(p.posthog_events, m.matomo_hits) - 1, 2) AS ratio_diff_pct
FROM matomo_daily m
FULL OUTER JOIN posthog_daily p USING (day, action_name)
WHERE COALESCE(m.day, p.day) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
ORDER BY day DESC, action_name
```

### 19.4 Mapping doctrine cross-sites → espace-adherent

| Point spec cross-sites | Application espace-adherent |
|---|---|
| §3.1 Projet PostHog unique | 1 projet PostHog EU pour toutes les marques (déjà en place) |
| §3.2 Reverse proxy `/ingest/*` par site | Symfony Controller `IngestProxyController` (Option A tranchée §2.3) |
| §3.3 Abandon linker URL cross-domain | Respecté — pas de query param cross-domain |
| §4.1 États anonymous → identified | Bridge auth ↔ consent implicit (§2.6) |
| §4.2 Hash email `SHA256(email + "renaissance-2027")` (doctrine) | **Divergence documentée** (§2.4 note) : `SHA256("${SALT_SITE}:${email}")` salts marque-specific, calculé server-side PHP (§7.1), byte-identique à `lib/posthog/identity.ts` prod attalpresident.fr |
| §4.3 Doctrine 2 cas | Appliqué par cluster (§8 mapping) |
| §4.4 Super-properties métier post-identify | `public_id` en `$set` + `identified_from_site`/`identified_at` en `$set_once` (§10.2) |
| §5.2 Cookie consent scopé root-domain marque | 4 cookies par marque, migration idempotente `ap_consent` (§2.2) |
| §6 Super-properties obligatoires | 8 auto boot + 3 post-identify (§10) |
| §7 Instrumentation | 30 events custom + autocapture PostHog (§9) |
| §8 UTMs | Autocapture PostHog gère les UTMs par défaut (`$initial_utm_*`, `$latest_utm_*`) |
| §11 Sécurité RGPD | `data-ph-no-capture` sur inputs sensibles, IP anonymization PostHog par défaut EU |

---

**Fin de spec.** Prochaine étape : review humaine par Victor, puis invocation `writing-plans` pour le plan d'implémentation détaillé (mapping commit → checklist reprise dev backend + front-end).
