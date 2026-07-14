# Taxonomie PostHog — espace-adherent (site principal Renaissance)

Source de vérité de la taxonomie PostHog pour espace-adherent (backend Symfony
servant 4 marques : parti-renaissance, attalpresident, avecgabrielattal,
nouvellerepublique). Owner : PO parti-renaissance + Data team.

## Doctrine

- Nomenclature commune Renaissance (`crm-integrations/docs/posthog-nomenclature-globale.md`)
- Super-properties auto sur chaque event (site, platform=web, environment, deploy_sha, deploy_version, locale, is_bot)
- Cas 1 doctrine email (majorité) : aucun email en clair côté client PostHog
- Cas 2 (newsletter + pétition) : `$set.email` server-side autorisé (whitelist grep CI)
- Bypass label PR : `posthog-privacy-ok` (avec justification)

## Registry (30 events custom)

### Consent
- `consent_granted` — `{source: banner|settings|implicit_login, consent_version}`
- `consent_refused` — `{source: banner|settings, consent_version}`
- `consent_dismissed` — `{source: banner, consent_version}`

### Auth (Symfony 7.4 firewall events)
- `login_succeeded` — `{method: form|magic-link|oauth}`
- `login_failed` — `{reason: bad_credentials|unknown}`
- `logout_completed` — `{}`
- `password_reset_requested` — `{}`
- `password_reset_completed` — `{}`
- `magic_link_requested` — `{}`
- `magic_link_login_succeeded` — `{method: magic-link}`

### Adhésion
- `adhesion_started` — `{has_referrer_pid: bool}`
- `adhesion_form_submitted` — `{step: info_personnelle}`
- `adhesion_payment_initiated` — `{payment_provider: paybox}`
- `adhesion_completed` — `{amount_eur, payment_method, is_first_adhesion}`
- `adhesion_payment_failed` — `{reason}`
- `adhesion_finish_page_viewed` — `{}`

### Don (Cas 1 forcé — donor_type: user|anonymous, jamais $set.email)
- `donation_started` — `{is_grand_donateur: bool}`
- `donation_form_submitted` — `{amount_eur, is_recurring}`
- `donation_payment_initiated` — `{payment_provider: paybox}`
- `donation_completed` — `{amount_eur, is_recurring, donor_type: user|anonymous}`
- `donation_payment_failed` — `{reason}`

### Meeting national (Cas 1 forcé — jointure DWH server-only)
- `national_event_page_viewed` — `{event_slug, event_uuid, has_referrer_pid}`
- `national_event_inscription_submitted` — `{event_uuid, is_paid}`
- `national_event_inscription_confirmed` — `{inscription_uuid}`
- `national_event_payment_completed` — `{amount_eur, payment_method}`
- `national_event_inscription_edited` — `{event_uuid, inscription_uuid}`

### Profil
- `profile_page_viewed` — `{}`
- `profile_updated` — `{fields_changed: list<string>}` (**noms techniques uniquement, jamais valeurs**)

### Newsletter (Cas 2 server-side)
- `newsletter_submitted_server` — `{postal_code_prefix, source_page, $set: {email}}`
- `newsletter_confirmed_server` — `{$set: {email}}`

### Pétition (Cas 2 server-side)
- `petition_signed_server` — `{petition_slug, $set: {email}}` *(petition_uuid en Phase 1.5)*

## Super-properties auto

Injectées via `PostHogService::buildSuperProperties()` sur tous les events server-side :
- `site` : `attalpresident|parti-renaissance|avecgabrielattal|nouvellerepublique` (SiteDetector)
- `platform` : `web` (constante)
- `environment` : depuis `APP_ENVIRONMENT` env var
- `deploy_sha` : 7 chars git SHA (fallback `local` si vide)
- `deploy_version` : semver tag (fallback `unknown` si vide)
- `locale` : `fr-FR`
- `is_bot` : `false`

Autocapture PostHog JS : `$pageview`, `$autocapture` clicks/liens, lifecycle events.

## Post-identify (`$set` + `$set_once`)

Injectés via `PostHogTwigExtension::identifyPayload($user)` si `app.user` :
- `$set.public_id` (Adherent `PublicIdTrait`, 7 chars, exposé serialization)
- `$set_once.identified_from_site` (site marque du 1er identify)
- `$set_once.identified_at` (ISO 8601)

## Cas 2 whitelist grep CI

Events autorisés à porter `$set.email` (server-side uniquement) :
- `newsletter_submitted_server`
- `newsletter_confirmed_server`
- `petition_signed_server`

Toute autre occurrence de `$set.email` côté client OU server = **BLOCK CI** via
`scripts/lint-posthog-privacy.sh` (Task 21). Bypass label PR : `posthog-privacy-ok`.

## Follow-ups Phase 1.5

- Wire form_submit_* sur les 7 forms restants (audit Fontaine)
- Ajouter `payment_method` sur PaymentStatusController (colonne DB à créer)
- Refactoriser `detectChangedFields()` ProfileController pour décomposer `post_address` composite
- Ajouter `petition_uuid` en payload PETITION_SIGNED_SERVER (relation hydratée)
- Tests JS Jest/Vitest sur assets/analytics/posthog/*.js (coverage 90%)
