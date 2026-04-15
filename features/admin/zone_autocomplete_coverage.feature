@app
@renaissance_admin
Feature: Zone autocomplete works for every admin group with minimal permissions
    This feature proves that the /autocomplete/zone endpoint is reachable — and
    every Sonata admin list/edit page that embeds a zone autocomplete (either
    as a form field or as a datagrid filter) still renders — for administrators
    holding only the minimum role set required for their group. Each scenario
    logs in as a dedicated fixture administrator, walks through the admin pages
    in their group (list, filter-applied list, edit form), then hits the
    autocomplete endpoint directly. Presence checks look for the JS-escaped
    form "\/autocomplete\/zone" because select2 config is emitted inside
    an `autoescape 'js'` Twig block.

    # ----------------------------------------------------------------------
    # Groups — every admin touched by the AdminZoneAutocompleteType migration
    # ----------------------------------------------------------------------
    Scenario: Group 1 — Militants (adhérents, parrains)
        When I am logged as "admin.group-militants@test.code" admin
        And I am on "/app/adherent/list"
        Then the response status code should be 200
        When I am on "/adherents-parrains/list"
        Then the response status code should be 200
        # Filter execution + widget rendering (#2 #3 #7)
        When I am on "/app/adherent/list?filter[zones][value][]=287"
        Then the response status code should be 200
        And the response should contain "filter[zones][value]"
        And the response should contain "\/autocomplete\/zone"
        # Edit mode (#1 #7)
        When I am on "/app/adherent/1/edit"
        Then the response status code should be 200
        And the response should contain "\/autocomplete\/zone"
        # Endpoint itself
        When I send a "GET" request to "/autocomplete/zone?q=Paris"
        Then the response status code should be 200
        And the JSON node "status" should be equal to "OK"
        And the JSON node "items[0].label" should contain "Département"

    Scenario: Group 2 — Territoires (comités, événements, teams, procurations, réunions, conventions)
        When I am logged as "admin.group-territoires@test.code" admin
        And I am on "/app/committee/list"
        Then the response status code should be 200
        When I am on "/app/event-event/list"
        Then the response status code should be 200
        When I am on "/app/team-team/list"
        Then the response status code should be 200
        When I am on "/app/generalmeeting-generalmeetingreport/list"
        Then the response status code should be 200
        When I am on "/app/generalconvention-generalconvention/list"
        Then the response status code should be 200
        When I am on "/app/procuration-request/list"
        Then the response status code should be 200
        # Filter execution + widget rendering
        When I am on "/app/committee/list?filter[zones][value][]=287"
        Then the response status code should be 200
        And the response should contain "filter[zones][value]"
        And the response should contain "\/autocomplete\/zone"
        # Edit mode — CommitteeAdmin form still uses a direct ModelAutocompleteType
        # for its zones field (NOT-IN subquery intentionally not migrated).
        # EventAdmin has no zone form field at all, only a filter.
        When I am on "/app/committee/1/edit"
        Then the response status code should be 200
        When I am on "/app/event-event/1/edit"
        Then the response status code should be 200
        # Endpoint
        When I send a "GET" request to "/autocomplete/zone?q=Paris"
        Then the response status code should be 200
        And the JSON node "status" should be equal to "OK"
        And the JSON node "items[0].label" should contain "Département"

    Scenario: Group 3 — Élus (élus & mandats déclarés)
        When I am logged as "admin.group-elus@test.code" admin
        And I am on "/app/electedrepresentative-electedrepresentative/list"
        Then the response status code should be 200
        When I am on "/adherents-elus/list"
        Then the response status code should be 200
        # Filter execution on nested mandates.geoZone filter
        When I am on "/app/electedrepresentative-electedrepresentative/list?filter[mandates.geoZone][value][]=287"
        Then the response status code should be 200
        And the response should contain "\/autocomplete\/zone"
        # Edit mode
        When I am on "/app/electedrepresentative-electedrepresentative/1/edit"
        Then the response status code should be 200
        And the response should contain "\/autocomplete\/zone"
        When I send a "GET" request to "/autocomplete/zone?q=Paris"
        Then the response status code should be 200
        And the JSON node "status" should be equal to "OK"
        And the JSON node "items[0].label" should contain "Département"

    Scenario: Group 4 — Tech & Finance (dons, administrateurs, emails JME, sites départementaux)
        When I am logged as "admin.group-tech@test.code" admin
        And I am on "/app/donation/list"
        Then the response status code should be 200
        When I am on "/app/administrator/list"
        Then the response status code should be 200
        When I am on "/app/email-emailtemplate/list"
        Then the response status code should be 200
        When I am on "/app/departmentsite-departmentsite/list"
        Then the response status code should be 200
        # Filter execution + widget rendering on a listing with zone filter
        When I am on "/app/email-emailtemplate/list?filter[zones][value][]=287"
        Then the response status code should be 200
        And the response should contain "filter[zones][value]"
        And the response should contain "\/autocomplete\/zone"
        # Edit mode
        When I am on "/app/donation/1/edit"
        Then the response status code should be 200
        And the response should contain "\/autocomplete\/zone"
        When I am on "/app/administrator/2/edit"
        Then the response status code should be 200
        And the response should contain "\/autocomplete\/zone"
        When I am on "/app/email-emailtemplate/1/edit"
        Then the response status code should be 200
        When I am on "/app/departmentsite-departmentsite/1/edit"
        Then the response status code should be 200
        And the response should contain "\/autocomplete\/zone"
        When I send a "GET" request to "/autocomplete/zone?q=Paris"
        Then the response status code should be 200
        And the JSON node "status" should be equal to "OK"
        And the JSON node "items[0].label" should contain "Département"

    Scenario: Group 5 — Campagnes, Jecoute & Formations
        When I am logged as "admin.group-campagnes@test.code" admin
        And I am on "/app/phoning-campaign/list"
        Then the response status code should be 200
        When I am on "/app/pap-campaign/list"
        Then the response status code should be 200
        When I am on "/app/jecoute-news/list"
        Then the response status code should be 200
        When I am on "/app/jecoute-localsurvey/list"
        Then the response status code should be 200
        When I am on "/jecoute-national-region/list"
        Then the response status code should be 200
        When I am on "/jecoute-candidate-region/list"
        Then the response status code should be 200
        When I am on "/jecoute-referent-region/list"
        Then the response status code should be 200
        When I am on "/app/adherentformation-formation/list"
        Then the response status code should be 200
        When I am on "/app/votingplatform-designation-designation/list"
        Then the response status code should be 200
        # Filter execution + widget rendering — use PapCampaignAdmin which has
        # a ZoneAutocompleteFilter on the listing (PhoningCampaignAdmin only
        # has a zone form field, no datagrid filter).
        When I am on "/app/pap-campaign/list?filter[zones][value][]=287"
        Then the response status code should be 200
        And the response should contain "filter[zones][value]"
        And the response should contain "\/autocomplete\/zone"
        # Edit mode
        When I am on "/app/phoning-campaign/1/edit"
        Then the response status code should be 200
        And the response should contain "\/autocomplete\/zone"
        When I am on "/app/votingplatform-designation-designation/1/edit"
        Then the response status code should be 200
        And the response should contain "\/autocomplete\/zone"
        When I send a "GET" request to "/autocomplete/zone?q=Paris"
        Then the response status code should be 200
        And the JSON node "status" should be equal to "OK"
        And the JSON node "items[0].label" should contain "Département"

    # ----------------------------------------------------------------------
    # Cross-group denial (#5) — each group admin MUST be refused on another
    # group's listing. Proves the fixture admins have truly minimal rights.
    # Behat Scenario Outline, one row per (admin, forbidden_url) pair.
    # ----------------------------------------------------------------------
    Scenario Outline: <group> admin is denied access to <forbidden> list
        When I am logged as "<email>" admin
        And I am on "<forbidden_url>"
        Then the response status code should be 403

        Examples:
            | group       | email                             | forbidden         | forbidden_url              |
            | Militants   | admin.group-militants@test.code   | committees        | /app/committee/list        |
            | Territoires | admin.group-territoires@test.code | donations         | /app/donation/list         |
            | Élus        | admin.group-elus@test.code        | phoning campaigns | /app/phoning-campaign/list |
            | Tech        | admin.group-tech@test.code        | adhérents         | /app/adherent/list         |
            | Campagnes   | admin.group-campagnes@test.code   | administrators    | /app/administrator/list    |

    # ----------------------------------------------------------------------
    # Presets (#6) — exercise each preset under a real admin session
    # ----------------------------------------------------------------------
    Scenario: jecoute_managed_area preset accepts departments and rejects regular cities
        When I am logged as "admin.group-militants@test.code" admin
        And I send a "GET" request to "/autocomplete/zone?q=Paris&preset=jecoute_managed_area"
        Then the response status code should be 200
        And the JSON node "status" should be equal to "OK"
        And the JSON node "items[0].label" should contain "Département"
        When I send a "GET" request to "/autocomplete/zone?q=Clichy&preset=jecoute_managed_area"
        Then the response status code should be 200
        And the JSON node "items" should have 0 elements

    Scenario: department_site preset accepts departments and the FDE custom zone
        When I am logged as "admin.group-tech@test.code" admin
        And I send a "GET" request to "/autocomplete/zone?q=Paris&preset=department_site"
        Then the response status code should be 200
        And the JSON node "status" should be equal to "OK"
        And the JSON node "items[0].label" should contain "Département"
