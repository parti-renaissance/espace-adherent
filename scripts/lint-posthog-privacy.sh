#!/bin/bash
# scripts/lint-posthog-privacy.sh
# Grep bloquant : email en clair côté client PostHog.
# Bypass label PR : posthog-privacy-ok (avec justification commentaire).
# PAS de set -e (git grep clean = exit 1, tuerait le script).

EXIT=0

# 1. Email en clair côté client (JS + Twig + PHP)
if git grep -nE 'posthog\.capture\([^)]*email' -- '*.js' '*.ts' '*.php' '*.twig' 2>/dev/null; then
  echo "❌ Email en clair détecté dans posthog.capture() côté client"
  EXIT=1
fi

# 2. Adresse email littérale dans identify()
if git grep -nE 'posthog\.identify\([^)]*@' -- '*.js' '*.ts' '*.php' '*.twig' 2>/dev/null; then
  echo "❌ Adresse email littérale dans posthog.identify()"
  EXIT=1
fi

# 3. $set.email hors whitelist Cas 2
# On exclut les lignes de commentaires (// … ou # …) qui documentent l'absence de $set.email
WHITELIST_EVENTS="newsletter_submitted_server|newsletter_confirmed_server|petition_signed_server"
SET_EMAIL_OCCURRENCES=$(git grep -nE "'\''email'\''[[:space:]]*=>" -- '*.php' '*.twig' 2>/dev/null | grep -vE "^\S+:\s*(//|#|/\*|\*)" || true)
if [ -n "$SET_EMAIL_OCCURRENCES" ]; then
  VIOLATIONS=$(echo "$SET_EMAIL_OCCURRENCES" | grep -vE "$WHITELIST_EVENTS" || true)
  if [ -n "$VIOLATIONS" ]; then
    echo "❌ \$set.email hors whitelist Cas 2 (autorisé uniquement : newsletter_submitted_server, newsletter_confirmed_server, petition_signed_server)"
    echo "$VIOLATIONS"
    EXIT=1
  fi
fi

# 4. Cohérence enum PHP ↔ dict JS : tous les cases PHP doivent exister dans le dict JS
# (le JS peut avoir des events client-only absents de l'enum PHP serveur — c'est normal)
REGISTRY_PHP="src/Analytics/PostHog/Events/PostHogEventName.php"
REGISTRY_JS="assets/analytics/posthog/posthog-capture.js"
if [ -f "$REGISTRY_PHP" ] && [ -f "$REGISTRY_JS" ]; then
  # Extract cases enum PHP: "case FOO = 'foo';"
  PHP_CASES=$(grep -oE "case [A-Z_]+ = '" "$REGISTRY_PHP" 2>/dev/null | sed "s/case //" | sed "s/ = '//" | sort)
  # Extract keys JS: "  FOO: 'foo'," (2 ou 4 space indentation)
  JS_KEYS=$(grep -oE "^\s+[A-Z_]+: " "$REGISTRY_JS" 2>/dev/null | sed 's/://' | tr -d ' ' | sort)

  # Check that every PHP case is present in JS keys
  MISSING=$(comm -23 <(echo "$PHP_CASES") <(echo "$JS_KEYS"))
  if [ -n "$MISSING" ]; then
    echo "❌ Cases PHP absents du dict JS $REGISTRY_JS :"
    echo "$MISSING"
    diff <(echo "$PHP_CASES") <(echo "$JS_KEYS") || true
    EXIT=1
  fi
fi

if [ $EXIT -eq 0 ]; then
  echo "✅ Lint privacy PostHog OK"
fi
exit $EXIT
