@app
@renaissance_admin
Feature: Admin — créer une inscription à un NationalEvent depuis Sonata
    This feature covers the create flow on NationalEventInscriptionsAdmin
    (and its JEM subclass). The flow is 2 steps:
    1. /create without ?event= renders a mini-form that asks the admin to
    pick a NationalEvent (filtered by allowed/forbidden types of the
    current admin).
    2. /create?event=<id> renders the full inscription form with package
    fields dynamically built from the chosen event's packageConfig.
    The event query parameter survives the POST through Sonata's
    configurePersistentParameters mechanism (no JS, no hidden field).

    Note: scenarios that POST the step-1 mini-form and then assert on the
    step-2 form (or persist a full inscription) are intentionally NOT covered
    here — they need the dynamically resolved event id and the package fields
    coming from packageConfig. Those invariants are covered by the unit/handler
    test layer; this Behat feature smokes the high-level Sonata wiring.

    Background:
        When I am logged as "superadmin@en-marche-dev.fr" admin

    Scenario: Step 1 — GET /create renders the event-choice mini-form
        When I am on "/app/nationalevent-eventinscription/create"
        Then the response status code should be 200
        And the response should contain "Créer une inscription — étape 1/2"
        And the response should contain "Choisir un événement"
        And the response should contain "Continuer"

    Scenario: Step 1 — JEM admin only lists JEM events in the mini-form
        # JEMNationalEventInscriptionsAdmin restricts allowedEventTypes to JEM.
        # The EntityType query_builder filters accordingly.
        When I am on "/meetings-jem/inscriptions/create"
        Then the response status code should be 200
        And the response should contain "Créer une inscription — étape 1/2"
        And the response should contain "Event JEM"
        And the response should not contain "Event national 1"
        And the response should not contain "Campus"
        And the response should not contain "Meeting NRP"

    Scenario: Step 1 — base admin excludes JEM events from the mini-form
        # The base admin forbids NationalEventTypeEnum::JEM.
        When I am on "/app/nationalevent-eventinscription/create"
        Then the response status code should be 200
        And the response should not contain "Event JEM"

    Scenario: Step 2 — GET /create with an unknown event id redirects with a flash error
        # createAction pre-validates the event id before delegating to Sonata's
        # standard flow. Unknown or forbidden events trigger a flash + redirect
        # back to step 1 (better UX than a raw 404). The admin profile auto-follows
        # redirects, so we land directly on the step-1 page carrying the flash.
        When I am on "/app/nationalevent-eventinscription/create?event=999999"
        Then the response status code should be 200
        And the response should contain "L'événement sélectionné est introuvable"

    Scenario: Step 2 — JEM admin with a non-JEM event id redirects with a flash error
        # Defense in depth on the JEM admin: forbidden event types trigger the
        # same flash + redirect as an unknown id.
        When I am on "/meetings-jem/inscriptions/create?event=1"
        Then the response status code should be 200
        And the response should contain "L'événement sélectionné est introuvable"

    Scenario: Sub-admin user without create permission is denied on Step 1
        # Defense in depth: createAction calls $this->admin->checkAccess('create')
        # before any rendering, so an admin without the CREATE grant gets 403.
        When I am logged as "admin.group-territoires@test.code" admin
        And I am on "/app/nationalevent-eventinscription/create"
        Then the response status code should be 403
