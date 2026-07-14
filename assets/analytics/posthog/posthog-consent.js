// assets/analytics/posthog/posthog-consent.js
// Lecture/écriture du cookie consent scopé root-domain marque.
// Bridge banner ↔ SDK PostHog opt-in/opt-out.

import posthog from 'posthog-js';

const COOKIE_MAX_AGE_SECONDS = 34128000; // ~13 mois CNIL max

export function readConsent(cookieName) {
  const match = document.cookie.match(new RegExp(`(?:^|; )${cookieName}=(0|1)`));
  return match ? match[1] === '1' : null;
}

export function writeConsent(cookieName, cookieDomain, granted) {
  const expires = new Date(Date.now() + COOKIE_MAX_AGE_SECONDS * 1000).toUTCString();
  const value = granted ? '1' : '0';
  // M2 review Opus : skip `secure` en HTTP local (dev)
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
