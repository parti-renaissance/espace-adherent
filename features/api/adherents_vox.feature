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
                "image_url": "http://test.renaissance.code/assets/images/profile/8dd6da70dcf774d0b6d5789289e8a421.jpg",
                "adherent_tags": [
                    {"code": "adherent:plus_a_jour:annee_2024", "label": "Adhérent 2024"}
                ],
                "static_tags": [
                    {"code": "national_event:present:congres-2024", "label": "Présent Congres 2024"}
                ],
                "elect_tags": [
                    {"code": "elu:cotisation_ok:soumis", "label": "@string@"}
                ],
                "instances": [
                    {"type": "assembly", "code": "77", "name": "Seine-et-Marne (77)"},
                    {"type": "circonscription", "code": "77-1", "name": "1ère circonscription • Seine-et-Marne (77-1)"}
                ],
                "elect_mandates": [],
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
                    "telegram": null,
                    "tiktok": null
                },
                "nationality": "FR",
                "sessions": {
                    "mobile": [
                        {
                            "device": "iPhone 14",
                            "active_since": "2024-01-15T10:30:00+01:00",
                            "last_activity_at": "2024-03-01T14:22:00+01:00",
                            "subscribed": true,
                            "status": "active"
                        }
                    ],
                    "web": null
                },
                "subscription_types": [
                    {"code": "subscribed_emails_movement_information", "label": "National", "subscribed": false},
                    {"code": "subscribed_emails_referents", "label": "Mon assemblée départementale", "subscribed": false},
                    {"code": "deputy_email", "label": "Ma circonscription", "subscribed": false},
                    {"code": "subscribed_emails_local_host", "label": "Mon comité local", "subscribed": false},
                    {"code": "senator_email", "label": "Mon sénateur/trice", "subscribed": false},
                    {"code": "candidate_email", "label": "Les candidats du Parti", "subscribed": false},
                    {"code": "event_email", "label": "Notification nouvel événement proche de chez moi", "subscribed": false}
                ],
                "roles": [
                    {"code": "president_departmental_assembly", "label": "Responsable communication (75, 77)", "is_delegated": true, "function": "Responsable communication", "zones": "Paris. Seine-et-Marne", "zone_codes": "75, 77"}
                ],
                "available_for_resubscribe_email": false
            }
            """

        Examples:
            | user                      | scope                           |
            | referent@en-marche-dev.fr | president_departmental_assembly |

    Scenario Outline: As a user with role on Vox app I can get adherent detail with multiple delegated roles
        Given I am logged with "<user>" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/adherents/b4219d47-3138-5efd-9762-2ef9f9495084?scope=<scope>"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                "public_id": null,
                "civility": "Madame",
                "first_name": "Gisele",
                "last_name": "Berthoux",
                "age": @integer@,
                "birthdate": "1983-12-24T00:00:00+01:00",
                "image_url": null,
                "adherent_tags": [
                    {"code": "adherent:a_jour_2026:recotisation", "label": "@string@"}
                ],
                "static_tags": [
                    {"code": "national_event:event-national-1", "label": "Event national 1"}
                ],
                "elect_tags": [
                    {"code": "elu:cotisation_ok:exempte", "label": "@string@"}
                ],
                "instances": [
                    {"type": "assembly", "code": "92", "name": "Hauts-de-Seine (92)"},
                    {"type": "committee", "name": "En Marche Paris 8", "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818"}
                ],
                "elect_mandates": [{
                    "code": "conseiller_municipal",
                    "label": "Conseiller municipal"
                }],
                "account_created_at": "@string@.isDateTime()",
                "subscriptions": {
                    "sms": {"available": true, "subscribed": true},
                    "web": {"available": true, "subscribed": true},
                    "email": {"available": true, "subscribed": true},
                    "mobile": {"available": true, "subscribed": false}
                },
                "first_contribution_at": null,
                "last_activity_at": null,
                "social_links": {
                    "facebook": null,
                    "twitter": null,
                    "instagram": null,
                    "linkedin": null,
                    "telegram": null,
                    "tiktok": null
                },
                "nationality": "FR",
                "sessions": {
                    "mobile": null,
                    "web": null
                },
                "subscription_types": [
                    {"code": "subscribed_emails_movement_information", "label": "National", "subscribed": false},
                    {"code": "subscribed_emails_referents", "label": "Mon assemblée départementale", "subscribed": true},
                    {"code": "deputy_email", "label": "Ma circonscription", "subscribed": false},
                    {"code": "subscribed_emails_local_host", "label": "Mon comité local", "subscribed": false},
                    {"code": "senator_email", "label": "Mon sénateur/trice", "subscribed": false},
                    {"code": "candidate_email", "label": "Les candidats du Parti", "subscribed": false},
                    {"code": "event_email", "label": "Notification nouvel événement proche de chez moi", "subscribed": false}
                ],
                "roles": [
                    {"code": "deputy", "label": "Responsable mobilisation (CIRCO_FDE-06)", "is_delegated": true, "function": "Responsable mobilisation", "zones": "Suisse", "zone_codes": "CIRCO_FDE-06"},
                    {"code": "senator", "label": "Responsable mobilisation (92)", "is_delegated": true, "function": "Responsable mobilisation", "zones": "Hauts-de-Seine", "zone_codes": "92"},
                    {"code": "deputy", "label": "Responsable mobilisation (75-2)", "is_delegated": true, "function": "Responsable mobilisation", "zones": "Paris (2)", "zone_codes": "75-2"},
                    {"code": "candidate", "label": "Candidat délégué", "is_delegated": true, "function": "Candidat délégué", "zones": null, "zone_codes": null},
                    {"code": "president_departmental_assembly", "label": "Responsable élus délégué #1 (92)", "is_delegated": true, "function": "Responsable élus délégué #1", "zones": "Hauts-de-Seine", "zone_codes": "92"},
                    {"code": "president_departmental_assembly", "label": "Responsable communication (13, 59, 76, 77, 92)", "is_delegated": true, "function": "Responsable communication", "zones": "Bouches-du-Rhône. Hauts-de-Seine. Nord. Seine-Maritime. Seine-et-Marne", "zone_codes": "13, 59, 76, 77, 92"},
                    {"code": "correspondent", "label": "Responsable logistique (92)", "is_delegated": true, "function": "Responsable logistique", "zones": "Hauts-de-Seine", "zone_codes": "92"},
                    {"code": "legislative_candidate", "label": "Responsable communication (75-1)", "is_delegated": true, "function": "Responsable communication", "zones": "Paris (1)", "zone_codes": "75-1"}
                ],
                "available_for_resubscribe_email": false
            }
            """

        Examples:
            | user                      | scope                           |
            | referent@en-marche-dev.fr | president_departmental_assembly |

    Scenario Outline: As a user with role on Vox app I can not filter adherents with invalid dates
        Given I am logged with "<user>" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/adherents?scope=<scope>&registered[start]=<start>&registered[end]=<end>"
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON nodes should match:
            | title  | An error occurred |
            | detail | @string@          |

        Examples:
            | user                      | scope                           | start                       | end                      |
            | referent@en-marche-dev.fr | president_departmental_assembly | +020220-04-06T08:02:00.000Z | 2016-04-06T07:59:00.000Z |
            | referent@en-marche-dev.fr | president_departmental_assembly | invalid-date                | 2024-04-06T00:00:00.000Z |

    Scenario Outline: As a user with role on Vox app I can get adherents list with split tags
        Given I am logged with "<user>" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/adherents?scope=<scope>"
        Then the response status code should be 200
        And the JSON nodes should match:
            | metadata.total_items    | @integer@ |
            | metadata.items_per_page | 50        |
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

    Scenario Outline: As a user with role on Vox app I can get adherents list with roles
        Given I am logged with "<user>" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/adherents?scope=<scope>"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 3,
                    "items_per_page": 50,
                    "count": 3,
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
                        "image_url": "http://test.renaissance.code/assets/images/profile/8dd6da70dcf774d0b6d5789289e8a421.jpg",
                        "account_created_at": "@string@.isDateTime()",
                        "first_contribution_at": null,
                        "last_activity_at": null,
                        "adherent_tags": [
                            {"code": "adherent:plus_a_jour:annee_2024", "label": "Adhérent 2024"}
                        ],
                        "static_tags": [
                            {"code": "national_event:present:congres-2024", "label": "Présent Congres 2024"}
                        ],
                        "elect_tags": [
                            {"code": "elu:cotisation_ok:soumis", "label": "@string@"}
                        ],
                        "instances": [
                            {"type": "assembly", "code": "77", "name": "Seine-et-Marne (77)"},
                            {"type": "circonscription", "code": "77-1", "name": "1ère circonscription • Seine-et-Marne (77-1)"}
                        ],
                        "elect_mandates": [],
                        "subscriptions": {
                            "sms": {"available": false, "subscribed": false},
                            "web": {"available": true, "subscribed": false},
                            "email": {"available": true, "subscribed": true},
                            "mobile": {"available": true, "subscribed": true}
                        },
                        "roles": [
                            {"code": "president_departmental_assembly", "label": "Responsable communication (75, 77)", "is_delegated": true, "function": "Responsable communication", "zones": "Paris. Seine-et-Marne", "zone_codes": "75, 77"}
                        ]
                    },
                    {
                        "uuid": "5f68e1cc-024e-4193-bd51-f2469f22dd07",
                        "public_id": null,
                        "civility": "Monsieur",
                        "first_name": "Jules",
                        "last_name": "Fullstack",
                        "age": @integer@,
                        "birthdate": "1942-01-10T00:00:00+02:00",
                        "image_url": null,
                        "account_created_at": "@string@.isDateTime()",
                        "first_contribution_at": null,
                        "last_activity_at": null,
                        "adherent_tags": [
                            {"code": "adherent:plus_a_jour:annee_2023", "label": "Adhérent 2023"}
                        ],
                        "static_tags": [
                            {"code": "national_event:event-national-2", "label": "Event national 2"}
                        ],
                        "elect_tags": null,
                        "instances": [
                            {"type": "assembly", "code": "92", "name": "Hauts-de-Seine (92)"}
                        ],
                        "elect_mandates": [],
                        "subscriptions": {
                            "sms": {"available": false, "subscribed": false},
                            "web": {"available": true, "subscribed": true},
                            "email": {"available": true, "subscribed": true},
                            "mobile": {"available": true, "subscribed": true}
                        },
                        "roles": [
                            {"code": "correspondent", "label": "Responsable local (92)", "is_delegated": false, "function": null, "zones": "Hauts-de-Seine", "zone_codes": "92"}
                        ]
                    },
                    {
                        "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                        "public_id": null,
                        "civility": "Madame",
                        "first_name": "Gisele",
                        "last_name": "Berthoux",
                        "age": @integer@,
                        "birthdate": "1983-12-24T00:00:00+01:00",
                        "image_url": null,
                        "account_created_at": "@string@.isDateTime()",
                        "first_contribution_at": null,
                        "last_activity_at": null,
                        "adherent_tags": [
                            {"code": "adherent:a_jour_2026:recotisation", "label": "@string@"}
                        ],
                        "static_tags": [
                            {"code": "national_event:event-national-1", "label": "Event national 1"}
                        ],
                        "elect_tags": [
                            {"code": "elu:cotisation_ok:exempte", "label": "@string@"}
                        ],
                        "instances": [
                            {"type": "assembly", "code": "92", "name": "Hauts-de-Seine (92)"},
                            {"type": "committee", "name": "En Marche Paris 8", "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818"}
                        ],
                        "elect_mandates": [{
                            "code": "conseiller_municipal",
                            "label": "Conseiller municipal"
                        }],
                        "subscriptions": {
                            "sms": {"available": true, "subscribed": true},
                            "web": {"available": true, "subscribed": true},
                            "email": {"available": true, "subscribed": true},
                            "mobile": {"available": true, "subscribed": false}
                        },
                        "roles": [
                            {"code": "deputy", "label": "Responsable mobilisation (CIRCO_FDE-06)", "is_delegated": true, "function": "Responsable mobilisation", "zones": "Suisse", "zone_codes": "CIRCO_FDE-06"},
                            {"code": "senator", "label": "Responsable mobilisation (92)", "is_delegated": true, "function": "Responsable mobilisation", "zones": "Hauts-de-Seine", "zone_codes": "92"},
                            {"code": "deputy", "label": "Responsable mobilisation (75-2)", "is_delegated": true, "function": "Responsable mobilisation", "zones": "Paris (2)", "zone_codes": "75-2"},
                            {"code": "candidate", "label": "Candidat délégué", "is_delegated": true, "function": "Candidat délégué", "zones": null, "zone_codes": null},
                            {"code": "president_departmental_assembly", "label": "Responsable élus délégué #1 (92)", "is_delegated": true, "function": "Responsable élus délégué #1", "zones": "Hauts-de-Seine", "zone_codes": "92"},
                            {"code": "president_departmental_assembly", "label": "Responsable communication (13, 59, 76, 77, 92)", "is_delegated": true, "function": "Responsable communication", "zones": "Bouches-du-Rhône. Hauts-de-Seine. Nord. Seine-Maritime. Seine-et-Marne", "zone_codes": "13, 59, 76, 77, 92"},
                            {"code": "correspondent", "label": "Responsable logistique (92)", "is_delegated": true, "function": "Responsable logistique", "zones": "Hauts-de-Seine", "zone_codes": "92"},
                            {"code": "legislative_candidate", "label": "Responsable communication (75-1)", "is_delegated": true, "function": "Responsable communication", "zones": "Paris (1)", "zone_codes": "75-1"}
                        ]
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                           |
            | referent@en-marche-dev.fr | president_departmental_assembly |

    Scenario: As a non logged-in user I cannot access sensitive data endpoint
        Given I send a "GET" request to "/api/v3/adherents/a046adbe-9c7b-56a9-a676-6151a6785dda/sensitive-data?type=phone"
        Then the response status code should be 401

    Scenario: As a deputy I can get phone of an adherent in my zone
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/adherents/a046adbe-9c7b-56a9-a676-6151a6785dda/sensitive-data?scope=deputy&type=phone"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            { "phone": "+33187264236" }
            """

    Scenario: As a deputy I can get email of an adherent in my zone
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/adherents/a046adbe-9c7b-56a9-a676-6151a6785dda/sensitive-data?scope=deputy&type=email"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            { "email": "jacques.picard@en-marche.fr" }
            """

    Scenario: As a deputy I can get address of an adherent in my zone
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/adherents/a046adbe-9c7b-56a9-a676-6151a6785dda/sensitive-data?scope=deputy&type=address"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "address": {
                    "address": "36 rue de la Paix",
                    "postal_code": "75008",
                    "city": "Paris 8ème",
                    "country": "FR"
                }
            }
            """

    Scenario: As a deputy I cannot get sensitive data with invalid type
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/adherents/a046adbe-9c7b-56a9-a676-6151a6785dda/sensitive-data?scope=deputy&type=invalid"
        Then the response status code should be 400

    Scenario: As a deputy I cannot get sensitive data without type parameter
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/adherents/a046adbe-9c7b-56a9-a676-6151a6785dda/sensitive-data?scope=deputy"
        Then the response status code should be 400

    Scenario: As a non logged-in user I cannot access donations endpoint
        Given I send a "GET" request to "/api/v3/adherents/a046adbe-9c7b-56a9-a676-6151a6785dda/donations"
        Then the response status code should be 401

    Scenario: As a deputy I can get donations of an adherent in my zone
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/adherents/a046adbe-9c7b-56a9-a676-6151a6785dda/donations?scope=deputy"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "date": "@string@.isDateTime()",
                    "amount": 30,
                    "transaction_type": "cb",
                    "transaction_type_label": "Carte bleue",
                    "type": "membership",
                    "type_label": "Cotisation",
                    "status": "paid",
                    "status_label": "Payé",
                    "uuid": "@uuid@"
                },
                {
                    "date": "@string@.isDateTime()",
                    "amount": 30,
                    "transaction_type": "cb",
                    "transaction_type_label": "Carte bleue",
                    "type": "membership",
                    "type_label": "Cotisation",
                    "status": "paid",
                    "status_label": "Payé",
                    "uuid": "@uuid@"
                },
                {
                    "date": "@string@.isDateTime()",
                    "amount": 30,
                    "transaction_type": "cb",
                    "transaction_type_label": "Carte bleue",
                    "type": "membership",
                    "type_label": "Cotisation",
                    "status": "paid",
                    "status_label": "Payé",
                    "uuid": "@uuid@"
                },
                {
                    "date": "@string@.isDateTime()",
                    "amount": 30,
                    "transaction_type": "cb",
                    "transaction_type_label": "Carte bleue",
                    "type": "membership",
                    "type_label": "Cotisation",
                    "status": "paid",
                    "status_label": "Payé",
                    "uuid": "@uuid@"
                },
                {
                    "date": "2020-01-06T19:00:00+01:00",
                    "amount": 100,
                    "transaction_type": "cb",
                    "transaction_type_label": "Carte bleue",
                    "type": "recurring",
                    "type_label": "Don récurrent",
                    "status": "paid",
                    "status_label": "Payé",
                    "uuid": "@uuid@"
                },
                {
                    "date": "2020-01-05T15:00:00+01:00",
                    "amount": 60,
                    "transaction_type": "cb",
                    "transaction_type_label": "Carte bleue",
                    "type": "recurring",
                    "type_label": "Don récurrent",
                    "status": "paid",
                    "status_label": "Payé",
                    "uuid": "@uuid@"
                },
                {
                    "date": "2020-01-04T12:30:00+01:00",
                    "amount": 40,
                    "transaction_type": "cb",
                    "transaction_type_label": "Carte bleue",
                    "type": "simple",
                    "type_label": "Don",
                    "status": "paid",
                    "status_label": "Payé",
                    "uuid": "@uuid@"
                },
                {
                    "date": "2019-12-05T15:00:00+01:00",
                    "amount": 60,
                    "transaction_type": "cb",
                    "transaction_type_label": "Carte bleue",
                    "type": "recurring",
                    "type_label": "Don récurrent",
                    "status": "paid",
                    "status_label": "Payé",
                    "uuid": "@uuid@"
                },
                {
                    "date": "2019-12-04T12:00:00+01:00",
                    "amount": 50,
                    "transaction_type": "cb",
                    "transaction_type_label": "Carte bleue",
                    "type": "simple",
                    "type_label": "Don",
                    "status": "paid",
                    "status_label": "Payé",
                    "uuid": "@uuid@"
                }
            ]
            """

    Scenario Outline: As a user with scope I can access donations of an adherent in my zone
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents/a9fc8d48-6f57-4d89-ae73-50b3f9b586f4/donations?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a user with scope I cannot access donations of an adherent outside my zone
        Given I am logged with "je-mengage-user-1@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents/a9fc8d48-6f57-4d89-ae73-50b3f9b586f4/donations?scope=correspondent"
        Then the response status code should be 403

    Scenario Outline: As a user with scope I can access sensitive data of an adherent in my zone
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents/a9fc8d48-6f57-4d89-ae73-50b3f9b586f4/sensitive-data?scope=<scope>&type=email"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            { "email": "francis.brioul@yahoo.com" }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a user with scope I cannot access sensitive data of an adherent outside my zone
        Given I am logged with "je-mengage-user-1@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents/a9fc8d48-6f57-4d89-ae73-50b3f9b586f4/sensitive-data?scope=correspondent&type=phone"
        Then the response status code should be 403

    Scenario: As a JeMengage Mobile user (VOX export) I can export adherents with VOX-aligned columns
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/adherents.csv?scope=president_departmental_assembly"
        Then the response status code should be 200
        And the header "Content-Type" should contain "text/csv"
        And the header "Content-Disposition" should contain "adherents--"
        And the header "Content-Disposition" should contain ".csv"
        And the response should contain "UUID;PID;Civilité;Prénom;Nom;Âge;\"Date de naissance\";\"Date de création de compte\";\"Date de première cotisation\";\"Date de dernière activité\";\"Labels Adhérent\";\"Labels Statique\";\"Label Élu\";Rôles;\"Abonné email\";\"Abonné SMS\""
        And the response should contain "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4;;Monsieur;Francis;Brioul;"
        And the response should contain ";07/01/1962;\"25/01/2017 19:31\""
        And the response should contain ";\"Président d'Assemblée (75, 77)\";1;"
        And the response should contain "b4219d47-3138-5efd-9762-2ef9f9495084;;Madame;Gisele;Berthoux;"
        And the response should contain ";24/12/1983;\"08/01/2017 05:55\""
        And the response should contain "Déléguée de circonscription (CIRCO_FDE-06), Sénatrice (92)"
        And the response should contain ";1;1"

    Scenario: As a VOX user I can get filters for contacts with correct groups and order
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/filters?scope=president_departmental_assembly&feature=contacts"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
                {
                    "label": "",
                    "color": "",
                    "filters": [
                        {
                            "code": "search_term",
                            "label": "Recherche",
                            "options": {
                                "favorite": true
                            },
                            "type": "text"
                        }
                    ]
                },
                {
                    "label": "",
                    "color": "",
                    "filters": [
                        {
                            "code": "adherent_tags",
                            "label": "Labels adhérent",
                            "options": {
                                "favorite": false,
                                "advanced": false,
                                "choices": {
                                    "adherent": "Adhérent",
                                    "adherent:a_jour_2026": "Adhérent - À jour 2026",
                                    "adherent:a_jour_2026:primo": "Adhérent - À jour 2026 - Primo-adhérent",
                                    "adherent:a_jour_2026:recotisation": "Adhérent - À jour 2026 - Recotisation",
                                    "adherent:a_jour_2026:elu_a_jour": "Adhérent - À jour 2026 - Élu à jour",
                                    "adherent:plus_a_jour": "Adhérent - Plus à jour",
                                    "adherent:plus_a_jour:annee_2025": "Adhérent - Plus à jour - À jour 2025",
                                    "adherent:plus_a_jour:annee_2024": "Adhérent - Plus à jour - À jour 2024",
                                    "adherent:plus_a_jour:annee_2023": "Adhérent - Plus à jour - À jour 2023",
                                    "adherent:plus_a_jour:annee_2022": "Adhérent - Plus à jour - À jour 2022",
                                    "sympathisant": "Sympathisant",
                                    "sympathisant:adhesion_incomplete": "Sympathisant - Adhésion incomplète",
                                    "sympathisant:compte_em": "Sympathisant - Ancien compte En Marche",
                                    "sympathisant:compte_avecvous_jemengage": "Sympathisant - Anciens comptes Je m'engage et Avec vous",
                                    "sympathisant:autre_parti": "Sympathisant - Adhérent d'un autre parti",
                                    "sympathisant:besoin_d_europe": "Sympathisant - Besoin d'Europe",
                                    "sympathisant:ensemble2024": "Sympathisant - Ensemble 2024"
                                },
                                "required": true,
                                "placeholder": "Tous mes contacts"
                            },
                            "type": "select"
                        },
                        {
                            "code": "static_tags",
                            "label": "Labels nationaux",
                            "options": {
                                "favorite": false,
                                "advanced": false,
                                "choices": {
                                    "national_event:event-national-1": "Event national 1",
                                    "national_event:event-national-2": "Event national 2",
                                    "national_event:meeting-nrp": "Meeting NRP",
                                    "national_event:campus": "Campus",
                                    "national_event:present:campus": "Présent Campus",
                                    "national_event:present:event-passe": "Présent Event passé"
                                },
                                "required": false,
                                "help": "Ces labels sont appliqués automatiquement ou manuellement au national"
                            },
                            "type": "select"
                        }
                    ]
                },
                {
                    "label": "Localisation",
                    "color": "",
                    "filters": [
                        {
                            "code": "zones",
                            "label": "Zone géographique",
                            "options": {
                                "url": "/api/v3/zone/autocomplete",
                                "query_param": "q",
                                "value_param": "uuid",
                                "label_param": "name",
                                "multiple": true,
                                "required": false,
                                "help": "<strong>Toutes les zones incluses dans votre zone de gestion sont filtrables.</strong> Exemple : Arrondissement, Canton, Ville, Circonscription électorale"
                            },
                            "type": "zone_autocomplete"
                        },
                        {
                            "code": "is_committee_member",
                            "label": "Membre d'un comité",
                            "options": {
                                "choices": {
                                    "false": "Non",
                                    "true": "Oui"
                                }
                            },
                            "type": "select"
                        },
                        {
                            "code": "committee_uuids",
                            "label": "Comités",
                            "options": {
                                "choices": {
                                    "d648d486-fbb3-4394-b4b3-016fac3658af": "Antenne En Marche de Fontainebleau",
                                    "5e00c264-1d4b-43b8-862e-29edc38389b3": "Comité des 3 communes",
                                    "508d4ac0-27d6-4635-8953-4cc8600018f9": "En Marche - Comité de Rouen",
                                    "b0cd0e52-a5a4-410b-bba3-37afdd326a0a": "En Marche Dammarie-les-Lys",
                                    "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3": "Second Comité des 3 communes"
                                },
                                "multiple": true
                            },
                            "type": "select"
                        }
                    ]
                },
                {
                    "label": "Informations personnelles",
                    "color": "#0E7490",
                    "filters": [
                        {
                            "code": "gender",
                            "label": "Civilité",
                            "options": {
                                "choices": {
                                    "female": "Madame",
                                    "male": "Monsieur"
                                }
                            },
                            "type": "select"
                        },
                        {
                            "code": "age",
                            "label": "Âge",
                            "options": {
                                "suffix": "ans",
                                "first": {
                                    "min": 1,
                                    "max": 200,
                                    "label": "Âgé d'au moins"
                                },
                                "second": {
                                    "min": 1,
                                    "max": 200,
                                    "label": "Âgé de maximum"
                                }
                            },
                            "type": "integer_interval"
                        },
                        {
                            "code": "nationality",
                            "label": "Nationalité",
                            "options": {
                                "choices": "@*@"
                            },
                            "type": "select"
                        }
                    ]
                },
                {
                    "label": "Communications",
                    "color": "#0891B2",
                    "filters": [
                        {
                            "code": "email_subscription",
                            "label": "Abonné aux emails",
                            "options": {
                                "choices": {
                                    "false": "Non",
                                    "true": "Oui"
                                }
                            },
                            "type": "select"
                        },
                        {
                            "code": "sms_subscription",
                            "label": "Abonné aux SMS",
                            "options": {
                                "choices": {
                                    "false": "Non",
                                    "true": "Oui"
                                }
                            },
                            "type": "select"
                        }
                    ]
                },
                {
                    "label": "Dates",
                    "color": "#0E7490",
                    "filters": [
                        {
                            "code": "first_membership",
                            "label": "Première cotisation",
                            "options": null,
                            "type": "date_interval"
                        },
                        {
                            "code": "last_membership",
                            "label": "Dernière cotisation",
                            "options": null,
                            "type": "date_interval"
                        },
                        {
                            "code": "registered",
                            "label": "Création du compte",
                            "options": null,
                            "type": "date_interval"
                        }
                    ]
                },
                {
                    "label": "Élus",
                    "color": "#2563EB",
                    "filters": [
                        {
                            "code": "elect_tags",
                            "label": "Label élu",
                            "options": {
                                "favorite": false,
                                "advanced": false,
                                "choices": {
                                    "elu": "Élu",
                                    "elu:attente_declaration": "Élu - En attente de déclaration",
                                    "elu:cotisation_ok": "Élu - À jour de cotisation",
                                    "elu:cotisation_ok:exempte": "Élu - À jour de cotisation - Exempté de cotisation",
                                    "elu:cotisation_ok:non_soumis": "Élu - À jour de cotisation - Non soumis à cotisation",
                                    "elu:cotisation_ok:soumis": "Élu - À jour de cotisation - Soumis à cotisation",
                                    "elu:cotisation_nok": "Élu - Non à jour de cotisation",
                                    "elu:exempte_et_adherent_cotisation_nok": "Élu - Exempté mais pas à jour de cotisation adhérent"
                                },
                                "required": false
                            },
                            "type": "select"
                        },
                        {
                            "code": "elect_mandates",
                            "label": "Mandats",
                            "options": {
                                "advanced": false,
                                "choices": {
                                    "conseiller_arrondissement": "Conseiller d'arrondissement",
                                    "conseiller_communautaire": "Conseiller communautaire",
                                    "conseiller_departemental": "Conseiller départemental",
                                    "conseiller_fde": "Conseiller FDE",
                                    "conseiller_municipal": "Conseiller municipal",
                                    "conseiller_regional": "Conseiller régional",
                                    "conseiller_territorial": "Conseiller territorial",
                                    "delegue_consulaire": "Délégué consulaire",
                                    "depute": "Député",
                                    "depute_europeen": "Député européen",
                                    "maire": "Maire",
                                    "membre_assemblee_fde": "Membre de l'Assemblée des Français de l'étranger",
                                    "ministre": "Ministre",
                                    "president_conseil_communautaire": "Président du Conseil communautaire",
                                    "president_conseil_departemental": "Président du Conseil départemental",
                                    "president_conseil_regional": "Président du Conseil régional",
                                    "senateur": "Sénateur"
                                },
                                "multiple": true,
                                "help": "Les mandats sont ajoutés et mis à jour par le pôle élections et les Assemblées départementales ou FDE."
                            },
                            "type": "select"
                        },
                        {
                            "code": "declared_mandates",
                            "label": "Déclarations de mandats",
                            "options": {
                                "advanced": false,
                                "choices": {
                                    "conseiller_arrondissement": "Conseiller d'arrondissement",
                                    "conseiller_communautaire": "Conseiller communautaire",
                                    "conseiller_departemental": "Conseiller départemental",
                                    "conseiller_fde": "Conseiller FDE",
                                    "conseiller_municipal": "Conseiller municipal",
                                    "conseiller_regional": "Conseiller régional",
                                    "conseiller_territorial": "Conseiller territorial",
                                    "delegue_consulaire": "Délégué consulaire",
                                    "depute": "Député",
                                    "depute_europeen": "Député européen",
                                    "maire": "Maire",
                                    "membre_assemblee_fde": "Membre de l'Assemblée des Français de l'étranger",
                                    "ministre": "Ministre",
                                    "president_conseil_communautaire": "Président du Conseil communautaire",
                                    "president_conseil_departemental": "Président du Conseil départemental",
                                    "president_conseil_regional": "Président du Conseil régional",
                                    "senateur": "Sénateur"
                                },
                                "multiple": true,
                                "help": "@string@"
                            },
                            "type": "select"
                        }
                    ]
                }
            ]
            """
