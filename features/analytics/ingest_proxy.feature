Feature: Reverse proxy /ingest vers PostHog EU
    Symfony proxifie /ingest/{path} vers eu.i.posthog.com pour contourner
    les blocages Safari ITP / Firefox ETP (first-party cookies).
    Whitelist paths PostHog v1.180+ : e|decide|s|static|batch|array|flags|surveys|warehouse.

    Scenario: POST /ingest/e/ forward vers PostHog EU
        When je fais un POST '{"event":"test"}' à "/ingest/e/" sur "https://utilisateur.parti-renaissance.fr"
        Then la réponse devrait avoir le status 200
        And la réponse ne devrait pas avoir le cookie "ph_session"

    Scenario: /ingest/interdit renvoie 404
        When je fais un GET "/ingest/interdit" sur "https://utilisateur.parti-renaissance.fr"
        Then la réponse devrait avoir le status 404

    Scenario: /ingest/flags/ dans la whitelist
        When je fais un POST à "/ingest/flags/" sur "https://utilisateur.parti-renaissance.fr"
        Then la réponse ne devrait pas avoir le status 404
