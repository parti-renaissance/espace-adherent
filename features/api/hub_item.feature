@api
@renaissance_api
Feature:
    In order to display a unified feed of events and actions
    As a client of the mobile/web apps
    I should be able to call the hub-item aggregation endpoint

    Scenario: As a logged-in user I can filter hub items by zone
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/hub-item?zone=75&page_size=300"
        Then the response status code should be 200
        And the JSON nodes should match:
            | metadata.total_items    | 14  |
            | metadata.items_per_page | 300 |
            | metadata.count          | 14  |
            | metadata.current_page   | 1   |
            | metadata.last_page      | 1   |

    Scenario: As a logged-in user I can filter hub items by bounding box
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/hub-item?bbox[ne][lat]=49&bbox[ne][lng]=3&bbox[sw][lat]=48&bbox[sw][lng]=2&page_size=300"
        Then the response status code should be 200
        # total_items varies between runs because Action fixtures pick random coordinates
        And the JSON node "metadata.items_per_page" should be equal to "300"
        And the JSON node "metadata.total_items" should match "@integer@.greaterThan(0)"

    Scenario: As a logged-in user I can filter hub items by beginAt date — far future returns zero items
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/hub-item?beginAt[strictly_after]=2050-01-01&page_size=300"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 0,
                    "items_per_page": 300,
                    "count": 0,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": []
            }
            """

    Scenario: beginAt[strictly_before] in the past returns zero items
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/hub-item?beginAt[strictly_before]=2000-01-01&page_size=300"
        Then the response status code should be 200
        And the JSON node "metadata.total_items" should be equal to "0"

    Scenario: beginAt[before] in the past returns zero items
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/hub-item?beginAt[before]=2000-01-01&page_size=300"
        Then the response status code should be 200
        And the JSON node "metadata.total_items" should be equal to "0"

    Scenario: beginAt[after] in the past keeps every item in zone 92
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/hub-item?zone=92&beginAt[after]=2000-01-01&page_size=300"
        Then the response status code should be 200
        And the JSON node "metadata.total_items" should be equal to "58"

    Scenario: As a logged-in user I get hub items sorted with distance when lat/lng provided
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/hub-item?lat=48.866667&lng=2.333333&page_size=10"
        Then the response status code should be 200
        And the JSON nodes should match:
            | metadata.items_per_page | 10 |

    Scenario: page_size is capped at 300
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/hub-item?page_size=999"
        Then the response status code should be 200
        And the JSON node "metadata.items_per_page" should be equal to "300"

    Scenario: Default pagination is 100 items per page
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/hub-item"
        Then the response status code should be 200
        And the JSON node "metadata.items_per_page" should be equal to "100"

    Scenario: editable falls back to "all my scopes with ACTIONS feature" on action items when no ?scope= is passed
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        And I send a "POST" request to "/api/v3/actions" with body:
            """
            {
                "type": "pap",
                "date": "2099-06-01 10:00:00",
                "description": "<p>desc</p>",
                "post_address": {
                    "address": "92 bd Victor Hugo",
                    "postal_code": "92110",
                    "city_name": "Clichy",
                    "country": "FR"
                }
            }
            """
        Then the response status code should be 201
        When I send a "GET" request to "/api/v3/hub-item?beginAt[after]=2099-01-01"
        Then the response status code should be 200
        And the JSON node "metadata.total_items" should be equal to "1"
        And the JSON node "items[0].type" should be equal to "action"
        And the JSON node "items[0].editable" should be equal to "true"

    # Full payload validation for the Event branch of the polymorphic response.
    # The Action branch is validated by HubItemViewNormalizerTest (unit) because Action
    # fixtures use random UUIDs/coordinates which would make a hardcoded Behat payload flaky.
    Scenario: Full payload — top events in zone 92 expose the canonical Event shape
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/hub-item?zone=92&page_size=2"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 58,
                    "items_per_page": 2,
                    "count": 2,
                    "current_page": 1,
                    "last_page": 29
                },
                "items": [
                    {
                        "type": "event",
                        "uuid": "5cab27a7-dbb3-4347-9781-566dad1b9eb5",
                        "name": "Nouvel événement online",
                        "slug": "@string@-nouvel-evenement-online",
                        "time_zone": "Europe/Paris",
                        "begin_at": "@string@.isDateTime()",
                        "finish_at": "@string@.isDateTime()",
                        "participants_count": 0,
                        "is_national": false,
                        "status": "SCHEDULED",
                        "capacity": 50,
                        "visio_url": null,
                        "mode": "online",
                        "category": null,
                        "post_address": {
                            "address": "47 rue Martre",
                            "postal_code": "92110",
                            "city": "92110-92024",
                            "city_name": "Clichy",
                            "country": "FR",
                            "latitude": 48.9016,
                            "longitude": 2.305268
                        },
                        "visibility": "public",
                        "live_url": null,
                        "hidden": false,
                        "pinned": true,
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()",
                        "author": {
                            "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                            "first_name": "Referent",
                            "last_name": "Referent",
                            "image_url": null,
                            "scope": null,
                            "role": "Président",
                            "instance": "Assemblée départementale",
                            "zone": null,
                            "theme": null
                        },
                        "local_begin_at": "@string@.isDateTime()",
                        "local_finish_at": "@string@.isDateTime()",
                        "organizer": {
                            "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                            "first_name": "Referent",
                            "last_name": "Referent",
                            "image_url": null
                        },
                        "image_url": null,
                        "image": null,
                        "editable": true,
                        "edit_link": true,
                        "user_registered_at": null,
                        "object_state": "full"
                    },
                    {
                        "type": "event",
                        "uuid": "e770cda4-b215-4ea2-85e5-03fc3e4423e3",
                        "name": "Un événement de l'assemblée départementale",
                        "slug": "@string@-un-evenement-de-l-assemblee-departementale",
                        "time_zone": "Europe/Paris",
                        "begin_at": "@string@.isDateTime()",
                        "finish_at": "@string@.isDateTime()",
                        "participants_count": 2,
                        "is_national": false,
                        "status": "SCHEDULED",
                        "capacity": 50,
                        "visio_url": "https://parti-renaissance.fr",
                        "mode": "online",
                        "category": {
                            "event_group_category": {
                                "name": "événement",
                                "slug": "evenement",
                                "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression."
                            },
                            "name": "Convivialité",
                            "slug": "convivialite",
                            "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression."
                        },
                        "post_address": {
                            "address": "47 rue Martre",
                            "postal_code": "92110",
                            "city": "92110-92024",
                            "city_name": "Clichy",
                            "country": "FR",
                            "latitude": 48.9016,
                            "longitude": 2.305268
                        },
                        "visibility": "public",
                        "live_url": null,
                        "hidden": false,
                        "pinned": true,
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()",
                        "author": {
                            "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                            "first_name": "Gisele",
                            "last_name": "Berthoux",
                            "image_url": null,
                            "scope": null,
                            "role": null,
                            "instance": null,
                            "zone": null,
                            "theme": null
                        },
                        "local_begin_at": "@string@.isDateTime()",
                        "local_finish_at": "@string@.isDateTime()",
                        "organizer": {
                            "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                            "first_name": "Gisele",
                            "last_name": "Berthoux",
                            "image_url": null
                        },
                        "image_url": null,
                        "image": null,
                        "editable": true,
                        "edit_link": true,
                        "user_registered_at": null,
                        "object_state": "full"
                    }
                ]
            }
            """

    # ---- Anonymous (no Bearer token): same merge logic, Event data via /api/events rules,
    #      Action data masked by ActionCleaner (CONTEXT_PUBLIC_ANONYMOUS) ----
    Scenario: As a non logged-in user the latest action is returned with masked data
        # Ordering by beginAt desc pins the furthest-future fixture Action (random uuid/coords),
        # so we match the variable fields and assert the masked shape exactly.
        When I send a "GET" request to "/api/hub-item?order[beginAt]=desc&page_size=1"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 83,
                    "items_per_page": 1,
                    "count": 1,
                    "current_page": 1,
                    "last_page": 83
                },
                "items": [
                    {
                        "type": "action",
                        "uuid": "@uuid@",
                        "name": "@string@",
                        "slug": null,
                        "time_zone": null,
                        "live_url": null,
                        "visibility": null,
                        "created_at": null,
                        "begin_at": "@string@.matchRegex('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/')",
                        "finish_at": null,
                        "organizer": {
                            "uuid": null,
                            "first_name": "@string@",
                            "last_name": "@string@",
                            "image_url": null,
                            "scope": null,
                            "role": null,
                            "instance": null,
                            "zone": null,
                            "theme": null
                        },
                        "participants_count": null,
                        "status": "scheduled",
                        "capacity": null,
                        "post_address": {
                            "address": null,
                            "postal_code": "75008",
                            "city": null,
                            "city_name": "Paris 8ème",
                            "country": "FR",
                            "latitude": null,
                            "longitude": null
                        },
                        "category": {
                            "event_group_category": null,
                            "description": null,
                            "name": "@string@",
                            "slug": "@string@"
                        },
                        "visio_url": null,
                        "pinned": false,
                        "hidden": false,
                        "editable": false,
                        "is_national": false,
                        "mode": null,
                        "local_begin_at": null,
                        "local_finish_at": null,
                        "image_url": null,
                        "image": null,
                        "user_registered_at": null,
                        "object_state": "partial"
                    }
                ]
            }
            """

    Scenario: As a non logged-in user a public event exposes the full canonical shape but is not editable
        When I send a "GET" request to "/api/hub-item?zone=92&page_size=1"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 58,
                    "items_per_page": 1,
                    "count": 1,
                    "current_page": 1,
                    "last_page": 58
                },
                "items": [
                    {
                        "type": "event",
                        "uuid": "5cab27a7-dbb3-4347-9781-566dad1b9eb5",
                        "name": "Nouvel événement online",
                        "slug": "@string@-nouvel-evenement-online",
                        "time_zone": "Europe/Paris",
                        "begin_at": "@string@.isDateTime()",
                        "finish_at": "@string@.isDateTime()",
                        "participants_count": 0,
                        "is_national": false,
                        "status": "SCHEDULED",
                        "capacity": 50,
                        "visio_url": null,
                        "mode": "online",
                        "category": null,
                        "post_address": {
                            "address": "47 rue Martre",
                            "postal_code": "92110",
                            "city": "92110-92024",
                            "city_name": "Clichy",
                            "country": "FR",
                            "latitude": 48.9016,
                            "longitude": 2.305268
                        },
                        "visibility": "public",
                        "live_url": null,
                        "hidden": false,
                        "pinned": true,
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()",
                        "author": {
                            "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                            "first_name": "Referent",
                            "last_name": "R",
                            "image_url": null,
                            "scope": null,
                            "role": "Président",
                            "instance": "Assemblée départementale",
                            "zone": null,
                            "theme": null
                        },
                        "local_begin_at": "@string@.isDateTime()",
                        "local_finish_at": "@string@.isDateTime()",
                        "organizer": {
                            "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                            "first_name": "Referent",
                            "last_name": "Referent",
                            "image_url": null
                        },
                        "image_url": null,
                        "image": null,
                        "editable": false,
                        "object_state": "full"
                    }
                ]
            }
            """

    Scenario: As a non logged-in user a far-future beginAt filter returns zero items
        When I send a "GET" request to "/api/hub-item?beginAt[strictly_after]=2050-01-01&page_size=300"
        Then the response status code should be 200
        And the JSON node "metadata.total_items" should be equal to "0"
