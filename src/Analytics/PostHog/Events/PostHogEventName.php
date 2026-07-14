<?php

declare(strict_types=1);

namespace App\Analytics\PostHog\Events;

/**
 * Enum unique des 30 events custom PostHog Renaissance web.
 * Cohérent avec docs/analytics/posthog-integration/spec.md §9.1 + doc taxonomie
 * (Task 20 posthog-events-parti-renaissance.md).
 * Miroir JS dans assets/analytics/posthog/posthog-capture.js (POSTHOG_EVENTS dict).
 * Check consistency PHP↔JS via workflow lint-posthog-privacy (Task 21).
 */
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

    // Newsletter (Cas 2 server-side, $set.email autorisé)
    case NEWSLETTER_SUBMITTED_SERVER = 'newsletter_submitted_server';
    case NEWSLETTER_CONFIRMED_SERVER = 'newsletter_confirmed_server';

    // Pétition (Cas 2 server-side)
    case PETITION_SIGNED_SERVER = 'petition_signed_server';
}
