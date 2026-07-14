// assets/analytics/posthog/posthog-init.js
// Boot du SDK PostHog. Appelé depuis bootstrap.js après Sentry init.
// Gate par window.__POSTHOG_CONFIG__ injecté par le snippet Twig server-rendered.

import posthog from 'posthog-js';
import { applyConsentToSdk } from './posthog-consent';
import { applyIdentifyPayload } from './posthog-identify';

export function initPostHog() {
  const config = window.__POSTHOG_CONFIG__;
  if (!config || !config.enabled || !config.apiKey) {
    return; // Feature flag off ou config absente
  }

  posthog.init(config.apiKey, {
    api_host: '/ingest', // Reverse proxy first-party (Symfony IngestProxyController)
    ui_host: 'https://eu.posthog.com',
    autocapture: {
      captureLifecycleEvents: true,
      captureScreens: true,
      captureTouches: false,
    },
    capture_pageview: true,
    disable_session_recording: true, // Session Replay OFF Phase 1
    opt_out_capturing_by_default: true, // Consent explicit obligatoire
    persistence: 'cookie',
    loaded: (ph) => {
      // Register super-properties auto (server-injected)
      if (config.superProperties) {
        ph.register(config.superProperties);
      }
      // Apply consent state initial (server-detected)
      if (config.consent === true) {
        ph.opt_in_capturing();
      } else if (config.consent === false) {
        ph.opt_out_capturing();
      }
      // Apply identify si user connecté (server-injected)
      applyIdentifyPayload();
    },
  });

  // Bridge : le banner Alpine.js appelle ce helper après clic Accepter/Refuser.
  window.__POSTHOG_APPLY_CONSENT__ = (granted) => applyConsentToSdk(granted);
}
