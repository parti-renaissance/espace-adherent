// assets/analytics/posthog/posthog-capture.js
// Miroir enum PHP PostHogEventName. Doit rester byte-identique à
// src/Analytics/PostHog/Events/PostHogEventName.php.
// Check de cohérence en CI via scripts/lint-posthog-privacy.sh (Task 21).

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
  NEWSLETTER_SUBMITTED_SERVER: 'newsletter_submitted_server',
  NEWSLETTER_CONFIRMED_SERVER: 'newsletter_confirmed_server',
  PETITION_SIGNED_SERVER: 'petition_signed_server',
});

export function capture(eventName, properties = {}) {
  if (!Object.values(POSTHOG_EVENTS).includes(eventName)) {
    // eslint-disable-next-line no-console
    console.warn(`[posthog] Unknown event: ${eventName}`);
    return;
  }
  posthog.capture(eventName, properties);
}
