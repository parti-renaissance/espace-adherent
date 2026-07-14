# PostHog Integration in espace-adherent (multi-domain Symfony) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Intégrer PostHog dans le backend Symfony `espace-adherent` (site principal Renaissance) servi en mode white-label multi-domaine sur 4 hostnames (`utilisateur.{parti-renaissance|attalpresident|avecgabrielattal|nouvellerepublique}.fr`), avec détection dynamique du `site`, cookies consent scopés root-domain par marque, reverse proxy `/ingest/*` Symfony, doctrine identité alignée sur la réalité prod attalpresident.fr (salts marque-specific `${SALT}:${email}`).

**Architecture:** Services PHP `src/Analytics/PostHog/` (SiteDetector + SiteContext + HashEmailService + ConsentCookieHelper + PostHogService + AuthEventSubscriber + IngestProxyController + PostHogTwigExtension), assets JS `assets/analytics/posthog/` compilés via webpack dans le bundle `bootstrap`, templates Twig `templates/analytics/posthog/`, workflow CI `lint-posthog-privacy.yml` PHP/Twig/JS, dual-run Matomo 4 semaines puis PR de suppression.

**Tech Stack:** Symfony 7.4 + PHP 8.5 (`hash('sha256', ...)`), Twig 3, `posthog-js@^1.180` SDK web officiel, Webpack 5 + Alpine.js 3.13, PHPUnit 13 + Behat 3.10, GitHub Actions (existing CI + nouveau workflow privacy).

**Spec parente:** `docs/superpowers/specs/2026-07-14-espace-adherent-posthog-multi-domain-design.md` (vdef, 1243 lignes).

**Repos:**
- Développement : `~/dev/analysis/espace-adherent` (branche `feat/RE-5165-posthog-web` à créer depuis `master`)
- Docs source de vérité : ce repo `crm-integrations` (specs + nomenclature)

**Reviewers humains attendus:** dev backend Symfony (reprise), dev front-end (JS/Twig/Alpine), Emilien Vandevelde (relecture finale)

---

## File Structure

### Nouveaux fichiers (à créer)

| Path | Responsabilité |
|---|---|
| `src/Analytics/PostHog/SiteDetector.php` | Mapping `Request::getHost()` → site enum (4 marques). **Fail-open** sur hostname non mappé (return null + log WARNING) — évite crash sur admin/api/webhooks/health hors périmètre PostHog. |
| `src/Analytics/PostHog/SiteContext.php` | Holds `site + cookie_name + cookie_domain + salt`, injecté par listener, scope request. |
| `src/Analytics/PostHog/SiteContextListener.php` | EventListener `kernel.request` priority=250, initialise SiteContext au boot de chaque requête. |
| `src/Analytics/PostHog/HashEmailService.php` | `SHA256("${SALT_MARQUE}:${email_norm}")`, salts marque-specific alignés prod attalpresident.fr. |
| `src/Analytics/PostHog/ConsentCookieHelper.php` | Read/write cookie consent scopé root-domain par marque, migration idempotente `ap_consent`. |
| `src/Analytics/PostHog/PostHogService.php` | Build super-properties + capture server-side POST vers `eu.i.posthog.com`. |
| `src/Analytics/PostHog/IngestProxyController.php` | Reverse proxy `/ingest/{path}` avec whitelist + rate-limit + sanitization headers. |
| `src/Analytics/PostHog/ConsentSettingsController.php` | POST `/parametres/confidentialite` toggle granted ↔ refused. |
| `src/Analytics/PostHog/Events/PostHogEventName.php` | Enum PHP 8.1 des ~30 events custom Renaissance web. |
| `src/Analytics/PostHog/Events/AbstractPayload.php` | Base value-object pour payloads typés. |
| `src/Analytics/PostHog/EventSubscriber/AuthEventSubscriber.php` | Symfony EventSubscriber sur `LoginSuccessEvent`, `LoginFailureEvent`, `LogoutEvent`. |
| `src/Analytics/PostHog/Twig/PostHogTwigExtension.php` | `posthog_site`, `posthog_super_properties()`, `posthog_identify_payload()`, `posthog_snippet()`. |
| `assets/analytics/posthog/posthog-init.js` | Entry point : boot SDK, apply consent state, register super-properties client-side. |
| `assets/analytics/posthog/posthog-capture.js` | Wrapper `capture(name, payload)` type-checké contre POSTHOG_EVENTS dict miroir. |
| `assets/analytics/posthog/posthog-consent.js` | Read/write cookie via `document.cookie`, bridge banner ↔ SDK. |
| `assets/analytics/posthog/posthog-consent-banner.js` | Alpine.js component (bannière déconnectés). |
| `assets/analytics/posthog/posthog-identify.js` | Applique payload identify server-generated. |
| `templates/analytics/posthog/_snippet.html.twig` | `<script>` inline server-rendered init + identify si `app.user`. |
| `templates/analytics/posthog/_consent_banner.html.twig` | Macro consent banner (Alpine.js). |
| `templates/analytics/posthog/_consent_settings_row.html.twig` | Toggle Réglages > Confidentialité. |
| `templates/renaissance/parametres/confidentialite.html.twig` | Nouvel écran Réglages > Confidentialité. |
| `config/routes/analytics.yaml` | Routes ingest proxy + confidentialite settings. |
| `config/packages/prod/posthog.yaml` | Env vars `POSTHOG_API_KEY`, `POSTHOG_HOST`, `POSTHOG_ENABLED`, `HASH_EMAIL_SALT`. |
| `docs/adrs/posthog-multi-domain.md` | ADR local espace-adherent (décisions structurantes multi-domain). |
| `docs/analytics/posthog-events-parti-renaissance.md` | Taxonomie MVP events web + mapping. |
| `.github/workflows/lint-posthog-privacy.yml` | Grep bloquant email clair côté client (PHP + Twig + JS). |
| `scripts/lint-posthog-privacy.sh` | Script bash de linting utilisé par le workflow. |
| `tests/Analytics/PostHog/*Test.php` | 7 tests PHPUnit (SiteDetector, HashEmailService, ConsentCookieHelper, IngestProxyController, PostHogService, PostHogTwigExtension, AuthEventSubscriber). |
| `features/analytics/*.feature` | 3 scenarios Behat (consent_banner, multi_domain, ingest_proxy). |

### Fichiers existants à modifier

| Path | Modification |
|---|---|
| `.env` + `.env.local.dist` + `.env.test` | +1 var `USER_VOX_HOST_REGEX` (regex documenté hors `->host()`), +5 vars `POSTHOG_*`, +2 vars `DEPLOY_SHA`/`APP_VERSION` (fallback safe). `USER_VOX_HOST` **inchangé** (28+ occurrences littérales préservées). |
| `config/routes.php` | Uniquement les vrais `->host(...)` matchers Symfony (2-3 occurrences confirmées par grep) migrés vers pattern route param `host: 'utilisateur.{marque}.fr'` + `requirements: [marque: '...\|...\|...']`. Les `defaults` `%user_vox_host%` composés, bindings `$userVoxHost`, firewall host, URL gen Twig : **NON touchés**. |
| `config/services.php` | Registration `SiteContextListener` tag `kernel.event_listener` priority=250, injection `HttpClientInterface` dans `IngestProxyController`. |
| `package.json` | +1 dep runtime `posthog-js@^1.180`. |
| `composer.json` | Aucun ajout (Symfony HttpClient + PHPUnit déjà présents). |
| `webpack.common.js` | Entry additionnelle pour bundle `posthog-init` compilé dans `bootstrap.js`. |
| `assets/bootstrap.js` | `import { initPostHog } from './analytics/posthog/posthog-init';` **après** Sentry init. |
| `templates/base_renaissance.html.twig` | Bloc `{% block analytics %}` L38-60 : ajouter `{{ include('analytics/posthog/_snippet.html.twig') }}` avant Matomo. Ajouter `{% include 'analytics/posthog/_consent_banner.html.twig' %}` en fin `<body>`. |
| `templates/renaissance/parametres/_navigation.html.twig` | +1 entrée "Confidentialité" pointant `/parametres/confidentialite`. |
| `src/Controller/Renaissance/SecurityController.php` | +wire `password_reset_requested` + `password_reset_completed` (POST controllers, pas login events firewall). |
| `src/Controller/Renaissance/Adhesion/AdhesionController.php` | +wire `adhesion_started` + `adhesion_form_submitted` + `adhesion_payment_initiated`. |
| `src/Controller/Renaissance/Adhesion/FinishController.php` | +wire `adhesion_finish_page_viewed`. |
| `src/Controller/Renaissance/Donation/DonationController.php` | +wire `donation_started` + `donation_form_submitted` + `donation_payment_initiated`. |
| `src/Controller/Renaissance/Donation/FinishController.php` | +wire `donation_completed` / `donation_payment_failed`. |
| `src/Controller/Renaissance/NationalEvent/InscriptionController.php` | +wire `national_event_page_viewed` + `national_event_inscription_submitted`. |
| Autres controllers National Event | +wire confirm / edit / payment_completed. |
| `src/Controller/Renaissance/Adherent/ProfileController.php` | +wire `profile_page_viewed` + `profile_updated`. |
| `src/Controller/Renaissance/Newsletter/SaveNewsletterController.php` | +wire `newsletter_submitted_server` avec `$set: {email}` (Cas 2). |
| `src/Controller/Renaissance/Newsletter/ConfirmNewsletterController.php` | +wire `newsletter_confirmed_server` (Cas 2). |
| `src/Controller/Renaissance/Petition/SignatureValidateController.php` | +wire `petition_signed_server` avec `$set: {email}` (Cas 2). |
| `src/Controller/MagicLinkController.php` | +wire `magic_link_requested`. |

### Fichiers explicitement NON modifiés

- Matomo custom dans `base_renaissance.html.twig` L39-60 (dual-run 4 sem).
- Aucune modification des templates/controllers d'admin (`/admin`) ou API (`/api/*` sauf newsletter).

---

## Task 0: Bootstrap branche + routing multi-host (approche route param + requirements)

**Contexte** : le repo actuel a `USER_VOX_HOST` = **1 valeur unique** dans `.env`, littéralement consommée en **28+ occurrences** (grep `%user_vox_host%`) — dont `defaults` de routes (string composé consommé par un URL matcher, pas un pattern regex), binding argument `$userVoxHost` dans `config/services.php:40,42`, URL generation dans `config/packages/twig.php:32`, firewall host requirement dans `config/packages/security.php:699`.

**❌ Anti-pattern rejeté** : remplacer `%user_vox_host%` par un regex partout casse le firewall (regex ≠ hostname littéral), l'OIDC issuer, la génération d'URL Twig et les `defaults` `app_domain`.

**✅ Approche retenue** — route param `{marque}` + requirements sur les vrais `->host()` matchers Symfony **uniquement** :

```php
->host('utilisateur.{marque}.fr')
->requirements(['marque' => 'parti-renaissance|attalpresident|avecgabrielattal|nouvellerepublique'])
```

Le `{marque}` est un placeholder Symfony natif (interpolé), `requirements` applique un match regex sur ce param seulement. C'est le pattern Symfony officiel pour du host multi-tenant.

> ⚠️ **Symfony `->host()` n'interprète PAS un paramètre string comme regex**. La bonne approche est un route param. Ne jamais tenter `->host('%user_vox_host_regex%')` avec un pattern regex dedans — Symfony le prend en littéral.

**Files:**
- Setup: `~/dev/analysis/espace-adherent`
- Modify: `.env`, `.env.local.dist`, `.env.test`, `config/routes.php` (uniquement les vrais `->host()`, PAS les `defaults` ni bindings)

- [ ] **Step 0.1 — Créer branche feat/RE-5165-posthog-web**

```bash
cd ~/dev/analysis/espace-adherent
git fetch origin master
git checkout -B feat/RE-5165-posthog-web origin/master
git status
```

Attendu : `On branch feat/RE-5165-posthog-web`, working tree clean.

- [ ] **Step 0.2 — Ajouter env vars PostHog (aucune migration `USER_VOX_HOST`)**

**IMPORTANT** : `USER_VOX_HOST` reste inchangé partout — `.env`, `.env.local.dist`, `.env.test`, `config/routes.php` (defaults), `config/services.php` (binding), `config/packages/twig.php`, `config/packages/security.php`. **Aucun remplacement littéral**.

On ajoute uniquement les nouvelles env vars PostHog + un `USER_VOX_HOST_REGEX` **séparé** pour usage documenté (paramètre Twig / futurs matchers regex explicites hors `->host()`).

Append à la fin de `.env` + `.env.local.dist` :

```dotenv

###> RE-5165 PostHog multi-marque ###
# Regex host matching multi-marque, utilisé UNIQUEMENT par des consommateurs
# qui savent parser un pattern regex (paramètre Twig documenté, futurs listeners).
# Les vrais Symfony ->host() matchers utilisent route param + requirements (cf. Task 0.3).
USER_VOX_HOST_REGEX=utilisateur\.(parti-renaissance|attalpresident|avecgabrielattal|nouvellerepublique)\.fr

# PostHog env vars (activés en Task 2)
POSTHOG_ENABLED=false
POSTHOG_API_KEY=
POSTHOG_HOST=https://eu.i.posthog.com
HASH_EMAIL_SALT_ATTAL=attalpresident-2027
HASH_EMAIL_SALT_PR=parti-renaissance-2027
HASH_EMAIL_SALT_AGA=avecgabrielattal-2027
HASH_EMAIL_SALT_NR=nouvellerepublique-2027

# Release tracking (injectés par le CI/CD au build time — fallback safe si vides)
DEPLOY_SHA=
APP_VERSION=
###< RE-5165 PostHog multi-marque ###
```

Pour `.env.test`, valeur adaptée au dev local (grep confirme `USER_VOX_HOST=test.renaissance.code` dans le repo réel) :

```dotenv

###> RE-5165 PostHog multi-marque ###
# En test, hostname local dev = test.renaissance.code (cf. .env.test existant)
USER_VOX_HOST_REGEX=(utilisateur|test)\.renaissance\.code
POSTHOG_ENABLED=false
POSTHOG_API_KEY=
POSTHOG_HOST=https://eu.i.posthog.com
HASH_EMAIL_SALT_ATTAL=attalpresident-2027
HASH_EMAIL_SALT_PR=parti-renaissance-2027
HASH_EMAIL_SALT_AGA=avecgabrielattal-2027
HASH_EMAIL_SALT_NR=nouvellerepublique-2027
DEPLOY_SHA=
APP_VERSION=
###< RE-5165 PostHog multi-marque ###
```

- [ ] **Step 0.3 — Identifier et migrer les vrais `->host()` matchers Symfony**

Grep précis pour compter les vrais matchers (par opposition aux `defaults`, bindings, URL gen) :

```bash
grep -nE "->host\(" config/routes.php config/routes/*.php 2>/dev/null
```

Attendu : probablement 2-3 occurrences seulement (à confirmer sur le repo réel avant migration). Migrer **UNIQUEMENT** celles-là vers le pattern route param + requirements :

```php
// Avant
->host('%user_vox_host%')

// Après
->host('utilisateur.{marque}.fr')
->requirements(['marque' => 'parti-renaissance|attalpresident|avecgabrielattal|nouvellerepublique'])
```

Pour dev/staging local `test.renaissance.code`, l'approche flexible qui s'adapte à tous les environnements :

```php
->host('{host_prefix}.{marque_or_test}.{tld}')
->requirements([
    'host_prefix'     => 'utilisateur|test',
    'marque_or_test'  => 'parti-renaissance|attalpresident|avecgabrielattal|nouvellerepublique|renaissance',
    'tld'             => 'fr|code',
])
```

Fontaine tranche entre approche stricte (prod-only) et flexible (multi-env) selon les patterns existants du repo.

**Fichiers explicitement NON touchés** (préservation `USER_VOX_HOST` littéral) :
- `config/routes.php` `defaults` composés (`app_domain => '%user_vox_host%|...'` etc.)
- `config/services.php` binding `$userVoxHost` (L40, L42)
- `config/packages/twig.php` URL generation (L32)
- `config/packages/security.php` firewall (L699)
- `.env`, `.env.local.dist`, `.env.test` valeurs `USER_VOX_HOST` existantes

- [ ] **Step 0.4 — Verify tests existants passent**

```bash
composer install
php bin/console cache:clear --env=test
vendor/bin/phpunit --group=routing 2>&1 | tail -10
vendor/bin/phpunit --group=security 2>&1 | tail -10
```

Attendu : 0 nouveau fail. Les fixtures Behat existantes utilisent `test.renaissance.code` (préservé) ; la plupart des tests routing existants ne hit pas les nouvelles routes analytics donc pas d'impact fixtures. Si un scénario Behat hit une des routes analytics migrées, adapter localement le host header dans le step.

- [ ] **Step 0.5 — Commit**

```bash
git add .env .env.local.dist .env.test config/routes.php
git commit -m "chore(routing): add route params multi-marque sur les ->host() matchers (RE-5165 prérequis, USER_VOX_HOST littéral préservé)"
```

---

## Task 1: Install PostHog SDK web

**Files:**
- Modify: `package.json`, `pnpm-lock.yaml`

- [ ] **Step 1.1 — Install `posthog-js`**

```bash
cd ~/dev/analysis/espace-adherent
pnpm add posthog-js@^1.180.0
```

Attendu : `posthog-js` ajouté aux `dependencies`. Pas d'erreur peer dep (React n'est pas utilisé côté espace-adherent, `posthog-js` n'a pas de dépendance React).

- [ ] **Step 1.2 — Verify version installée**

```bash
grep -A1 '"posthog-js"' package.json
```

Attendu : version résolue `^1.180.0` ou plus récente.

- [ ] **Step 1.3 — Commit**

```bash
git add package.json pnpm-lock.yaml
git commit -m "chore(deps): install posthog-js@^1.180 (SDK web PostHog Cloud EU)"
```

---

## Task 2: Configuration Symfony + env vars structurés

**Files:**
- Create: `config/packages/prod/posthog.yaml`
- Modify: `config/services.php`

- [ ] **Step 2.1 — Créer `config/packages/prod/posthog.yaml`**

```yaml
# config/packages/prod/posthog.yaml
# Configuration PostHog (Cloud EU). Paramètres injectés en DI, lecture env vars.
# Feature flag POSTHOG_ENABLED protège l'activation en prod jusqu'à validation manuelle.

parameters:
    posthog.enabled:      '%env(bool:POSTHOG_ENABLED)%'
    posthog.api_key:      '%env(POSTHOG_API_KEY)%'
    posthog.api_host:     '%env(POSTHOG_HOST)%'
    posthog.salt.attalpresident:     '%env(HASH_EMAIL_SALT_ATTAL)%'
    posthog.salt.parti_renaissance:  '%env(HASH_EMAIL_SALT_PR)%'
    posthog.salt.avecgabrielattal:   '%env(HASH_EMAIL_SALT_AGA)%'
    posthog.salt.nouvellerepublique: '%env(HASH_EMAIL_SALT_NR)%'
    # Release tracking. Fallback safe : si vars vides, PostHogService normalise
    # deploy_sha='local' et deploy_version='unknown' (cf. Task 8 buildSuperProperties).
    posthog.deploy_sha:     '%env(default::DEPLOY_SHA)%'
    posthog.deploy_version: '%env(default::APP_VERSION)%'

framework:
    # Rate limiter reverse proxy /ingest (cf. Task 6). Token bucket keyed par IP.
    rate_limiter:
        posthog_ingest:
            policy: token_bucket
            limit: 600
            rate: { interval: '1 minute', amount: 60 }
```

> **Alternative safer** (à considérer par Fontaine) : réutiliser la source `SENTRY_RELEASE` déjà passée par `Bootstrap.boot()` au lieu d'ajouter deux env vars nouvelles. Dans ce cas remplacer `posthog.deploy_sha` par `'%env(default::SENTRY_RELEASE)%'`. Vérifier le pattern existant en runtime avant de trancher.

- [ ] **Step 2.2 — Cache clear + verify**

```bash
php bin/console cache:clear --env=test
php bin/console debug:container --parameter posthog.enabled 2>&1 | head -5
```

Attendu : le paramètre est visible en DI. Valeur `false` (dev) ou string vide (test).

- [ ] **Step 2.3 — Commit**

```bash
git add config/packages/prod/posthog.yaml
git commit -m "chore(env): variables POSTHOG_* + posthog.yaml (feature flag POSTHOG_ENABLED)"
```

---

## Task 3: SiteDetector + SiteContext + SiteContextListener — TDD

**Files:**
- Create: `src/Analytics/PostHog/SiteDetector.php`
- Create: `src/Analytics/PostHog/SiteContext.php`
- Create: `src/Analytics/PostHog/SiteContextListener.php`
- Create: `tests/Analytics/PostHog/SiteDetectorTest.php`
- Modify: `config/services.php`

- [ ] **Step 3.1 — Test SiteDetector d'abord (TDD)**

```php
<?php declare(strict_types=1);

namespace Tests\App\Analytics\PostHog;

use App\Analytics\PostHog\SiteDetector;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class SiteDetectorTest extends TestCase
{
    public function testMappingKnownHostnames(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $detector = new SiteDetector($logger);

        $cases = [
            'utilisateur.parti-renaissance.fr'  => 'parti-renaissance',
            'utilisateur.attalpresident.fr'     => 'attalpresident',
            'utilisateur.avecgabrielattal.fr'   => 'avecgabrielattal',
            'utilisateur.nouvellerepublique.fr' => 'nouvellerepublique',
        ];
        foreach ($cases as $host => $expected) {
            $request = Request::create('/', 'GET', server: ['HTTP_HOST' => $host]);
            $this->assertSame($expected, $detector->detectFromRequest($request), "Host: $host");
        }
    }

    public function testUnmappedHostnameReturnsNull(): void
    {
        // Fail-open : hostname hors périmètre PostHog Renaissance (admin/api/webhooks/health)
        // → return null + log WARNING. AUCUN throw (éviterait de crasher tout le site).
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('hors périmètre PostHog'));

        $detector = new SiteDetector($logger);
        $request = Request::create('/', 'GET', server: ['HTTP_HOST' => 'admin.attalpresident.fr']);

        $this->assertNull($detector->detectFromRequest($request));
    }

    public function testCookieConfigByMarque(): void
    {
        $this->assertSame(
            ['name' => 'ap_consent', 'domain' => '.attalpresident.fr'],
            SiteDetector::getCookieConfig('attalpresident'),
        );
        $this->assertSame(
            ['name' => 'pr_consent', 'domain' => '.parti-renaissance.fr'],
            SiteDetector::getCookieConfig('parti-renaissance'),
        );
        $this->assertSame(
            ['name' => 'aga_consent', 'domain' => '.avecgabrielattal.fr'],
            SiteDetector::getCookieConfig('avecgabrielattal'),
        );
        $this->assertSame(
            ['name' => 'nr_consent', 'domain' => '.nouvellerepublique.fr'],
            SiteDetector::getCookieConfig('nouvellerepublique'),
        );
    }
}
```

- [ ] **Step 3.2 — Verify test fails**

```bash
vendor/bin/phpunit tests/Analytics/PostHog/SiteDetectorTest.php 2>&1 | tail -5
```

Attendu : FAIL "Class App\Analytics\PostHog\SiteDetector does not exist".

- [ ] **Step 3.3 — Implémenter `SiteDetector.php`**

```php
<?php declare(strict_types=1);

namespace App\Analytics\PostHog;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Rôle : détecter la marque Renaissance depuis Request::getHost() → site enum.
 *
 * 4 marques mappées explicitement (nomenclature §2 crm-integrations).
 *
 * FAIL-OPEN sur hostname non mappé : return null + log WARNING.
 * L'app sert plusieurs kernels/hosts dans le même process — admin, api,
 * webhooks, health, national_event, app_renaissance_host... Un throw ici
 * casserait tout le site. PostHog n'est qu'un service analytics accessoire ;
 * un hostname non-mappé n'est PAS une anomalie mais un cas nominal.
 *
 * Le fail-open est safe car SiteContextListener détecte null et return early,
 * empêchant l'injection PostHog dans les périmètres hors Renaissance.
 *
 * Cf. spec espace-adherent §2.1 + ADR local posthog-multi-domain.
 */
final class SiteDetector
{
    private const HOSTNAME_SITE_MAP = [
        'utilisateur.parti-renaissance.fr'  => 'parti-renaissance',
        'utilisateur.attalpresident.fr'     => 'attalpresident',
        'utilisateur.avecgabrielattal.fr'   => 'avecgabrielattal',
        'utilisateur.nouvellerepublique.fr' => 'nouvellerepublique',
    ];

    private const COOKIE_CONFIG_BY_SITE = [
        'attalpresident'      => ['name' => 'ap_consent',  'domain' => '.attalpresident.fr'],
        'parti-renaissance'   => ['name' => 'pr_consent',  'domain' => '.parti-renaissance.fr'],
        'avecgabrielattal'    => ['name' => 'aga_consent', 'domain' => '.avecgabrielattal.fr'],
        'nouvellerepublique'  => ['name' => 'nr_consent',  'domain' => '.nouvellerepublique.fr'],
    ];

    public function __construct(private readonly LoggerInterface $logger) {}

    public function detectFromRequest(Request $request): ?string
    {
        $host = strtolower($request->getHost());
        if (isset(self::HOSTNAME_SITE_MAP[$host])) {
            return self::HOSTNAME_SITE_MAP[$host];
        }
        // Hostname non mappé : n'est pas dans notre périmètre PostHog Renaissance.
        // Log WARNING (pas CRITICAL) : c'est un cas normal pour admin/api/webhooks/health.
        $this->logger->warning(
            'PostHog SiteDetector: hostname hors périmètre PostHog',
            ['hostname' => $host],
        );
        return null;
    }

    /** @return array{name: string, domain: string} */
    public static function getCookieConfig(string $site): array
    {
        return self::COOKIE_CONFIG_BY_SITE[$site]
            ?? throw new \InvalidArgumentException("Unknown site: $site");
    }
}
```

- [ ] **Step 3.4 — Verify test pass**

```bash
vendor/bin/phpunit tests/Analytics/PostHog/SiteDetectorTest.php 2>&1 | tail -3
```

Attendu : 3 tests PASS.

- [ ] **Step 3.5 — Implémenter `SiteContext.php`**

```php
<?php declare(strict_types=1);

namespace App\Analytics\PostHog;

/**
 * Rôle : holds le site détecté pour la durée d'une requête HTTP.
 * Injecté par SiteContextListener (kernel.request priority=250).
 * Consommé par HashEmailService, ConsentCookieHelper, PostHogTwigExtension.
 */
final class SiteContext
{
    private ?string $site = null;

    public function setSite(string $site): void
    {
        $this->site = $site;
    }

    public function getSite(): string
    {
        return $this->site ?? throw new \LogicException(
            'SiteContext::getSite() called before SiteContextListener initialized it. '
            . 'This is likely a bug in the listener registration or priority.',
        );
    }

    public function isInitialized(): bool
    {
        return null !== $this->site;
    }

    /** @return array{name: string, domain: string} */
    public function getCookieConfig(): array
    {
        return SiteDetector::getCookieConfig($this->getSite());
    }
}
```

- [ ] **Step 3.6 — Implémenter `SiteContextListener.php`**

```php
<?php declare(strict_types=1);

namespace App\Analytics\PostHog;

use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Rôle : EventListener kernel.request priority=250.
 * Initialise SiteContext au boot de chaque requête.
 * Registered via config/services.php tag kernel.event_listener.
 *
 * FAIL-OPEN : si le hostname est hors périmètre PostHog Renaissance
 * (admin/api/webhooks/health/national_event...), SiteDetector retourne null
 * et le listener return early — SiteContext reste non-initialisé.
 * Les consommateurs (PostHogTwigExtension, ConsentSettingsController...) doivent
 * checker SiteContext::isInitialized() avant d'utiliser le context.
 */
final class SiteContextListener
{
    public function __construct(
        private readonly SiteDetector $detector,
        private readonly SiteContext $context,
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $site = $this->detector->detectFromRequest($event->getRequest());
        if (null === $site) {
            return; // hostname hors périmètre PostHog Renaissance
        }
        $this->context->setSite($site);
    }
}
```

- [ ] **Step 3.7 — Registration DI dans `config/services.php`**

Append à la section services :

```php
$services->set(App\Analytics\PostHog\SiteDetector::class);
$services->set(App\Analytics\PostHog\SiteContext::class);
$services->set(App\Analytics\PostHog\SiteContextListener::class)
    ->tag('kernel.event_listener', [
        'event' => 'kernel.request',
        'method' => 'onKernelRequest',
        'priority' => 250,
    ]);
```

- [ ] **Step 3.8 — Verify service registered + cache clear**

```bash
php bin/console cache:clear --env=test
php bin/console debug:event-dispatcher kernel.request 2>&1 | grep -i posthog
```

Attendu : `SiteContextListener::onKernelRequest` listé priority=250.

- [ ] **Step 3.9 — Commit**

```bash
git add src/Analytics/PostHog/SiteDetector.php src/Analytics/PostHog/SiteContext.php src/Analytics/PostHog/SiteContextListener.php tests/Analytics/PostHog/SiteDetectorTest.php config/services.php
git commit -m "feat(analytics): SiteDetector + SiteContext + Listener (fail-closed multi-marque)"
```

---

## Task 4: HashEmailService (salts marque-specific) — TDD

**Files:**
- Create: `src/Analytics/PostHog/HashEmailService.php`
- Create: `tests/Analytics/PostHog/HashEmailServiceTest.php`
- Modify: `config/services.php`

- [ ] **Step 4.1 — Test avec les 4 hashes snapshots**

Ces hashes sont **byte-identiques** à ceux produits par `attalpresident.fr/lib/posthog/identity.ts` L22-38 (validé par calcul Python) :

```php
<?php declare(strict_types=1);

namespace Tests\App\Analytics\PostHog;

use App\Analytics\PostHog\HashEmailService;
use App\Analytics\PostHog\SiteContext;
use PHPUnit\Framework\TestCase;

class HashEmailServiceTest extends TestCase
{
    public function testKnownHashPerSite(): void
    {
        $email = 'test@example.com';
        $expected = [
            'attalpresident'      => 'c8be02d2a41f9f84e80335c10ba29dddc09d94645cfbecf81a88161d86a3eda0',
            'parti-renaissance'   => 'f1bfce1212e9adc7c7e789acc6727ef278c48618c2fb3b99580fde3c891b87ea',
            'avecgabrielattal'    => 'ebc164cb050861a4297a5e658fbfabeb3e051770bcd659ea452ec80793ee8a9d',
            'nouvellerepublique'  => '1d07c9a32f4cf1a8d2542334ec6dc7fabedf9b69487969e9c4f9909bd98ad4f1',
        ];
        $salts = [
            'attalpresident'      => 'attalpresident-2027',
            'parti-renaissance'   => 'parti-renaissance-2027',
            'avecgabrielattal'    => 'avecgabrielattal-2027',
            'nouvellerepublique'  => 'nouvellerepublique-2027',
        ];
        foreach ($expected as $site => $hash) {
            $ctx = new SiteContext();
            $ctx->setSite($site);
            $service = new HashEmailService($ctx, $salts);
            $this->assertSame($hash, $service->hash($email), "Site: $site");
        }
    }

    public function testNormalizeTrimLowercase(): void
    {
        $ctx = new SiteContext();
        $ctx->setSite('attalpresident');
        $service = new HashEmailService($ctx, ['attalpresident' => 'attalpresident-2027']);

        // "  Test@Example.COM " (trim + lowercase) → hash identique à testKnownHashPerSite
        $this->assertSame(
            'c8be02d2a41f9f84e80335c10ba29dddc09d94645cfbecf81a88161d86a3eda0',
            $service->hash('  Test@Example.COM '),
        );
    }

    public function testEmptyEmailThrows(): void
    {
        // Un email vide ou whitespace-only hashé silencieusement serait un bug caché :
        // toutes les identités anonymes tomberaient sur le même hash déterministe.
        $ctx = new SiteContext(); $ctx->setSite('attalpresident');
        $service = new HashEmailService($ctx, ['attalpresident' => 'attalpresident-2027']);
        $this->expectException(\InvalidArgumentException::class);
        $service->hash('   '); // whitespace-only
    }
}
```

- [ ] **Step 4.2 — Verify fail**

```bash
vendor/bin/phpunit tests/Analytics/PostHog/HashEmailServiceTest.php 2>&1 | tail -5
```

Attendu : FAIL (module inexistant).

- [ ] **Step 4.3 — Implémenter `HashEmailService.php`**

```php
<?php declare(strict_types=1);

namespace App\Analytics\PostHog;

/**
 * Rôle : hash SHA256(salt:email_norm) avec salt marque-specific.
 *
 * Format : SHA256("${SALT}:${email_norm}") — SALT devant, ':' séparateur,
 * email_norm = trim().toLowerCase() (compat UTF-8).
 *
 * Aligné byte-identique sur attalpresident.fr/lib/posthog/identity.ts L22-38.
 * Divergence intentionnelle vs doctrine cross-sites §4.2 (salt global) —
 * cf. ADR local posthog-multi-domain, spec §2.4.
 *
 * Cross-brand PostHog UI : 2 persons distinctes web/marque pour un même
 * adhérent — réconciliation via view BQ posthog_identity_bridge (mart Renaissance).
 */
final class HashEmailService
{
    /**
     * @param array<string, string> $saltsBySite Map site → salt marque-specific
     */
    public function __construct(
        private readonly SiteContext $context,
        private readonly array $saltsBySite,
    ) {}

    public function hash(string $email): string
    {
        $normalized = $this->normalize($email);
        if ('' === $normalized) {
            throw new \InvalidArgumentException('HashEmailService: email vide ou whitespace-only');
        }
        $site = $this->context->getSite();
        $salt = $this->saltsBySite[$site]
            ?? throw new \RuntimeException("HashEmailService: no salt configured for site '$site'");
        return hash('sha256', $salt . ':' . $normalized);
    }

    private function normalize(string $email): string
    {
        return strtolower(trim($email));
    }
}
```

- [ ] **Step 4.4 — Registration DI dans `config/services.php`**

```php
$services->set(App\Analytics\PostHog\HashEmailService::class)
    ->arg('$saltsBySite', [
        'attalpresident'      => '%posthog.salt.attalpresident%',
        'parti-renaissance'   => '%posthog.salt.parti_renaissance%',
        'avecgabrielattal'    => '%posthog.salt.avecgabrielattal%',
        'nouvellerepublique'  => '%posthog.salt.nouvellerepublique%',
    ]);
```

- [ ] **Step 4.5 — Verify tests pass**

```bash
vendor/bin/phpunit tests/Analytics/PostHog/HashEmailServiceTest.php 2>&1 | tail -3
```

Attendu : 2 tests PASS.

- [ ] **Step 4.6 — Commit**

```bash
git add src/Analytics/PostHog/HashEmailService.php tests/Analytics/PostHog/HashEmailServiceTest.php config/services.php
git commit -m "feat(analytics): HashEmailService (salts marque-specific, snapshot byte-identique attalpresident.fr)"
```

---

## Task 5: ConsentCookieHelper (migration idempotente ap_consent) — TDD

**Files:**
- Create: `src/Analytics/PostHog/ConsentCookieHelper.php`
- Create: `tests/Analytics/PostHog/ConsentCookieHelperTest.php`
- Modify: `config/services.php`

- [ ] **Step 5.1 — Test**

```php
<?php declare(strict_types=1);

namespace Tests\App\Analytics\PostHog;

use App\Analytics\PostHog\ConsentCookieHelper;
use App\Analytics\PostHog\SiteContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

class ConsentCookieHelperTest extends TestCase
{
    public function testReadReturnsNullWhenCookieAbsent(): void
    {
        $ctx = new SiteContext(); $ctx->setSite('attalpresident');
        $helper = new ConsentCookieHelper($ctx);
        $request = Request::create('/');
        $this->assertNull($helper->read($request));
    }

    public function testReadReturnsTrueForCookieValue1(): void
    {
        $ctx = new SiteContext(); $ctx->setSite('attalpresident');
        $helper = new ConsentCookieHelper($ctx);
        $request = Request::create('/', 'GET', cookies: ['ap_consent' => '1']);
        $this->assertTrue($helper->read($request));
    }

    public function testReadReturnsFalseForCookieValue0(): void
    {
        $ctx = new SiteContext(); $ctx->setSite('parti-renaissance');
        $helper = new ConsentCookieHelper($ctx);
        $request = Request::create('/', 'GET', cookies: ['pr_consent' => '0']);
        $this->assertFalse($helper->read($request));
    }

    public function testWriteProducesCookieWithScopedDomain(): void
    {
        $ctx = new SiteContext(); $ctx->setSite('attalpresident');
        $helper = new ConsentCookieHelper($ctx);
        $cookie = $helper->write(true);

        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertSame('ap_consent', $cookie->getName());
        $this->assertSame('1', $cookie->getValue());
        $this->assertSame('.attalpresident.fr', $cookie->getDomain());
        $this->assertSame('/', $cookie->getPath());
        $this->assertTrue($cookie->isSecure());
        $this->assertFalse($cookie->isHttpOnly());
        $this->assertSame(Cookie::SAMESITE_LAX, $cookie->getSameSite());
    }

    public function testWriteFalseSetsValue0(): void
    {
        $ctx = new SiteContext(); $ctx->setSite('avecgabrielattal');
        $helper = new ConsentCookieHelper($ctx);
        $cookie = $helper->write(false);

        $this->assertSame('aga_consent', $cookie->getName());
        $this->assertSame('0', $cookie->getValue());
        $this->assertSame('.avecgabrielattal.fr', $cookie->getDomain());
    }
}
```

- [ ] **Step 5.2 — Verify fail**

```bash
vendor/bin/phpunit tests/Analytics/PostHog/ConsentCookieHelperTest.php 2>&1 | tail -5
```

Attendu : FAIL.

- [ ] **Step 5.3 — Implémenter `ConsentCookieHelper.php`**

```php
<?php declare(strict_types=1);

namespace App\Analytics\PostHog;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

/**
 * Rôle : lecture/écriture du cookie consent scopé root-domain par marque.
 *
 * Nommage :
 *   .parti-renaissance.fr   → pr_consent
 *   .attalpresident.fr      → ap_consent (déjà en prod attalpresident.fr, migration idempotente)
 *   .avecgabrielattal.fr    → aga_consent
 *   .nouvellerepublique.fr  → nr_consent
 *
 * Valeurs : '1' = granted, '0' = refused, absent = undefined.
 * Attributes : Secure, SameSite=Lax, HttpOnly=false (JS doit lire pour piloter SDK PostHog).
 *
 * Cf. spec §5.
 */
final class ConsentCookieHelper
{
    private const MAX_AGE_SECONDS = 34_128_000; // ~13 mois (CNIL max)

    public function __construct(private readonly SiteContext $context) {}

    public function read(Request $request): ?bool
    {
        $config = $this->context->getCookieConfig();
        $raw = $request->cookies->get($config['name']);
        return match ($raw) {
            '1'     => true,
            '0'     => false,
            default => null,
        };
    }

    public function write(bool $granted): Cookie
    {
        $config = $this->context->getCookieConfig();
        return Cookie::create(
            name: $config['name'],
            value: $granted ? '1' : '0',
            expire: time() + self::MAX_AGE_SECONDS,
            path: '/',
            domain: $config['domain'],
            secure: true,
            httpOnly: false,
            sameSite: Cookie::SAMESITE_LAX,
        );
    }
}
```

- [ ] **Step 5.4 — Verify tests pass**

```bash
vendor/bin/phpunit tests/Analytics/PostHog/ConsentCookieHelperTest.php 2>&1 | tail -3
```

Attendu : 5 tests PASS.

- [ ] **Step 5.5 — Commit**

```bash
git add src/Analytics/PostHog/ConsentCookieHelper.php tests/Analytics/PostHog/ConsentCookieHelperTest.php
git commit -m "feat(analytics): ConsentCookieHelper (4 cookies scopés root-domain, migration idempotente ap_consent)"
```

---

## Task 6: IngestProxyController (reverse proxy PostHog) — TDD

**Files:**
- Create: `src/Analytics/PostHog/IngestProxyController.php`
- Create: `tests/Analytics/PostHog/IngestProxyControllerTest.php`
- Create: `config/routes/analytics.yaml`

- [ ] **Step 6.1 — Test**

```php
<?php declare(strict_types=1);

namespace Tests\App\Analytics\PostHog;

use App\Analytics\PostHog\IngestProxyController;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class IngestProxyControllerTest extends WebTestCase
{
    private function alwaysAcceptLimiter(): RateLimiterFactory
    {
        $limiter = $this->createStub(LimiterInterface::class);
        $limiter->method('consume')->willReturn(
            new RateLimit(available: 1000, retryAfter: new \DateTimeImmutable(), accepted: true, limit: 1000),
        );
        $factory = $this->createStub(RateLimiterFactory::class);
        $factory->method('create')->willReturn($limiter);
        return $factory;
    }

    public function testForwardEventEndpoint(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('{"status":"ok"}', ['http_code' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);
        $logger = $this->createMock(LoggerInterface::class);
        $controller = new IngestProxyController($mockClient, 'https://eu.i.posthog.com', $logger, $this->alwaysAcceptLimiter());
        $request = Request::create('/ingest/e/', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: '{"event":"test"}');

        $response = $controller->__invoke('e/', $request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('"status":"ok"', $response->getContent());
    }

    public function testTimeoutReturns504(): void
    {
        $mockClient = new MockHttpClient(function () {
            throw new \Symfony\Component\HttpClient\Exception\TransportException('timeout');
        });
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('PostHog proxy'));
        $controller = new IngestProxyController($mockClient, 'https://eu.i.posthog.com', $logger, $this->alwaysAcceptLimiter());
        $request = Request::create('/ingest/e/', 'POST');

        $response = $controller->__invoke('e/', $request);

        $this->assertSame(504, $response->getStatusCode());
    }

    public function testSanitizesCookieHeader(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 200, 'response_headers' => [
                'set-cookie' => 'ph_session=abc; Path=/',
                'content-type' => 'application/json',
            ]]),
        ]);
        $logger = $this->createMock(LoggerInterface::class);
        $controller = new IngestProxyController($mockClient, 'https://eu.i.posthog.com', $logger, $this->alwaysAcceptLimiter());
        $request = Request::create('/ingest/decide/', 'POST');

        $response = $controller->__invoke('decide/', $request);

        $this->assertEmpty($response->headers->getCookies());
        $this->assertNull($response->headers->get('set-cookie'));
    }

    public function testPreservesContentEncodingHeader(): void
    {
        // Content-Encoding upstream doit être préservé downstream, sinon le browser
        // essaie de parser du gzip comme du plain text → payload corrompu client-side.
        $mockClient = new MockHttpClient([
            new MockResponse('{"gzipped":"payload"}', ['http_code' => 200, 'response_headers' => [
                'content-encoding' => 'gzip',
                'content-type'     => 'application/json',
            ]]),
        ]);
        $logger = $this->createMock(LoggerInterface::class);
        $controller = new IngestProxyController($mockClient, 'https://eu.i.posthog.com', $logger, $this->alwaysAcceptLimiter());
        $request = Request::create('/ingest/e/', 'POST');

        $response = $controller->__invoke('e/', $request);

        $this->assertSame('gzip', $response->headers->get('content-encoding'));
    }

    public function testRateLimitBlocksExcess(): void
    {
        // 601e requête/min → 429 avec Retry-After.
        $limiter = $this->createStub(LimiterInterface::class);
        $limiter->method('consume')->willReturn(
            new RateLimit(available: 0, retryAfter: new \DateTimeImmutable('+60 seconds'), accepted: false, limit: 600),
        );
        $factory = $this->createStub(RateLimiterFactory::class);
        $factory->method('create')->willReturn($limiter);

        $mockClient = new MockHttpClient(function () {
            $this->fail('httpClient must NOT be called when rate limit rejects');
        });
        $logger = $this->createMock(LoggerInterface::class);
        $controller = new IngestProxyController($mockClient, 'https://eu.i.posthog.com', $logger, $factory);
        $request = Request::create('/ingest/e/', 'POST');

        $response = $controller->__invoke('e/', $request);

        $this->assertSame(429, $response->getStatusCode());
        $this->assertSame('60', $response->headers->get('Retry-After'));
    }
}
```

- [ ] **Step 6.2 — Verify fail**

```bash
vendor/bin/phpunit tests/Analytics/PostHog/IngestProxyControllerTest.php 2>&1 | tail -5
```

Attendu : FAIL.

- [ ] **Step 6.3 — Implémenter `IngestProxyController.php`**

```php
<?php declare(strict_types=1);

namespace App\Analytics\PostHog;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Reverse proxy /ingest/{path} → eu.i.posthog.com/{path}.
 *
 * - Contourne Safari ITP / Firefox ETP : PostHog reçu en first-party sur le domain marque.
 * - Whitelist paths : e|decide|s|static|batch|array|flags|surveys|warehouse (v1.180+).
 * - Sanitize headers upstream (jamais forward Cookie/Auth), sanitize downstream (drop Set-Cookie).
 * - Timeout 5s → 504 sur PostHog EU lent.
 * - Rate limit token_bucket 600 burst / 60 refill par IP (config framework, cf. Task 2).
 *
 * Cf. spec §6.
 */
final class IngestProxyController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%posthog.api_host%')]
        private readonly string $apiHost,
        private readonly LoggerInterface $logger,
        #[Autowire(service: 'limiter.posthog_ingest')]
        private readonly RateLimiterFactory $rateLimiter,
    ) {}

    public function __invoke(string $path, Request $request): Response
    {
        // Rate limit keyed par IP client. 601e requête/min → 429.
        $limiter = $this->rateLimiter->create($request->getClientIp() ?? 'unknown');
        if (!$limiter->consume(1)->isAccepted()) {
            return new Response('', 429, ['Retry-After' => '60']);
        }

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
                    'max_redirects' => 0,
                ],
            );
            $content = $upstream->getContent(throw: false);
            $status  = $upstream->getStatusCode();
            $headers = $this->sanitizeResponseHeaders($upstream->getHeaders(throw: false));
        } catch (TransportException $e) {
            $this->logger->warning('PostHog proxy transport error', ['exception' => $e->getMessage(), 'path' => $path]);
            return new Response('', 504);
        }

        return new Response($content, $status, $headers);
    }

    /** @return array<string, string> */
    private function forwardableHeaders(Request $request): array
    {
        return [
            'User-Agent'      => $request->headers->get('User-Agent', ''),
            'Content-Type'    => $request->headers->get('Content-Type', 'application/json'),
            'Accept'          => $request->headers->get('Accept', '*/*'),
            'Accept-Encoding' => $request->headers->get('Accept-Encoding', 'gzip'),
        ];
    }

    /** @param array<string, list<string>> $headers */
    private function sanitizeResponseHeaders(array $headers): array
    {
        // Drop uniquement : Set-Cookie (jamais utile), Transfer-Encoding (Symfony gère).
        // GARDER Content-Encoding : le browser doit décompresser correctement le gzip
        // upstream, sinon le payload arrive corrompu côté client.
        unset(
            $headers['set-cookie'],
            $headers['x-posthog-set-cookie'],
            $headers['posthog-session-cookie'],
            $headers['transfer-encoding'],
        );
        return $headers;
    }
}
```

- [ ] **Step 6.4 — Créer `config/routes/analytics.yaml`**

```yaml
# config/routes/analytics.yaml
# Approche route param + requirements (Symfony natif, cf. Task 0.3).
# Le host {marque} est un placeholder ; requirements applique le regex sur ce param.
app_posthog_ingest_proxy:
    path: /ingest/{path}
    controller: App\Analytics\PostHog\IngestProxyController
    requirements:
        path: '^(e|decide|s|static|batch|array|flags|surveys|warehouse)(/.*)?$'
        marque: 'parti-renaissance|attalpresident|avecgabrielattal|nouvellerepublique'
    methods: [GET, POST, OPTIONS]
    host: 'utilisateur.{marque}.fr'
```

- [ ] **Step 6.5 — Verify tests pass**

```bash
vendor/bin/phpunit tests/Analytics/PostHog/IngestProxyControllerTest.php 2>&1 | tail -3
```

Attendu : 5 tests PASS (forward + timeout + cookie sanitize + content-encoding + rate limit).

- [ ] **Step 6.6 — Commit**

```bash
git add src/Analytics/PostHog/IngestProxyController.php tests/Analytics/PostHog/IngestProxyControllerTest.php config/routes/analytics.yaml
git commit -m "feat(analytics): IngestProxyController (whitelist paths PostHog v1.180+, sanitize headers, 504 défensif)"
```

---

## Task 7: PostHogEventName enum + payloads value-objects — TDD

**Files:**
- Create: `src/Analytics/PostHog/Events/PostHogEventName.php`
- Create: `src/Analytics/PostHog/Events/AbstractPayload.php`
- Create: `tests/Analytics/PostHog/Events/PostHogEventNameTest.php`

- [ ] **Step 7.1 — Test count events**

```php
<?php declare(strict_types=1);

namespace Tests\App\Analytics\PostHog\Events;

use App\Analytics\PostHog\Events\PostHogEventName;
use PHPUnit\Framework\TestCase;

class PostHogEventNameTest extends TestCase
{
    public function testCountEventsMatchesSpec(): void
    {
        // Spec §9.1 : 30 events custom Renaissance web
        $this->assertCount(30, PostHogEventName::cases());
    }

    public function testValueUsesSnakeCase(): void
    {
        foreach (PostHogEventName::cases() as $case) {
            $this->assertMatchesRegularExpression(
                '/^[a-z][a-z0-9_]+$/',
                $case->value,
                "Event {$case->name} must be snake_case",
            );
        }
    }

    public function testConsentGrantedValue(): void
    {
        $this->assertSame('consent_granted', PostHogEventName::CONSENT_GRANTED->value);
    }
}
```

- [ ] **Step 7.2 — Verify fail**

```bash
vendor/bin/phpunit tests/Analytics/PostHog/Events/PostHogEventNameTest.php 2>&1 | tail -5
```

- [ ] **Step 7.3 — Implémenter `PostHogEventName.php`**

```php
<?php declare(strict_types=1);

namespace App\Analytics\PostHog\Events;

/**
 * Enum unique de tous les events PostHog custom Renaissance web.
 * 30 events Phase 1. Cohérent avec docs/analytics/posthog-events-parti-renaissance.md.
 * Miroir JS dans assets/analytics/posthog/posthog-capture.js (POSTHOG_EVENTS dict).
 *
 * Cf. spec §9.1.
 */
enum PostHogEventName: string
{
    // Consent
    case CONSENT_GRANTED   = 'consent_granted';
    case CONSENT_REFUSED   = 'consent_refused';
    case CONSENT_DISMISSED = 'consent_dismissed';

    // Auth
    case LOGIN_SUCCEEDED             = 'login_succeeded';
    case LOGIN_FAILED                = 'login_failed';
    case LOGOUT_COMPLETED            = 'logout_completed';
    case PASSWORD_RESET_REQUESTED    = 'password_reset_requested';
    case PASSWORD_RESET_COMPLETED    = 'password_reset_completed';
    case MAGIC_LINK_REQUESTED        = 'magic_link_requested';
    case MAGIC_LINK_LOGIN_SUCCEEDED  = 'magic_link_login_succeeded';

    // Adhésion
    case ADHESION_STARTED           = 'adhesion_started';
    case ADHESION_FORM_SUBMITTED    = 'adhesion_form_submitted';
    case ADHESION_PAYMENT_INITIATED = 'adhesion_payment_initiated';
    case ADHESION_COMPLETED         = 'adhesion_completed';
    case ADHESION_PAYMENT_FAILED    = 'adhesion_payment_failed';
    case ADHESION_FINISH_PAGE_VIEWED = 'adhesion_finish_page_viewed';

    // Don
    case DONATION_STARTED           = 'donation_started';
    case DONATION_FORM_SUBMITTED    = 'donation_form_submitted';
    case DONATION_PAYMENT_INITIATED = 'donation_payment_initiated';
    case DONATION_COMPLETED         = 'donation_completed';
    case DONATION_PAYMENT_FAILED    = 'donation_payment_failed';

    // Meeting national
    case NATIONAL_EVENT_PAGE_VIEWED             = 'national_event_page_viewed';
    case NATIONAL_EVENT_INSCRIPTION_SUBMITTED   = 'national_event_inscription_submitted';
    case NATIONAL_EVENT_INSCRIPTION_CONFIRMED   = 'national_event_inscription_confirmed';
    case NATIONAL_EVENT_PAYMENT_COMPLETED       = 'national_event_payment_completed';
    case NATIONAL_EVENT_INSCRIPTION_EDITED      = 'national_event_inscription_edited';

    // Profil
    case PROFILE_PAGE_VIEWED    = 'profile_page_viewed';
    case PROFILE_UPDATED        = 'profile_updated';

    // Newsletter (Cas 2 server-side)
    case NEWSLETTER_SUBMITTED_SERVER = 'newsletter_submitted_server';
    case NEWSLETTER_CONFIRMED_SERVER = 'newsletter_confirmed_server';

    // Pétition (Cas 2 server-side)
    case PETITION_SIGNED_SERVER = 'petition_signed_server';
}
```

- [ ] **Step 7.4 — Implémenter `AbstractPayload.php`**

```php
<?php declare(strict_types=1);

namespace App\Analytics\PostHog\Events;

/**
 * Base class pour les payloads value-objects typés.
 * Chaque payload event porte ses propriétés typées + une méthode toArray()
 * qui sérialise pour PostHog capture.
 */
abstract class AbstractPayload
{
    /** @return array<string, mixed> */
    abstract public function toArray(): array;
}
```

- [ ] **Step 7.5 — Verify tests pass**

```bash
vendor/bin/phpunit tests/Analytics/PostHog/Events/ 2>&1 | tail -3
```

Attendu : 3 tests PASS.

- [ ] **Step 7.6 — Commit**

```bash
git add src/Analytics/PostHog/Events/ tests/Analytics/PostHog/Events/
git commit -m "feat(analytics): PostHogEventName enum (30 events Renaissance web) + AbstractPayload"
```

---

## Task 8: PostHogService (capture server-side + super-properties) — TDD

**Files:**
- Create: `src/Analytics/PostHog/PostHogService.php`
- Create: `tests/Analytics/PostHog/PostHogServiceTest.php`
- Modify: `config/services.php`

- [ ] **Step 8.1 — Test**

```php
<?php declare(strict_types=1);

namespace Tests\App\Analytics\PostHog;

use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\HashEmailService;
use App\Analytics\PostHog\PostHogService;
use App\Analytics\PostHog\SiteContext;
use App\Entity\Adherent;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class PostHogServiceTest extends TestCase
{
    public function testBuildSuperPropertiesReturnsExpectedKeys(): void
    {
        $ctx = new SiteContext(); $ctx->setSite('parti-renaissance');
        $hash = $this->createStub(HashEmailService::class);
        $logger = $this->createStub(LoggerInterface::class);
        $service = new PostHogService(
            httpClient: new MockHttpClient(),
            apiHost: 'https://eu.i.posthog.com',
            apiKey: 'phc_test',
            enabled: true,
            context: $ctx,
            hashEmail: $hash,
            environment: 'test',
            deploySha: 'abc1234',
            deployVersion: '1.0.0',
            logger: $logger,
        );

        $props = $service->buildSuperProperties();

        $expected = ['site', 'platform', 'environment', 'deploy_sha', 'deploy_version', 'locale', 'is_bot'];
        foreach ($expected as $key) {
            $this->assertArrayHasKey($key, $props, "Missing super-property: $key");
        }
        $this->assertSame('parti-renaissance', $props['site']);
        $this->assertSame('test', $props['environment']);
    }

    public function testBuildSuperPropertiesFallbackOnEmptyEnvVars(): void
    {
        // DEPLOY_SHA / APP_VERSION vides (cas dev local, staging sans CI/CD) → fallback safe.
        $ctx = new SiteContext(); $ctx->setSite('parti-renaissance');
        $hash = $this->createStub(HashEmailService::class);
        $logger = $this->createStub(LoggerInterface::class);
        $service = new PostHogService(
            new MockHttpClient(), 'https://eu.i.posthog.com', 'phc_test', true,
            $ctx, $hash, 'dev', deploySha: '', deployVersion: '', logger: $logger,
        );

        $props = $service->buildSuperProperties();
        $this->assertSame('local', $props['deploy_sha']);
        $this->assertSame('unknown', $props['deploy_version']);
    }

    public function testCaptureServerSideSkipsWhenDisabled(): void
    {
        $mockClient = new MockHttpClient(function () {
            $this->fail('httpClient should not be called when POSTHOG_ENABLED=false');
        });
        $ctx = new SiteContext(); $ctx->setSite('attalpresident');
        $hash = $this->createStub(HashEmailService::class);
        $logger = $this->createStub(LoggerInterface::class);
        $service = new PostHogService(
            $mockClient, 'https://eu.i.posthog.com', 'phc_test', enabled: false,
            context: $ctx, hashEmail: $hash, environment: 'test',
            deploySha: 'abc', deployVersion: '1.0.0', logger: $logger,
        );

        $service->captureServerSide(PostHogEventName::LOGIN_SUCCEEDED, ['method' => 'form']);
        // Pas d'exception, pas d'appel HTTP → OK
        $this->assertTrue(true);
    }

    public function testCaptureServerSidePostsToCorrectEndpoint(): void
    {
        $capturedUrl = null;
        $capturedBody = null;
        $mockClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$capturedUrl, &$capturedBody) {
            $capturedUrl = $url;
            $capturedBody = $options['body'] ?? '';
            return new MockResponse('', ['http_code' => 200]);
        });
        $ctx = new SiteContext(); $ctx->setSite('parti-renaissance');
        $hash = $this->createStub(HashEmailService::class);
        $logger = $this->createStub(LoggerInterface::class);
        $service = new PostHogService(
            $mockClient, 'https://eu.i.posthog.com', 'phc_test', enabled: true,
            context: $ctx, hashEmail: $hash, environment: 'test',
            deploySha: 'abc', deployVersion: '1.0.0', logger: $logger,
        );

        $service->captureServerSide(PostHogEventName::LOGIN_SUCCEEDED, ['method' => 'form']);

        $this->assertStringContainsString('/capture/', $capturedUrl);
        $decoded = json_decode($capturedBody, true);
        $this->assertSame('login_succeeded', $decoded['event']);
        $this->assertSame('phc_test', $decoded['api_key']);
        $this->assertSame('form', $decoded['properties']['method']);
        $this->assertSame('parti-renaissance', $decoded['properties']['site']);
    }

    public function testCaptureServerSideWithSetIncludesSetProperty(): void
    {
        // Test dédié Cas 2 (spec §8.6-7) : newsletter/petition with $set.email.
        $capturedBody = null;
        $mockClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$capturedBody) {
            $capturedBody = $options['body'] ?? '';
            return new MockResponse('', ['http_code' => 200]);
        });
        $ctx = new SiteContext(); $ctx->setSite('parti-renaissance');
        $hash = $this->createStub(HashEmailService::class);
        $logger = $this->createStub(LoggerInterface::class);
        $service = new PostHogService(
            $mockClient, 'https://eu.i.posthog.com', 'phc_test', true,
            $ctx, $hash, 'test', 'abc', '1.0.0', $logger,
        );

        $service->captureServerSideWithSet(
            PostHogEventName::NEWSLETTER_SUBMITTED_SERVER,
            ['postal_code_prefix' => '75'],
            ['email' => 'test@example.com'],
            'distinct-id-hash',
        );

        $decoded = json_decode($capturedBody, true);
        $this->assertSame('newsletter_submitted_server', $decoded['event']);
        $this->assertSame('distinct-id-hash', $decoded['distinct_id']);
        $this->assertSame('test@example.com', $decoded['properties']['$set']['email']);
        $this->assertSame('75', $decoded['properties']['postal_code_prefix']);
    }
}
```

- [ ] **Step 8.2 — Verify fail + implémenter**

```php
<?php declare(strict_types=1);

namespace App\Analytics\PostHog;

use App\Analytics\PostHog\Events\PostHogEventName;
use App\Entity\Adherent;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Rôle : capture server-side événements PostHog + build super-properties.
 *
 * Feature flag POSTHOG_ENABLED : skip complet si false (dev/staging/preview).
 * Endpoint POST direct vers PostHog EU (pas via proxy — server-side, pas de blocage ITP).
 * Timeout 3s (server-side, plus court que proxy client).
 * Silent fail sur exception réseau — log WARNING (jamais bloquer un flow métier).
 *
 * Cf. spec §7 + §10.
 */
final class PostHogService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%posthog.api_host%')]
        private readonly string $apiHost,
        #[Autowire('%posthog.api_key%')]
        private readonly string $apiKey,
        #[Autowire('%posthog.enabled%')]
        private readonly bool $enabled,
        private readonly SiteContext $context,
        private readonly HashEmailService $hashEmail,
        #[Autowire('%env(APP_ENVIRONMENT)%')]
        private readonly string $environment,
        #[Autowire('%posthog.deploy_sha%')]
        private readonly string $deploySha,
        #[Autowire('%posthog.deploy_version%')]
        private readonly string $deployVersion,
        private readonly LoggerInterface $logger,
    ) {}

    /** @return array<string, mixed> */
    public function buildSuperProperties(): array
    {
        // Fallback safe : si env vars DEPLOY_SHA/APP_VERSION vides en runtime,
        // normalise en 'local' / 'unknown' au lieu de string vide (facilite le
        // filtering PostHog UI et évite le nettoyage manuel de données vides).
        return [
            'site'           => $this->context->getSite(),
            'platform'       => 'web',
            'environment'    => $this->environment,
            'deploy_sha'     => substr($this->deploySha ?: 'local', 0, 7),
            'deploy_version' => $this->deployVersion ?: 'unknown',
            'locale'         => 'fr-FR',
            'is_bot'         => false,
        ];
    }

    /** @param array<string, mixed> $properties */
    public function captureServerSide(
        PostHogEventName $event,
        array $properties,
        ?Adherent $user = null,
    ): void {
        if (!$this->enabled) {
            return;
        }

        $distinctId = $user?->getEmailAddress()
            ? $this->hashEmail->hash($user->getEmailAddress())
            : 'anonymous-server';

        $payload = [
            'api_key'     => $this->apiKey,
            'event'       => $event->value,
            'distinct_id' => $distinctId,
            'timestamp'   => (new \DateTimeImmutable())->format(DATE_ATOM),
            'properties'  => array_merge(
                $this->buildSuperProperties(),
                $properties,
            ),
        ];

        $this->postCapture($event, $payload);
    }

    /**
     * Variante Cas 2 (spec §8.6-7) — accepte `$set` explicite pour propager
     * `email` sur la Person PostHog (whitelisted events uniquement, cf. lint CI).
     *
     * @param array<string, mixed> $properties
     * @param array<string, mixed> $set
     */
    public function captureServerSideWithSet(
        PostHogEventName $event,
        array $properties,
        array $set,
        string $distinctId,
    ): void {
        if (!$this->enabled) {
            return;
        }

        $payload = [
            'api_key'     => $this->apiKey,
            'event'       => $event->value,
            'distinct_id' => $distinctId,
            'timestamp'   => (new \DateTimeImmutable())->format(DATE_ATOM),
            'properties'  => array_merge(
                $this->buildSuperProperties(),
                $properties,
                ['$set' => $set],
            ),
        ];

        $this->postCapture($event, $payload);
    }

    /** @param array<string, mixed> $payload */
    private function postCapture(PostHogEventName $event, array $payload): void
    {
        try {
            $this->httpClient->request(
                'POST',
                sprintf('%s/capture/', $this->apiHost),
                [
                    'body'    => json_encode($payload, JSON_THROW_ON_ERROR),
                    'headers' => ['Content-Type' => 'application/json'],
                    'timeout' => 3.0,
                ],
            );
        } catch (\Throwable $e) {
            // Ne jamais bloquer un flow métier si PostHog fail.
            $this->logger->warning('PostHog captureServerSide failed', [
                'event' => $event->value,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

- [ ] **Step 8.3 — Registration DI (`config/services.php`)**

Aucun tag spécial requis (autowire natif via constructor). Cache clear + verify.

- [ ] **Step 8.4 — Tests pass + commit**

```bash
vendor/bin/phpunit tests/Analytics/PostHog/PostHogServiceTest.php 2>&1 | tail -3
git add src/Analytics/PostHog/PostHogService.php tests/Analytics/PostHog/PostHogServiceTest.php
git commit -m "feat(analytics): PostHogService (super-properties + capture server-side, feature flag POSTHOG_ENABLED)"
```

---

## Task 9: PostHogTwigExtension — TDD

**Files:**
- Create: `src/Analytics/PostHog/Twig/PostHogTwigExtension.php`
- Create: `tests/Analytics/PostHog/Twig/PostHogTwigExtensionTest.php`

- [ ] **Step 9.1 — Test**

```php
<?php declare(strict_types=1);

namespace Tests\App\Analytics\PostHog\Twig;

use App\Analytics\PostHog\ConsentCookieHelper;
use App\Analytics\PostHog\HashEmailService;
use App\Analytics\PostHog\PostHogService;
use App\Analytics\PostHog\SiteContext;
use App\Analytics\PostHog\Twig\PostHogTwigExtension;
use App\Entity\Adherent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PostHogTwigExtensionTest extends TestCase
{
    private function makeStack(?Request $request = null): RequestStack
    {
        $stack = new RequestStack();
        if ($request !== null) {
            $stack->push($request);
        }
        return $stack;
    }

    public function testGlobalsExposeSiteAndCookieConfig(): void
    {
        $ctx = new SiteContext(); $ctx->setSite('attalpresident');
        $service = $this->createStub(PostHogService::class);
        $hash = $this->createStub(HashEmailService::class);
        $cookie = $this->createMock(ConsentCookieHelper::class);
        $cookie->method('read')->willReturn(true);
        $ext = new PostHogTwigExtension($ctx, $service, $hash, $cookie, $this->makeStack(Request::create('/')), true, 'phc_test');

        $globals = $ext->getGlobals();
        $this->assertTrue($globals['posthog_config_enabled']);
        $this->assertSame('phc_test', $globals['posthog_config_api_key']);
        $this->assertTrue($globals['posthog_consent_state']);
        $this->assertSame('attalpresident', $globals['posthog_site']);
        $this->assertSame('ap_consent', $globals['posthog_consent_cookie_name']);
        $this->assertSame('.attalpresident.fr', $globals['posthog_consent_cookie_domain']);
    }

    public function testGlobalsReturnSafeDefaultsWhenContextNotInitialized(): void
    {
        // Hostname hors périmètre PostHog (admin/api/webhooks/health) → SiteContext non-initialisé.
        // Le Twig snippet doit pouvoir se rendre sans crash.
        $ctx = new SiteContext(); // pas de setSite
        $service = $this->createStub(PostHogService::class);
        $hash = $this->createStub(HashEmailService::class);
        $cookie = $this->createStub(ConsentCookieHelper::class);
        $ext = new PostHogTwigExtension($ctx, $service, $hash, $cookie, $this->makeStack(), true, 'phc_test');

        $globals = $ext->getGlobals();
        $this->assertFalse($globals['posthog_config_enabled']);
        $this->assertSame('', $globals['posthog_config_api_key']);
        $this->assertNull($globals['posthog_consent_state']);
        $this->assertNull($globals['posthog_site']);
        $this->assertNull($globals['posthog_consent_cookie_name']);
        $this->assertNull($globals['posthog_consent_cookie_domain']);
    }

    public function testIdentifyPayloadNullWithoutUser(): void
    {
        $ctx = new SiteContext(); $ctx->setSite('parti-renaissance');
        $service = $this->createStub(PostHogService::class);
        $hash = $this->createStub(HashEmailService::class);
        $cookie = $this->createStub(ConsentCookieHelper::class);
        $ext = new PostHogTwigExtension($ctx, $service, $hash, $cookie, $this->makeStack(), true, 'phc_test');

        $this->assertNull($ext->identifyPayload(null));
    }

    public function testIdentifyPayloadContainsHashAndSetOnce(): void
    {
        $ctx = new SiteContext(); $ctx->setSite('parti-renaissance');
        $service = $this->createStub(PostHogService::class);
        $hash = $this->createMock(HashEmailService::class);
        $hash->method('hash')->willReturn('f1bfce1212e9adc7c7e789acc6727ef278c48618c2fb3b99580fde3c891b87ea');
        $cookie = $this->createStub(ConsentCookieHelper::class);

        $user = $this->createMock(Adherent::class);
        $user->method('getEmailAddress')->willReturn('test@example.com');
        $user->method('getPublicId')->willReturn('123ABCD');

        $ext = new PostHogTwigExtension($ctx, $service, $hash, $cookie, $this->makeStack(), true, 'phc_test');
        $payload = $ext->identifyPayload($user);

        $this->assertNotNull($payload);
        $this->assertSame('f1bfce1212e9adc7c7e789acc6727ef278c48618c2fb3b99580fde3c891b87ea', $payload['distinct_id']);
        $this->assertSame('123ABCD', $payload['$set']['public_id']);
        $this->assertSame('parti-renaissance', $payload['$set_once']['identified_from_site']);
        $this->assertArrayHasKey('identified_at', $payload['$set_once']);
    }
}
```

- [ ] **Step 9.2 — Implémenter `PostHogTwigExtension.php`**

```php
<?php declare(strict_types=1);

namespace App\Analytics\PostHog\Twig;

use App\Analytics\PostHog\ConsentCookieHelper;
use App\Analytics\PostHog\HashEmailService;
use App\Analytics\PostHog\PostHogService;
use App\Analytics\PostHog\SiteContext;
use App\Entity\Adherent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

/**
 * Expose des variables Twig globales + fonctions pour le rendering des snippets PostHog :
 *   {{ posthog_config_enabled }}          → feature flag POSTHOG_ENABLED (bool)
 *   {{ posthog_config_api_key }}          → clé publique client SDK
 *   {{ posthog_consent_state }}           → bool|null (server-detected du cookie courant)
 *   {{ posthog_site }}                    → site marque courante (ou null hors périmètre)
 *   {{ posthog_consent_cookie_name }}     → ap_consent / pr_consent / ...
 *   {{ posthog_consent_cookie_domain }}   → .attalpresident.fr / ...
 *   {{ posthog_super_properties()|json_encode|raw }} → super-props auto pour init SDK JS
 *   {{ posthog_identify_payload(app.user)|json_encode|raw }} → payload identify si user, sinon null
 *
 * FAIL-OPEN : si SiteContext n'est pas initialisé (hostname hors périmètre PostHog
 * Renaissance — admin/api/webhooks/health), retourne tous les globals à false/null/empty
 * sans crash. Le snippet Twig court-circuite alors l'injection PostHog côté client.
 *
 * Cf. spec §4.3.
 */
final class PostHogTwigExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly SiteContext $context,
        private readonly PostHogService $service,
        private readonly HashEmailService $hashEmail,
        private readonly ConsentCookieHelper $cookieHelper,
        private readonly RequestStack $requestStack,
        #[Autowire('%posthog.enabled%')]
        private readonly bool $enabled,
        #[Autowire('%posthog.api_key%')]
        private readonly string $apiKey,
    ) {}

    /** @return array<string, mixed> */
    public function getGlobals(): array
    {
        if (!$this->context->isInitialized()) {
            // Hors périmètre PostHog Renaissance (admin/api/webhooks/health).
            // Renvoie tous les globals safe pour que le snippet Twig ne crash pas.
            return [
                'posthog_config_enabled'         => false,
                'posthog_config_api_key'         => '',
                'posthog_consent_state'          => null,
                'posthog_site'                   => null,
                'posthog_consent_cookie_name'    => null,
                'posthog_consent_cookie_domain'  => null,
            ];
        }
        $config = $this->context->getCookieConfig();
        $request = $this->requestStack->getCurrentRequest();
        $consentState = $request !== null ? $this->cookieHelper->read($request) : null;
        return [
            'posthog_config_enabled'         => $this->enabled,
            'posthog_config_api_key'         => $this->apiKey,
            'posthog_consent_state'          => $consentState,
            'posthog_site'                   => $this->context->getSite(),
            'posthog_consent_cookie_name'    => $config['name'],
            'posthog_consent_cookie_domain'  => $config['domain'],
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('posthog_super_properties', $this->superProperties(...)),
            new TwigFunction('posthog_identify_payload', $this->identifyPayload(...)),
        ];
    }

    /** @return array<string, mixed> */
    public function superProperties(): array
    {
        return $this->service->buildSuperProperties();
    }

    /** @return array<string, mixed>|null */
    public function identifyPayload(?Adherent $user): ?array
    {
        if (null === $user || null === $user->getEmailAddress()) {
            return null;
        }
        return [
            'distinct_id' => $this->hashEmail->hash($user->getEmailAddress()),
            '$set' => [
                'public_id' => $user->getPublicId(),
            ],
            '$set_once' => [
                'identified_from_site' => $this->context->getSite(),
                'identified_at' => (new \DateTimeImmutable())->format(DATE_ATOM),
            ],
        ];
    }
}
```

- [ ] **Step 9.3 — Tests + commit**

```bash
vendor/bin/phpunit tests/Analytics/PostHog/Twig/PostHogTwigExtensionTest.php 2>&1 | tail -3
git add src/Analytics/PostHog/Twig/PostHogTwigExtension.php tests/Analytics/PostHog/Twig/PostHogTwigExtensionTest.php
git commit -m "feat(analytics): PostHogTwigExtension (globals + super_properties + identify_payload)"
```

---

## Task 10: Assets JS SDK PostHog + webpack entry

**Files:**
- Create: `assets/analytics/posthog/posthog-init.js`
- Create: `assets/analytics/posthog/posthog-capture.js`
- Create: `assets/analytics/posthog/posthog-consent.js`
- Create: `assets/analytics/posthog/posthog-identify.js`
- Modify: `assets/bootstrap.js`

- [ ] **Step 10.1 — Créer `posthog-capture.js`**

```javascript
// assets/analytics/posthog/posthog-capture.js
// Miroir enum PHP PostHogEventName. Doit rester byte-identique à src/Analytics/PostHog/Events/PostHogEventName.php.
// Test de cohérence en CI : chaque POSTHOG_EVENTS.X doit exister dans PostHogEventName::X.

import posthog from 'posthog-js';

export const POSTHOG_EVENTS = Object.freeze({
  CONSENT_GRANTED: 'consent_granted',
  CONSENT_REFUSED: 'consent_refused',
  CONSENT_DISMISSED: 'consent_dismissed',
  LOGIN_SUCCEEDED: 'login_succeeded',
  LOGIN_FAILED: 'login_failed',
  LOGOUT_COMPLETED: 'logout_completed',
  PASSWORD_RESET_REQUESTED: 'password_reset_requested',
  PASSWORD_RESET_COMPLETED: 'password_reset_completed',
  MAGIC_LINK_REQUESTED: 'magic_link_requested',
  MAGIC_LINK_LOGIN_SUCCEEDED: 'magic_link_login_succeeded',
  ADHESION_STARTED: 'adhesion_started',
  ADHESION_FORM_SUBMITTED: 'adhesion_form_submitted',
  ADHESION_PAYMENT_INITIATED: 'adhesion_payment_initiated',
  ADHESION_COMPLETED: 'adhesion_completed',
  ADHESION_PAYMENT_FAILED: 'adhesion_payment_failed',
  ADHESION_FINISH_PAGE_VIEWED: 'adhesion_finish_page_viewed',
  DONATION_STARTED: 'donation_started',
  DONATION_FORM_SUBMITTED: 'donation_form_submitted',
  DONATION_PAYMENT_INITIATED: 'donation_payment_initiated',
  DONATION_COMPLETED: 'donation_completed',
  DONATION_PAYMENT_FAILED: 'donation_payment_failed',
  NATIONAL_EVENT_PAGE_VIEWED: 'national_event_page_viewed',
  NATIONAL_EVENT_INSCRIPTION_SUBMITTED: 'national_event_inscription_submitted',
  NATIONAL_EVENT_INSCRIPTION_CONFIRMED: 'national_event_inscription_confirmed',
  NATIONAL_EVENT_PAYMENT_COMPLETED: 'national_event_payment_completed',
  NATIONAL_EVENT_INSCRIPTION_EDITED: 'national_event_inscription_edited',
  PROFILE_PAGE_VIEWED: 'profile_page_viewed',
  PROFILE_UPDATED: 'profile_updated',
});

export function capture(eventName, properties = {}) {
  if (!Object.values(POSTHOG_EVENTS).includes(eventName)) {
    console.warn(`[posthog] Unknown event: ${eventName}`);
    return;
  }
  posthog.capture(eventName, properties);
}
```

- [ ] **Step 10.2 — Créer `posthog-consent.js`**

```javascript
// assets/analytics/posthog/posthog-consent.js
import posthog from 'posthog-js';

const COOKIE_MAX_AGE_SECONDS = 34128000; // ~13 mois CNIL max

export function readConsent(cookieName) {
  const match = document.cookie.match(new RegExp(`(?:^|; )${cookieName}=(0|1)`));
  return match ? match[1] === '1' : null;
}

export function writeConsent(cookieName, cookieDomain, granted) {
  const expires = new Date(Date.now() + COOKIE_MAX_AGE_SECONDS * 1000).toUTCString();
  const value = granted ? '1' : '0';
  // Skip `secure` en HTTP local dev (localhost, test.renaissance.code sur http://).
  // En prod, tous les hostnames servent HTTPS strict, donc `secure` reste posé.
  const secure = window.location.protocol === 'https:' ? '; secure' : '';
  document.cookie = `${cookieName}=${value}; expires=${expires}; path=/; domain=${cookieDomain}${secure}; samesite=lax`;
}

export function applyConsentToSdk(granted) {
  if (granted) {
    posthog.opt_in_capturing();
  } else {
    posthog.opt_out_capturing();
  }
}
```

- [ ] **Step 10.3 — Créer `posthog-identify.js`**

```javascript
// assets/analytics/posthog/posthog-identify.js
import posthog from 'posthog-js';

export function applyIdentifyPayload() {
  if (!window.__POSTHOG_IDENTIFY__) return;
  const { distinct_id, $set, $set_once } = window.__POSTHOG_IDENTIFY__;
  posthog.identify(distinct_id, { $set, $set_once });
}
```

- [ ] **Step 10.4 — Créer `posthog-init.js`**

```javascript
// assets/analytics/posthog/posthog-init.js
// Boot du SDK PostHog. Appelé depuis bootstrap.js après Sentry init.
// Gate par window.__POSTHOG_CONFIG__ injecté par le snippet Twig server-rendered.

import posthog from 'posthog-js';
import { readConsent, applyConsentToSdk } from './posthog-consent';
import { applyIdentifyPayload } from './posthog-identify';

export function initPostHog() {
  const config = window.__POSTHOG_CONFIG__;
  if (!config || !config.enabled || !config.apiKey) {
    return; // Feature flag off ou config absente
  }

  posthog.init(config.apiKey, {
    api_host: '/ingest',                    // Reverse proxy first-party
    ui_host: 'https://eu.posthog.com',
    autocapture: {
      captureLifecycleEvents: true,
      captureScreens: true,
      captureTouches: false,
    },
    capture_pageview: true,
    disable_session_recording: true,        // Session Replay OFF Phase 1
    opt_out_capturing_by_default: true,
    persistence: 'cookie',
    loaded: (ph) => {
      // Register super-properties auto
      if (config.superProperties) {
        ph.register(config.superProperties);
      }
      // Applique consent state initial (server-detected)
      if (config.consent === true) {
        ph.opt_in_capturing();
      } else if (config.consent === false) {
        ph.opt_out_capturing();
      }
      // Applique identify si user connecté
      applyIdentifyPayload();
    },
  });

  // Bridge storage : si le cookie change en runtime (ex. après clic Réglages),
  // reapply au SDK via un helper exposé globalement.
  window.__POSTHOG_APPLY_CONSENT__ = (granted) => applyConsentToSdk(granted);
}
```

- [ ] **Step 10.5 — Wire dans `assets/bootstrap.js` après Sentry**

Éditer `assets/bootstrap.js`, ajouter après le bloc Sentry init :

```javascript
import { initPostHog } from './analytics/posthog/posthog-init';
initPostHog();
```

- [ ] **Step 10.6 — Build + verify inclusion PostHog dans le bundle + commit**

```bash
pnpm build-dev 2>&1 | tail -3
# Verify PostHog est inclus dans le bundle output
grep -q "posthog" public/built/bootstrap*.js && echo "✅ posthog présent dans bundle" || echo "❌ posthog absent du bundle"
```

Attendu : compilation OK + `✅ posthog présent dans bundle`. Si `❌ posthog absent`, le tree-shaking a viré l'import — vérifier que `initPostHog()` est bien appelé côté `bootstrap.js` (pas juste importé).

```bash
git add assets/analytics/posthog/ assets/bootstrap.js
git commit -m "feat(analytics): assets JS PostHog (init/capture/consent/identify) + wire dans bootstrap.js"
```

---

## Task 11: Templates Twig (snippet + consent banner)

**Files:**
- Create: `templates/analytics/posthog/_snippet.html.twig`
- Create: `templates/analytics/posthog/_consent_banner.html.twig`
- Create: `templates/analytics/posthog/_consent_settings_row.html.twig`

- [ ] **Step 11.1 — Créer `_snippet.html.twig`**

```twig
{# templates/analytics/posthog/_snippet.html.twig
   Snippet inline injecté dans le <head> par base_renaissance.html.twig.
   Rendu server-side avec les super-properties + identify payload si app.user.
   Consommé par assets/analytics/posthog/posthog-init.js au boot du SDK.
#}
<script>
  window.__POSTHOG_CONFIG__ = {
    enabled:     {{ posthog_config_enabled|default(false)|json_encode|raw }},
    apiKey:      {{ posthog_config_api_key|default('')|json_encode|raw }},
    superProperties: {{ posthog_super_properties()|json_encode|raw }},
    consent:     {{ posthog_consent_state|default(null)|json_encode|raw }}
  };
  {% set identify = posthog_identify_payload(app.user|default(null)) %}
  {% if identify %}
    window.__POSTHOG_IDENTIFY__ = {{ identify|json_encode|raw }};
  {% endif %}
</script>
```

- [ ] **Step 11.2 — Créer `_consent_banner.html.twig`**

```twig
{# templates/analytics/posthog/_consent_banner.html.twig
   Bannière consent affichée aux déconnectés au 1er boot (server-side detection).
   Alpine.js pilote le POST vers /parametres/confidentialite.
#}
{% if posthog_consent_state is null and app.user is null %}
<div id="posthog-consent-banner"
     x-data="{ open: true }"
     x-show="open"
     role="alert"
     class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 shadow-lg p-4">
  <div class="max-w-4xl mx-auto flex flex-col md:flex-row items-start md:items-center gap-4">
    <div class="flex-1">
      <h3 class="font-semibold text-lg mb-1">Analyse d'usage anonymisée</h3>
      <p class="text-sm text-gray-700">
        Nous mesurons l'usage du site pour l'améliorer. Aucune donnée personnelle sensible n'est collectée.
        Vous pouvez changer d'avis à tout moment dans
        <a href="{{ path('app_analytics_privacy_settings') }}" class="text-blue-600 underline">Réglages > Confidentialité</a>.
      </p>
    </div>
    <div class="flex gap-2">
      <form method="post" action="{{ path('app_analytics_privacy_settings_toggle') }}" @submit.prevent="fetch($el.action, { method: 'POST', body: new FormData($el) }).then(() => { open = false; window.__POSTHOG_APPLY_CONSENT__?.(true); })">
        <input type="hidden" name="granted" value="1">
        <input type="hidden" name="source" value="banner">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded font-semibold">Accepter</button>
      </form>
      <form method="post" action="{{ path('app_analytics_privacy_settings_toggle') }}" @submit.prevent="fetch($el.action, { method: 'POST', body: new FormData($el) }).then(() => { open = false; window.__POSTHOG_APPLY_CONSENT__?.(false); })">
        <input type="hidden" name="granted" value="0">
        <input type="hidden" name="source" value="banner">
        <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-800 rounded font-semibold">Refuser</button>
      </form>
      <a href="{{ path('app_analytics_privacy_settings') }}" class="px-4 py-2 border border-blue-600 text-blue-600 rounded font-semibold">Gérer</a>
    </div>
  </div>
</div>
{% endif %}
```

- [ ] **Step 11.3 — Créer `_consent_settings_row.html.twig`**

```twig
{# Ligne à insérer dans l'écran Réglages > Confidentialité #}
<div class="flex items-center justify-between p-4 bg-white rounded border">
  <div class="flex-1 mr-4">
    <h4 class="font-medium">Analyse d'usage anonymisée</h4>
    <p class="text-sm text-gray-600 mt-1">PostHog EU, aucune donnée personnelle sensible.</p>
  </div>
  <form method="post" action="{{ path('app_analytics_privacy_settings_toggle') }}">
    <input type="hidden" name="granted" value="{{ posthog_consent_state == false ? '1' : '0' }}">
    <input type="hidden" name="source" value="settings">
    <button type="submit" class="px-4 py-2 {{ posthog_consent_state ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' }} rounded">
      {{ posthog_consent_state ? 'Désactiver' : 'Activer' }}
    </button>
  </form>
</div>
```

- [ ] **Step 11.4 — Commit**

```bash
git add templates/analytics/posthog/
git commit -m "feat(analytics): templates Twig (snippet + consent banner Alpine.js + settings row)"
```

---

## Task 12: ConsentSettingsController + template écran Confidentialité

**Files:**
- Create: `src/Analytics/PostHog/ConsentSettingsController.php`
- Create: `templates/renaissance/parametres/confidentialite.html.twig`
- Modify: `config/routes/analytics.yaml`

- [ ] **Step 12.1 — Implémenter `ConsentSettingsController.php`**

```php
<?php declare(strict_types=1);

namespace App\Analytics\PostHog;

use App\Analytics\PostHog\Events\PostHogEventName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConsentSettingsController extends AbstractController
{
    public function __construct(
        private readonly ConsentCookieHelper $cookieHelper,
        private readonly PostHogService $service,
    ) {}

    #[Route(
        '/parametres/confidentialite',
        name: 'app_analytics_privacy_settings',
        requirements: ['marque' => 'parti-renaissance|attalpresident|avecgabrielattal|nouvellerepublique'],
        host: 'utilisateur.{marque}.fr',
        methods: ['GET'],
    )]
    public function show(Request $request): Response
    {
        return $this->render('renaissance/parametres/confidentialite.html.twig', [
            'consent_state' => $this->cookieHelper->read($request),
        ]);
    }

    #[Route(
        '/parametres/confidentialite/toggle',
        name: 'app_analytics_privacy_settings_toggle',
        requirements: ['marque' => 'parti-renaissance|attalpresident|avecgabrielattal|nouvellerepublique'],
        host: 'utilisateur.{marque}.fr',
        methods: ['POST'],
    )]
    public function toggle(Request $request): Response
    {
        $granted = '1' === $request->request->get('granted', '0');
        // M6 : source explicite via POST param (hidden input dans banner/settings templates),
        // au lieu de deviner via Referer (fragile, dépend du RefererPolicy client).
        $source = $request->request->get('source', 'banner'); // 'banner' | 'settings'
        $cookie = $this->cookieHelper->write($granted);

        $event = $granted ? PostHogEventName::CONSENT_GRANTED : PostHogEventName::CONSENT_REFUSED;
        $this->service->captureServerSide($event, [
            'source' => $source,
            'consent_version' => '1',
        ], $this->getUser());

        $response = new Response('', 204);
        $response->headers->setCookie($cookie);
        return $response;
    }
}
```

- [ ] **Step 12.2 — Créer template `confidentialite.html.twig`**

```twig
{% extends 'base_renaissance.html.twig' %}

{% block title %}Confidentialité{% endblock %}

{% block content %}
<div class="max-w-2xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Confidentialité</h1>
    <p class="text-gray-700 mb-6">
        Gérez vos préférences d'analyse d'usage. Vous pouvez activer ou désactiver
        la collecte anonymisée à tout moment. En cas de désactivation, aucun événement
        analytique ne sera envoyé.
    </p>
    {% set posthog_consent_state = consent_state %}
    {{ include('analytics/posthog/_consent_settings_row.html.twig') }}
</div>
{% endblock %}
```

- [ ] **Step 12.3 — Commit**

```bash
git add src/Analytics/PostHog/ConsentSettingsController.php templates/renaissance/parametres/confidentialite.html.twig
git commit -m "feat(consent): ConsentSettingsController + /parametres/confidentialite (toggle granted↔refused, capture server-side)"
```

---

## Task 13: AuthEventSubscriber (login/logout via Symfony events) — TDD

**Files:**
- Create: `src/Analytics/PostHog/EventSubscriber/AuthEventSubscriber.php`
- Create: `tests/Analytics/PostHog/EventSubscriber/AuthEventSubscriberTest.php`
- Modify: `config/services.php`

- [ ] **Step 13.1 — Test**

> ⚠️ Vérifier la signature exacte `LoginSuccessEvent::__construct` du repo avant de figer le test :
> ```bash
> composer show symfony/security-http | head -20
> ```
> Signature Symfony 7.4 attendue : `(AuthenticatorInterface, ?Passport, TokenInterface, Request, ?Response, string $firewallName)`. Si un argument positional a été ajouté en 7.4.x, adapter le `new LoginSuccessEvent(...)` du test en conséquence — sinon le test crash au setup.

```php
<?php declare(strict_types=1);

namespace Tests\App\Analytics\PostHog\EventSubscriber;

use App\Analytics\PostHog\EventSubscriber\AuthEventSubscriber;
use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\PostHogService;
use App\Entity\Adherent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class AuthEventSubscriberTest extends TestCase
{
    public function testOnLoginSuccessCapturesEvent(): void
    {
        $service = $this->createMock(PostHogService::class);
        $service->expects($this->once())
            ->method('captureServerSide')
            ->with(
                PostHogEventName::LOGIN_SUCCEEDED,
                $this->arrayHasKey('method'),
                $this->isInstanceOf(Adherent::class),
            );
        $subscriber = new AuthEventSubscriber($service);

        $user = $this->createMock(Adherent::class);
        $token = new UsernamePasswordToken($user, 'main');
        $request = new Request();
        $authenticator = $this->createMock(AuthenticatorInterface::class);
        $event = new LoginSuccessEvent($authenticator, null, $token, $request, null, 'main');

        $subscriber->onLoginSuccess($event);
    }

    public function testOnLoginFailureMapsBadCredentials(): void
    {
        $service = $this->createMock(PostHogService::class);
        $service->expects($this->once())
            ->method('captureServerSide')
            ->with(
                PostHogEventName::LOGIN_FAILED,
                $this->callback(fn($props) => $props['reason'] === 'bad_credentials'),
            );
        $subscriber = new AuthEventSubscriber($service);

        $authenticator = $this->createMock(AuthenticatorInterface::class);
        $event = new LoginFailureEvent(
            new BadCredentialsException(),
            $authenticator,
            new Request(),
            null,
            'main',
        );

        $subscriber->onLoginFailure($event);
    }
}
```

- [ ] **Step 13.2 — Implémenter `AuthEventSubscriber.php`**

```php
<?php declare(strict_types=1);

namespace App\Analytics\PostHog\EventSubscriber;

use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\PostHogService;
use App\Entity\Adherent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Wire login_succeeded / login_failed / logout_completed via Symfony 7.4 firewall events.
 * Pattern cohérent avec UserActionHistorySubscriber existant.
 *
 * Cf. spec §8.1.
 */
final class AuthEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly PostHogService $service) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
            LogoutEvent::class       => 'onLogout',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof Adherent) return;

        $method = $this->detectAuthMethod($event);
        $eventName = 'magic-link' === $method
            ? PostHogEventName::MAGIC_LINK_LOGIN_SUCCEEDED
            : PostHogEventName::LOGIN_SUCCEEDED;

        $this->service->captureServerSide($eventName, ['method' => $method], $user);
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $this->service->captureServerSide(
            PostHogEventName::LOGIN_FAILED,
            ['reason' => $this->classifyError($event->getException())],
        );
    }

    public function onLogout(LogoutEvent $event): void
    {
        $user = $event->getToken()?->getUser();
        $this->service->captureServerSide(
            PostHogEventName::LOGOUT_COMPLETED,
            [],
            $user instanceof Adherent ? $user : null,
        );
    }

    private function detectAuthMethod(LoginSuccessEvent $event): string
    {
        $authenticatorClass = $event->getAuthenticator()::class;
        return match (true) {
            str_contains($authenticatorClass, 'MagicLink') => 'magic-link',
            str_contains($authenticatorClass, 'OAuth')     => 'oauth',
            default                                        => 'form',
        };
    }

    private function classifyError(AuthenticationException $e): string
    {
        return match (true) {
            $e instanceof BadCredentialsException => 'bad_credentials',
            default                               => 'unknown',
        };
    }
}
```

- [ ] **Step 13.3 — Vérifier auto-registration DI + tests + commit**

`AuthEventSubscriber implements EventSubscriberInterface` doit être auto-configuré par Symfony (le repo a `_defaults: autoconfigure: true` en `config/services.php:82`). Vérifier :

```bash
php bin/console debug:event-dispatcher 2>&1 | grep -iE "AuthEventSubscriber|LoginSuccessEvent"
```

Attendu : `AuthEventSubscriber` listé sur `LoginSuccessEvent`, `LoginFailureEvent`, `LogoutEvent`. Si absent (autoconfigure désactivée par override local), ajouter tag explicit dans `config/services.php` :

```php
$services->set(App\Analytics\PostHog\EventSubscriber\AuthEventSubscriber::class)
    ->tag('kernel.event_subscriber');
```

```bash
vendor/bin/phpunit tests/Analytics/PostHog/EventSubscriber/ 2>&1 | tail -3
git add src/Analytics/PostHog/EventSubscriber/ tests/Analytics/PostHog/EventSubscriber/
git commit -m "feat(analytics): AuthEventSubscriber (LoginSuccessEvent/Failure/Logout via Symfony 7.4 firewall)"
```

---

## Note critique — Injection `PostHogService` dans controllers existants (Tasks 14-19)

> ⚠️ Chaque controller Renaissance a déjà un constructor avec 3-8 dépendances (via property promotion ou setter injection). Deux approches sûres pour ajouter `PostHogService` sans casser :
>
> 1. **Ajouter en fin de property promotion** (recommandé Symfony 7.4 pattern) — évite de casser l'ordre des args existants.
> 2. **Setter injection dédié** :
>    ```php
>    #[Required]
>    public function setPostHogService(PostHogService $service): void {
>        $this->postHog = $service;
>    }
>    ```
>    Cette 2e approche n'impacte pas le constructor (safe pour sous-classes/tests instanciant manuellement).
>
> Choix par Fontaine selon les patterns existants du repo. **Ne jamais réordonner les args positional existants d'un constructor public** — casse les sous-classes et les tests qui instancient manuellement.

---

## Task 14: Wire password_reset + magic_link (controllers)

**Files:**
- Modify: `src/Controller/Renaissance/SecurityController.php`
- Modify: `src/Controller/MagicLinkController.php`

- [ ] **Step 14.1 — Wire dans `SecurityController::retrieveForgotPasswordAction`**

Après le flush qui persiste la demande de reset (le point exact varie selon le code réel — locate `->flush()` ou event dispatch dans le fichier), ajouter :

```php
use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\PostHogService;

// Dans __construct :
private readonly PostHogService $postHog,

// Dans retrieveForgotPasswordAction, après succès :
$this->postHog->captureServerSide(
    PostHogEventName::PASSWORD_RESET_REQUESTED,
    [],
);
```

Idem `resetPasswordAction` post-flush :

```php
$this->postHog->captureServerSide(
    PostHogEventName::PASSWORD_RESET_COMPLETED,
    [],
    $adherent, // user reset via token
);
```

- [ ] **Step 14.2 — Wire dans `MagicLinkController::getMagicLinkAction`**

Après succès génération du lien :

```php
$this->postHog->captureServerSide(
    PostHogEventName::MAGIC_LINK_REQUESTED,
    [],
);
```

Note : `magic_link_login_succeeded` est déjà capturé par `AuthEventSubscriber::detectAuthMethod` en Task 13.

- [ ] **Step 14.3 — Commit**

```bash
git add src/Controller/Renaissance/SecurityController.php src/Controller/MagicLinkController.php
git commit -m "feat(analytics): wire password_reset_requested/completed + magic_link_requested"
```

---

## Task 15: Wire cluster Adhésion

**Files:**
- Modify: `src/Controller/Renaissance/Adhesion/AdhesionController.php`
- Modify: `src/Controller/Renaissance/Adhesion/FinishController.php`

- [ ] **Step 15.1 — Wire adhesion_started + adhesion_form_submitted**

Dans `AdhesionController::__invoke()`, au GET initial de la form → `ADHESION_STARTED` avec `has_referrer_pid: bool`. Au POST successful (submit du form pré-paiement) → `ADHESION_FORM_SUBMITTED`. Avant redirect vers `/paiement/{uuid}` → `ADHESION_PAYMENT_INITIATED`.

```php
// AdhesionController.php extrait
public function __invoke(Request $request, ...): Response
{
    if ($request->isMethod('GET')) {
        $this->postHog->captureServerSide(
            PostHogEventName::ADHESION_STARTED,
            ['has_referrer_pid' => null !== $pid],
        );
    }

    // ... existing form handling ...

    if ($form->isSubmitted() && $form->isValid()) {
        $this->postHog->captureServerSide(
            PostHogEventName::ADHESION_FORM_SUBMITTED,
            ['step' => 'info_personnelle'],
        );
        // ... persistence + redirect payment
        $this->postHog->captureServerSide(
            PostHogEventName::ADHESION_PAYMENT_INITIATED,
            ['payment_provider' => 'paybox'],
        );
        return $this->redirectToRoute('app_payment', ['uuid' => $adhesion->getUuid()]);
    }
    // ...
}
```

- [ ] **Step 15.2 — Wire adhesion_finish_page_viewed dans `FinishController`**

```php
public function __invoke(): Response
{
    $this->postHog->captureServerSide(
        PostHogEventName::ADHESION_FINISH_PAGE_VIEWED,
        [],
        $this->getUser(),
    );
    return $this->render(...);
}
```

- [ ] **Step 15.3 — adhesion_completed / adhesion_payment_failed**

Le callback Paybox est traité par **`src/Controller/Renaissance/Payment/PaymentController.php`** (route `app_payment_callback`). Le handler `PayboxPaymentSubscription` post-callback dispatche des events métier. Wire `adhesion_completed` / `adhesion_payment_failed` selon deux options :

- **Option A (recommandée)** : dans `PaymentController::__invoke()`, après validation du callback Paybox, capture selon le status. Direct, pas de couplage event dispatcher.
- **Option B** : créer un `PaymentEventSubscriber` sur un event métier custom si le repo en dispatche déjà un (grep `PaymentCompletedEvent` pour vérifier). Plus découplé mais nécessite un event existant.

Payloads capturés : `amount_eur`, `payment_method`, `is_first_adhesion`.

- [ ] **Step 15.4 — Commit**

```bash
git add src/Controller/Renaissance/Adhesion/
git commit -m "feat(analytics): wire cluster Adhésion (5 events + payment_completed via callback)"
```

---

## Task 16: Wire cluster Don

**Files:**
- Modify: `src/Controller/Renaissance/Donation/DonationController.php`
- Modify: `src/Controller/Renaissance/Donation/FinishController.php`

Pattern identique à Task 15. Events : `DONATION_STARTED`, `DONATION_FORM_SUBMITTED`, `DONATION_PAYMENT_INITIATED`, `DONATION_COMPLETED`, `DONATION_PAYMENT_FAILED`.

**Décision Cas 1 forcé** (spec §8.3) : `donor_type: "user" | "anonymous"` en propriété, **jamais** `$set.email` côté serveur ni client. Les dons anonymes remontent en `donation_completed` avec `donor_type: "anonymous"`.

- [ ] **Step 16.1 — Wire DonationController**

```php
if ($request->isMethod('GET')) {
    $this->postHog->captureServerSide(
        PostHogEventName::DONATION_STARTED,
        ['is_grand_donateur' => str_contains($request->getPathInfo(), '/grands-donateurs')],
    );
}
// Submit success :
$this->postHog->captureServerSide(
    PostHogEventName::DONATION_FORM_SUBMITTED,
    [
        'amount_eur' => $donation->getAmount(),
        'is_recurring' => $donation->isRecurring(),
    ],
);
$this->postHog->captureServerSide(
    PostHogEventName::DONATION_PAYMENT_INITIATED,
    ['payment_provider' => 'paybox'],
);
```

- [ ] **Step 16.2 — Wire FinishController + callback Paybox**

Le callback Paybox est **le même `PaymentController` que pour l'adhésion** (`src/Controller/Renaissance/Payment/PaymentController.php`, route `app_payment_callback`). Grep dans le controller pour identifier la branche don (probablement basée sur le type de `Payment` ou la sub-entité liée). Wire `DONATION_COMPLETED` / `DONATION_PAYMENT_FAILED` selon le status Paybox, avec les payloads ci-dessous.

```php
$this->postHog->captureServerSide(
    PostHogEventName::DONATION_COMPLETED,
    [
        'amount_eur' => $donation->getAmount(),
        'is_recurring' => $donation->isRecurring(),
        'donor_type' => null !== $donation->getDonator()?->getAdherent() ? 'user' : 'anonymous',
    ],
    $this->getUser(),
);
```

- [ ] **Step 16.3 — Commit**

```bash
git add src/Controller/Renaissance/Donation/
git commit -m "feat(analytics): wire cluster Don (5 events, Cas 1 forcé — jamais \$set.email)"
```

---

## Task 17: Wire cluster Meeting national

**Files:**
- Modify: `src/Controller/Renaissance/NationalEvent/InscriptionController.php`
- Modify: `src/Controller/Renaissance/NationalEvent/ConfirmInscriptionController.php`
- Modify: `src/Controller/Renaissance/NationalEvent/EditInscriptionController.php`
- Modify: `src/Controller/Renaissance/NationalEvent/PaymentStatusController.php`

**Décision Cas 1 forcé** (spec §8.4) : `inscription_uuid` en propriété, jointure DWH via `national_event_inscriptions.email` server-only (hors PostHog).

- [ ] **Step 17.1 — Wire les 5 events**

`NATIONAL_EVENT_PAGE_VIEWED` (GET), `NATIONAL_EVENT_INSCRIPTION_SUBMITTED` (POST), `NATIONAL_EVENT_INSCRIPTION_CONFIRMED` (Confirm), `NATIONAL_EVENT_PAYMENT_COMPLETED` (PaymentStatus success), `NATIONAL_EVENT_INSCRIPTION_EDITED` (Edit).

Payloads : `event_slug`, `event_uuid`, `has_referrer_pid`, `is_paid`, `inscription_uuid`, `amount_eur`, `payment_method`.

- [ ] **Step 17.2 — Commit**

```bash
git add src/Controller/Renaissance/NationalEvent/
git commit -m "feat(analytics): wire cluster Meeting national (5 events, Cas 1 forcé)"
```

---

## Task 18: Wire cluster Profil

**Files:**
- Modify: `src/Controller/Renaissance/Adherent/ProfileController.php`

- [ ] **Step 18.1 — Wire profile_page_viewed + profile_updated**

```php
// GET /parametres/mon-compte
$this->postHog->captureServerSide(
    PostHogEventName::PROFILE_PAGE_VIEWED,
    [],
    $this->getUser(),
);
// POST success (après persist)
$fieldsChanged = $this->diffFields($originalUser, $updatedUser); // helper à créer, retourne list<string>
$this->postHog->captureServerSide(
    PostHogEventName::PROFILE_UPDATED,
    ['fields_changed' => $fieldsChanged],
    $this->getUser(),
);
```

**Important** : `fields_changed` = liste de **noms techniques** de champs, jamais les valeurs (pas de PII).

- [ ] **Step 18.2 — Commit**

```bash
git add src/Controller/Renaissance/Adherent/ProfileController.php
git commit -m "feat(analytics): wire cluster Profil (profile_page_viewed + profile_updated sans PII)"
```

---

## Task 19: Wire newsletter + petition (Cas 2 server-side `$set.email`)

**Files:**
- Modify: `src/Controller/Renaissance/Newsletter/SaveNewsletterController.php`
- Modify: `src/Controller/Renaissance/Newsletter/ConfirmNewsletterController.php`
- Modify: `src/Controller/Renaissance/Petition/SignatureValidateController.php`

**Doctrine Cas 2** (spec §8.6-7) : `$set.email` autorisé server-side pour les 3 events. Ces events sont whitelistés dans le workflow `lint-posthog-privacy.yml`.

- [ ] **Step 19.1 — Wire newsletter_submitted_server**

`PostHogService::captureServerSideWithSet` a déjà été implémenté en Task 8. Ici on l'appelle depuis les controllers Cas 2.

Dans `SaveNewsletterController` (POST succès) :

```php
$this->postHog->captureServerSideWithSet(
    PostHogEventName::NEWSLETTER_SUBMITTED_SERVER,
    [
        'postal_code_prefix' => substr($newsletterSubscription->getPostalCode() ?? '', 0, 2),
        'source_page' => $request->headers->get('Referer', ''),
    ],
    ['email' => $newsletterSubscription->getEmail()],
    $this->hashEmail->hash($newsletterSubscription->getEmail()),
);
```

- [ ] **Step 19.2 — Wire newsletter_confirmed_server dans `ConfirmNewsletterController`**

Idem, avec `$set.email = ...`.

- [ ] **Step 19.3 — Wire petition_signed_server dans `SignatureValidateController`**

```php
$this->postHog->captureServerSideWithSet(
    PostHogEventName::PETITION_SIGNED_SERVER,
    ['petition_uuid' => $signature->getPetition()->getUuid()],
    ['email' => $signature->getEmailAddress()],
    $this->hashEmail->hash($signature->getEmailAddress()),
);
```

- [ ] **Step 19.4 — Commit**

```bash
git add src/Analytics/PostHog/PostHogService.php src/Controller/Renaissance/Newsletter/ src/Controller/Renaissance/Petition/
git commit -m "feat(analytics): wire newsletter + petition Cas 2 server-side (\$set.email autorisé doctrine §3)"
```

---

## Task 20: ADR local + docs taxonomie events

**Files:**
- Create: `docs/adrs/posthog-multi-domain.md`
- Create: `docs/analytics/posthog-events-parti-renaissance.md`

- [ ] **Step 20.1 — Créer `docs/adrs/posthog-multi-domain.md`**

Contenu concis (~80 lignes) reprenant les décisions structurantes :
- Multi-domain white-label + SiteDetector
- Salts marque-specific (divergence doctrine cross-sites §4.2)
- Cookie consent scopé root-domain (4 marques, `ap_consent` migration idempotente)
- Reverse proxy Symfony Controller (Option A)
- Doctrine Cas 1 forcé pour don + meeting (§8.3, §8.4)
- DPO validé 2026-07-14 (référence archive interne)
- Alternatives écartées

- [ ] **Step 20.2 — Créer `docs/analytics/posthog-events-parti-renaissance.md`**

Taxonomie MVP 30 events + mapping doctrine cross-sites + owners PO. Sections :
- Doctrine appliquée (nomenclature §2)
- Registry par cluster (Consent, Auth, Adhésion, Don, Meeting, Profil, Newsletter, Pétition)
- Super-properties auto + post-identify
- Follow-ups Phase 1.5

- [ ] **Step 20.3 — Commit**

```bash
git add docs/adrs/posthog-multi-domain.md docs/analytics/posthog-events-parti-renaissance.md
git commit -m "docs(analytics): ADR local posthog-multi-domain + taxonomie posthog-events-parti-renaissance"
```

---

## Task 21: CI workflow lint-posthog-privacy (PHP/Twig/JS)

**Files:**
- Create: `.github/workflows/lint-posthog-privacy.yml`
- Create: `scripts/lint-posthog-privacy.sh`

- [ ] **Step 21.1 — Créer `scripts/lint-posthog-privacy.sh`**

```bash
#!/bin/bash
# Note: PAS de `set -e` — un `git grep` clean (aucun match) retourne exit code 1,
# ce qui tuerait le script prématurément. On accumule les erreurs dans $EXIT à la place.
EXIT=0

# 1. Grep bloquant email clair côté client (JS + Twig + PHP)
if git grep -nE 'posthog\.capture\([^)]*email' -- '*.js' '*.ts' '*.php' '*.twig' 2>/dev/null; then
  echo "❌ Email en clair détecté dans posthog.capture() côté client"
  EXIT=1
fi

if git grep -nE 'posthog\.identify\([^)]*@' -- '*.js' '*.ts' '*.php' '*.twig' 2>/dev/null; then
  echo "❌ Adresse email littérale dans posthog.identify()"
  EXIT=1
fi

# 2. $set.email hors whitelist Cas 2
WHITELIST_EVENTS="newsletter_submitted_server|newsletter_confirmed_server|petition_signed_server"
if git grep -nE '\$set[^)]*email' -- '*.php' '*.twig' 2>/dev/null | grep -vE "$WHITELIST_EVENTS"; then
  echo "❌ \$set.email hors whitelist Cas 2 (autorisé uniquement : $WHITELIST_EVENTS)"
  EXIT=1
fi

# 3. Cohérence enum PHP ↔ dict JS
REGISTRY_PHP="src/Analytics/PostHog/Events/PostHogEventName.php"
REGISTRY_JS="assets/analytics/posthog/posthog-capture.js"
if [ -f "$REGISTRY_PHP" ] && [ -f "$REGISTRY_JS" ]; then
  # Extract cases enum PHP: "case FOO = 'foo';"
  PHP_CASES=$(grep -oE "case [A-Z_]+ = '" "$REGISTRY_PHP" | sed "s/case //" | sed "s/ = '//" | sort)
  # Extract keys JS: "FOO: 'foo'," — support indentation 2 ou 4 espaces (variabilité prettier/eslint)
  JS_KEYS=$(grep -oE "^\s+[A-Z_]+: " "$REGISTRY_JS" | sed 's/://' | tr -d ' ' | sort)

  if [ "$PHP_CASES" != "$JS_KEYS" ]; then
    echo "❌ Enum PHP $REGISTRY_PHP ↔ JS $REGISTRY_JS non aligné :"
    diff <(echo "$PHP_CASES") <(echo "$JS_KEYS") || true
    EXIT=1
  fi
fi

if [ $EXIT -eq 0 ]; then
  echo "✅ Lint privacy PostHog OK"
fi
exit $EXIT
```

```bash
chmod +x scripts/lint-posthog-privacy.sh
```

- [ ] **Step 21.2 — Créer `.github/workflows/lint-posthog-privacy.yml`**

```yaml
name: Lint PostHog Privacy

on:
  pull_request:

jobs:
  privacy-lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0
      - name: Check bypass label
        id: bypass
        run: |
          if echo '${{ toJson(github.event.pull_request.labels) }}' | grep -q 'posthog-privacy-ok'; then
            echo "bypass=true" >> $GITHUB_OUTPUT
          fi
      - name: Run privacy lint
        if: steps.bypass.outputs.bypass != 'true'
        run: bash scripts/lint-posthog-privacy.sh
```

- [ ] **Step 21.3 — Test script localement + commit**

Vérifier qu'un repo clean retourne exit 0 (pas 1 à cause d'un `git grep` sans match) :

```bash
bash scripts/lint-posthog-privacy.sh; echo "Exit: $?"
```

Attendu : `✅ Lint privacy PostHog OK` puis `Exit: 0`. Si `Exit: 1` sur un repo clean, le script a un bug (probablement `set -e` réintroduit).

```bash
git add scripts/lint-posthog-privacy.sh .github/workflows/lint-posthog-privacy.yml
git commit -m "ci(privacy): workflow lint-posthog-privacy PHP/Twig/JS (whitelist Cas 2, enum consistency check)"
```

---

## Task 21.5: Behat scenarios (spec §11.2)

**Files:**
- Create: `features/analytics/consent_banner.feature`
- Create: `features/analytics/multi_domain.feature`
- Create: `features/analytics/ingest_proxy.feature`

- [ ] **Step 21.5.1 — Créer `consent_banner.feature`**

Contenu Gherkin lisible PO :

```gherkin
Feature: Bannière consent PostHog
  Scenario: Bannière affichée si cookie absent (déco)
    Given I visit "https://utilisateur.parti-renaissance.fr/"
    Then I should see "Analyse d'usage anonymisée"
    And the response should have status 200

  Scenario: Bannière cachée si cookie déjà posé (opt-in)
    Given I have cookie "pr_consent" with value "1"
    When I visit "https://utilisateur.parti-renaissance.fr/"
    Then I should not see "Analyse d'usage anonymisée"

  Scenario: Migration idempotente ap_consent
    Given I have cookie "ap_consent" with value "0"
    When I visit "https://utilisateur.attalpresident.fr/"
    Then the cookie "ap_consent" should have value "0"
    And I should not see "Analyse d'usage anonymisée"
```

- [ ] **Step 21.5.2 — Créer `multi_domain.feature`**

```gherkin
Feature: Détection dynamique site
  Scenario Outline: hostname → site correct
    When I visit "https://<hostname>/"
    Then Twig global "posthog_site" should equal "<site>"

    Examples:
      | hostname                            | site               |
      | utilisateur.parti-renaissance.fr    | parti-renaissance  |
      | utilisateur.attalpresident.fr       | attalpresident     |
      | utilisateur.avecgabrielattal.fr     | avecgabrielattal   |
      | utilisateur.nouvellerepublique.fr   | nouvellerepublique |
```

- [ ] **Step 21.5.3 — Créer `ingest_proxy.feature`**

```gherkin
Feature: Reverse proxy /ingest
  Scenario: POST /ingest/e/ forward vers PostHog EU
    When I POST '{"event":"test"}' to "/ingest/e/"
    Then the response should have status 200
    And the response should not have cookie "ph_session"

  Scenario: /ingest/interdit renvoie 404
    When I GET "/ingest/interdit"
    Then the response should have status 404
```

- [ ] **Step 21.5.4 — Run Behat + commit**

```bash
vendor/bin/behat features/analytics/ 2>&1 | tail -10
git add features/analytics/
git commit -m "test(analytics): Behat scenarios (consent_banner + multi_domain + ingest_proxy — spec §11.2)"
```

---

## Task 22: Mount snippet + banner dans base_renaissance + activation

**Files:**
- Modify: `templates/base_renaissance.html.twig`

- [ ] **Step 22.1 — Ajouter snippet dans `{% block analytics %}`**

Éditer `templates/base_renaissance.html.twig`, dans le bloc `{% block analytics %}` L38-60, ajouter **AVANT** Matomo :

```twig
{% block analytics %}
    {{ include('analytics/posthog/_snippet.html.twig') }}

    {# Matomo existant (dual-run 4 sem, PR ultérieure supprime) #}
    {% if app_environment == 'production' %}
        {# existing Matomo code #}
    {% endif %}
{% endblock %}
```

- [ ] **Step 22.2 — Ajouter banner en fin `<body>`**

Éditer `templates/base_renaissance.html.twig`, juste avant `</body>` :

```twig
    {{ include('analytics/posthog/_consent_banner.html.twig') }}
  </body>
</html>
```

- [ ] **Step 22.3 — Verify build + tests globaux**

```bash
pnpm build-dev 2>&1 | tail -3
vendor/bin/phpunit 2>&1 | tail -5
```

Attendu : build OK + tests analytics PHPUnit verts.

- [ ] **Step 22.4 — Commit final + push branche**

```bash
git add templates/base_renaissance.html.twig
git commit -m "feat(analytics): mount PostHog snippet + consent banner dans base_renaissance (activation feature-flag)"
git push -u origin feat/RE-5165-posthog-web
```

- [ ] **Step 22.5 — Open PR**

```bash
gh pr create --repo parti-renaissance/espace-adherent --base master --head feat/RE-5165-posthog-web \
  --title "feat(analytics): RE-5165 intégration PostHog multi-domaine (22 commits atomiques, dual-run Matomo 4 sem)" \
  --body "Voir spec crm-integrations:docs/superpowers/specs/2026-07-14-espace-adherent-posthog-multi-domain-design.md + ADR local docs/adrs/posthog-multi-domain.md. Feature flag POSTHOG_ENABLED=false par défaut, activation staging puis prod après validation des 4 hostnames."
```

- [ ] **Step 22.6 — Activation prod post-merge (Fontaine)**

Après merge PR, activation en 3 étapes :

**1. Staging** : toggle env var + smoke test manuel.

```bash
# Set env var côté hébergement staging (Scaleway / Cloud Run / config appropriée)
POSTHOG_ENABLED=true
POSTHOG_API_KEY=phc_<key_staging>
```

Redéployer staging. Verify les 4 hostnames staging :

```bash
for h in utilisateur.parti-renaissance.fr utilisateur.attalpresident.fr utilisateur.avecgabrielattal.fr utilisateur.nouvellerepublique.fr; do
  curl -sI "https://staging-${h}/" | grep -i "content-security-policy\|content-type"
  curl -s  "https://staging-${h}/" | grep -o "__POSTHOG_CONFIG__" | head -1
done
```

Attendu : chaque page contient `window.__POSTHOG_CONFIG__` avec `enabled: true`.

**2. Vérification PostHog UI staging** : ouvrir https://eu.posthog.com projet Renaissance, filter events sur les 30 dernières minutes, vérifier que les events staging arrivent bien avec `site=parti-renaissance/attalpresident/...`, `platform=web`, `environment=staging`.

**3. Prod** : après 24h d'observation staging clean, toggle env var prod. Verify 3 métriques :
- `login_succeeded` fire sur ≥ 80 % des sessions authentifiées (KPI bridge auth ↔ consent).
- 0 erreur Sentry lié `PostHogService` ou `IngestProxyController` sur 24h.
- PostHog UI : les 4 marques `site=X` reçoivent des events (au minimum `$pageview` + 1 event métier).

**Rollback** : toggle `POSTHOG_ENABLED=false` + redeploy. Aucun impact code, feature flag pur.

---

## Self-Review

Vérifications inline :

**1. Spec coverage** — chaque section spec pointe vers une task :
- §2.1 Détection dynamique site → Task 3
- §2.2 Cookies marque + migration → Task 5
- §2.3 Reverse proxy Option A → Task 6
- §2.4 Doctrine identité + salts → Task 4
- §2.5 Doctrine email 2 cas → Tasks 15-19 (mapping par cluster)
- §2.6 Consent DPO validé → Tasks 5, 12
- §2.7 App env + release → Task 2
- §2.8 Skip ATT → n/a (web)
- §2.9 arbitrages review → tous intégrés
- §3 Architecture modules → Tasks 3-13
- §4 SiteDetector → Task 3
- §5 Cookie consent → Tasks 5, 11, 12
- §6 Reverse proxy → Task 6
- §7 Modèle identité → Tasks 4, 8, 9
- §8 Instrumentation 7 clusters → Tasks 13-19
- §9 Registry events → Task 7 + JS Task 10
- §10 Super-properties → Task 8
- §11 Tests → chaque Task TDD (**gap coverage 90% Phase 1, cf. bloc "Gaps coverage" ci-dessous**)
- §12 CI/CD → Task 21
- §13 Séquence commits → 1:1 avec Tasks 0-22 (22 tasks bissect-friendly)
- §14 Documentation code → JSDoc/PHPdoc headers dans chaque Task
- §15 Review humaine → checkpoints dans PR body Task 22.5
- §16 Deploy checklist → à intégrer dans PR body Task 22.5
- §17 Divergences doctrine → follow-up spec cross-sites §4.2 update (mini-PR crm-integrations séparée)
- §18 Follow-ups → tracés dans ADR Task 20
- §19 Annexes → dans docs Task 20

**2. Placeholder scan** — recheck : aucun "TBD", "TODO", "à définir" caché sauf les zones explicitement "reprise dev" (ex: Task 15.3 callback Paybox — dépend du code réel PaymentController, le path exact à identifier par le dev). Ces zones sont typées "reprise dev" pas "placeholder oublié".

**3. Type consistency** — helpers publics cohérents :
- `SiteContext::getSite(): string` — Task 3 def + réutilisé Tasks 4, 5, 8, 9, 12
- `HashEmailService::hash(string): string` — Task 4 def + réutilisé Tasks 8, 9, 19
- `ConsentCookieHelper::read/write` — Task 5 def + réutilisé Task 12
- `PostHogService::captureServerSide(EventName, array, ?Adherent)` — Task 8 def + réutilisé Tasks 13-19
- `PostHogEventName::*` cases — Task 7 def + réutilisé Tasks 8, 10, 13-19
- `PostHogTwigExtension` globals — Task 9 def + réutilisé Task 11

Cohérence OK. Plan complet.

**Gaps coverage 90 % Phase 1** :
- Non couvert PHPUnit : `SiteContextListener` (~10 LOC), `SiteContext` (~15 LOC), `ConsentSettingsController` (~40 LOC), `AbstractPayload` (~5 LOC) — total ~70 LOC sur ~500 → couverture PHP **~86 % réelle Phase 1**.
- Non couvert : `posthog-init.js`, `posthog-capture.js`, `posthog-consent.js`, `posthog-identify.js` (~200 LOC JS total).

**Actions Phase 1.5** (post-merge) :
- Ajouter tests PHPUnit pour SiteContextListener + ConsentSettingsController (fonctionnels + integration).
- Setup Jest ou Vitest côté `assets/analytics/posthog/` pour tester les 4 modules JS.
- Objectif coverage 90 % PHP + 80 % JS Phase 1.5.

Amender la spec parente §11.1 par mini-PR crm-integrations follow-up pour reformuler « 90 % `src/Analytics/PostHog/` » → « 80 % Phase 1, 90 % Phase 1.5 après tests JS ».

---

## Execution Handoff

**Plan complet et sauvegardé dans `docs/superpowers/plans/2026-07-14-espace-adherent-posthog-multi-domain.md`.**

Deux options d'exécution :

**1. Subagent-Driven (recommandé)** — je dispatch un sous-agent frais par task, review entre tasks, itération rapide, contexte principal préservé.

**2. Inline Execution** — j'exécute les tasks dans cette session avec checkpoints toutes les ~5 tasks.

Quel approche ?
