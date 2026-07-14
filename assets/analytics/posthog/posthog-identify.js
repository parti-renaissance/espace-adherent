// assets/analytics/posthog/posthog-identify.js
// Applique le payload identify server-generated (rendu inline par _snippet.html.twig).

import posthog from 'posthog-js';

export function applyIdentifyPayload() {
  if (!window.__POSTHOG_IDENTIFY__) return;
  const { distinct_id, $set, $set_once } = window.__POSTHOG_IDENTIFY__;
  posthog.identify(distinct_id, { $set, $set_once });
}
