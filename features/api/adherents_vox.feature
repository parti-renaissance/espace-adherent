@api
@renaissance
Feature:
    In order to get adherents information on Vox app
    As a Vox user with role
    I should be able to access adherents API with split tags format

    Scenario Outline: As a user with role on Vox app I can get adherent detail with split tags
        Given I am logged with "<user>" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/adherents/a9fc8d48-6f57-4d89-ae73-50b3f9b586f4?scope=<scope>"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                "public_id": null,
                "civility": "Monsieur",
                "first_name": "Francis",
                "last_name": "Brioul",
                "age": @integer@,
                "birthdate": "1962-01-07T00:00:00+01:00",
                "image_url": null,
                "adherent_tags": [
                    {"code": "adherent:a_jour_2024", "label": "Adhérent à jour 2024"}
                ],
                "static_tags": [
                    {"code": "national_event:present:congres-2024", "label": "Présent au Congrès 2024"}
                ],
                "elect_tags": [
                    {"code": "elu:conseiller_municipal", "label": "Conseiller municipal"}
                ],
                "instances": [
                    {"name": "Seine-et-Marne (1)", "type": "district"},
                    {"name": "Melun", "type": "district"}
                ],
                "account_created_at": "@string@.isDateTime()",
                "subscriptions": {
                    "mobile": {"available": true, "subscribed": true},
                    "web": {"available": true, "subscribed": false},
                    "sms": {"available": false, "subscribed": false},
                    "email": {"available": true, "subscribed": true}
                },
                "first_contribution_at": null,
                "last_activity_at": null,
                "social_links": {
                    "facebook": null,
                    "twitter": null,
                    "instagram": null,
                    "linkedin": null,
                    "telegram": null
                },
                "phone_available": false,
                "nationality": null,
                "sessions": [
                    {
                        "type": "mobile",
                        "device": "iPhone 14",
                        "active_since": "2024-01-15T10:30:00+01:00",
                        "last_activity_at": "2024-03-01T14:22:00+01:00",
                        "subscribed": true
                    }
                ],
                "subscription_types": [],
                "available_for_resubscribe_email": false
            }
            """

        Examples:
            | user                      | scope                           |
            | referent@en-marche-dev.fr | president_departmental_assembly |

    Scenario Outline: As a user with role on Vox app I can get adherents list with split tags
        Given I am logged with "<user>" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/adherents?scope=<scope>"
        Then the response status code should be 200
        And the JSON nodes should match:
            | metadata.total_items    | @integer@ |
            | metadata.items_per_page | 25        |
            | metadata.current_page   | 1         |
        And the JSON node "items[0].uuid" should exist
        And the JSON node "items[0].first_name" should exist
        And the JSON node "items[0].last_name" should exist
        And the JSON node "items[0].adherent_tags" should exist
        And the JSON node "items[0].static_tags" should exist
        And the JSON node "items[0].elect_tags" should exist
        And the JSON node "items[0].instances" should exist
        And the JSON node "items[0].subscriptions" should exist

        Examples:
            | user                      | scope                           |
            | referent@en-marche-dev.fr | president_departmental_assembly |

    Scenario Outline: As a user with role on Vox app I can filter adherents list and get Vox list format
        Given I am logged with "<user>" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/adherents?scope=<scope>&firstName=Francis"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 1,
                    "items_per_page": 25,
                    "count": 1,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                        "public_id": null,
                        "civility": "Monsieur",
                        "first_name": "Francis",
                        "last_name": "Brioul",
                        "age": @integer@,
                        "birthdate": "1962-01-07T00:00:00+01:00",
                        "image_url": null,
                        "adherent_tags": [
                            {"code": "adherent:a_jour_2024", "label": "Adhérent à jour 2024"}
                        ],
                        "static_tags": [
                            {"code": "national_event:present:congres-2024", "label": "Présent au Congrès 2024"}
                        ],
                        "elect_tags": [
                            {"code": "elu:conseiller_municipal", "label": "Conseiller municipal"}
                        ],
                        "instances": [
                            {"name": "Seine-et-Marne (1)", "type": "district"},
                            {"name": "Melun", "type": "district"}
                        ],
                        "account_created_at": "@string@.isDateTime()",
                        "subscriptions": {
                            "mobile": {"available": true, "subscribed": true},
                            "web": {"available": true, "subscribed": false},
                            "sms": {"available": false, "subscribed": false},
                            "email": {"available": true, "subscribed": true}
                        },
                        "first_contribution_at": null,
                        "last_activity_at": null
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                           |
            | referent@en-marche-dev.fr | president_departmental_assembly |
