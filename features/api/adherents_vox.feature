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
                    {"code": "adherent:a_jour_2024", "label": "Adhérent à jour 2024"}
                ],
                "static_tags": [
                    {"code": "national_event:present:congres-2024", "label": "Présent au Congrès 2024"}
                ],
                "elect_tags": [
                    {"code": "elu:conseiller_municipal", "label": "Conseiller municipal"}
                ],
                "instances": [
                    {"type": "assembly", "code": "77", "name": "Seine-et-Marne (77)"},
                    {"type": "circonscription", "code": "77-1", "name": "1ère circonscription • Seine-et-Marne (77-1)"}
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
                    "telegram": null,
                    "tiktok": null
                },
                "nationality": null,
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
                    {"code": "subscribed_emails_movement_information", "label": "Recevoir les emails du national", "subscribed": false},
                    {"code": "subscribed_emails_weekly_letter", "label": "Recevoir la newsletter hebdomadaire nationale", "subscribed": false},
                    {"code": "subscribed_emails_referents", "label": "Recevoir les emails de mon Assemblée départementale", "subscribed": false},
                    {"code": "deputy_email", "label": "Recevoir les emails de ma/mon député(e) ou de ma/mon délégué(e) de circonscription", "subscribed": false},
                    {"code": "subscribed_emails_local_host", "label": "Recevoir les emails de mon Comité local", "subscribed": false},
                    {"code": "senator_email", "label": "Recevoir les emails de ma/mon sénateur/trice", "subscribed": false},
                    {"code": "candidate_email", "label": "Recevoir les emails des candidats du parti", "subscribed": false},
                    {"code": "jam_email", "label": "Recevoir les emails des Jeunes avec Macron", "subscribed": false},
                    {"code": "event_email", "label": "Recevoir les emails d'événements", "subscribed": false},
                    {"code": "militant_action_sms", "label": "Recevoir les informations sur les actions militantes du mouvement par téléphone", "subscribed": false}
                ],
                "roles": [
                    {"code": "president_departmental_assembly", "label": "Président d'assemblée départementale", "is_delegated": true, "function": "Responsable communication", "zones": null, "zone_codes": null}
                ],
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
                            {"code": "adherent:a_jour_2024", "label": "Adhérent à jour 2024"}
                        ],
                        "static_tags": [
                            {"code": "national_event:present:congres-2024", "label": "Présent au Congrès 2024"}
                        ],
                        "elect_tags": [
                            {"code": "elu:conseiller_municipal", "label": "Conseiller municipal"}
                        ],
                        "instances": [
                            {"type": "assembly", "code": "77", "name": "Seine-et-Marne (77)"},
                            {"type": "circonscription", "code": "77-1", "name": "1ère circonscription • Seine-et-Marne (77-1)"}
                        ],
                        "subscriptions": {
                            "sms": {"available": false, "subscribed": false},
                            "web": {"available": true, "subscribed": false},
                            "email": {"available": true, "subscribed": true},
                            "mobile": {"available": true, "subscribed": true}
                        },
                        "roles": [
                            {"code": "president_departmental_assembly", "label": "Président d'assemblée départementale", "is_delegated": true, "function": "Responsable communication", "zones": null, "zone_codes": null}
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
                            {"code": "adherent:a_jour_2024", "label": "Adhérente à jour 2024"}
                        ],
                        "static_tags": [
                            {"code": "national_event:event-national-1:2022-12-12", "label": "Participant événement national"}
                        ],
                        "elect_tags": [
                            {"code": "elu:conseiller_municipal", "label": "Conseillère municipale"}
                        ],
                        "instances": [
                            {"type": "assembly", "code": "92", "name": "Hauts-de-Seine (92)"},
                            {"type": "committee", "name": "En Marche Paris 8", "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818"}
                        ],
                        "subscriptions": {
                            "sms": {"available": true, "subscribed": true},
                            "web": {"available": true, "subscribed": true},
                            "email": {"available": true, "subscribed": true},
                            "mobile": {"available": true, "subscribed": false}
                        },
                        "roles": [
                            {"code": "deputy", "label": "Déléguée de circonscription", "is_delegated": true, "function": "Responsable mobilisation", "zones": null, "zone_codes": null},
                            {"code": "senator", "label": "Sénatrice", "is_delegated": true, "function": "Responsable mobilisation", "zones": null, "zone_codes": null},
                            {"code": "deputy", "label": "Déléguée de circonscription", "is_delegated": true, "function": "Responsable mobilisation", "zones": null, "zone_codes": null},
                            {"code": "candidate", "label": "Candidate", "is_delegated": true, "function": "Candidat délégué", "zones": null, "zone_codes": null},
                            {"code": "president_departmental_assembly", "label": "Présidente d'assemblée départementale", "is_delegated": true, "function": "Responsable élus délégué #1", "zones": null, "zone_codes": null},
                            {"code": "president_departmental_assembly", "label": "Présidente d'assemblée départementale", "is_delegated": true, "function": "Responsable communication", "zones": null, "zone_codes": null},
                            {"code": "correspondent", "label": "Responsable locale", "is_delegated": true, "function": "Responsable logistique", "zones": null, "zone_codes": null},
                            {"code": "legislative_candidate", "label": "Candidate aux législatives", "is_delegated": true, "function": "Responsable communication", "zones": null, "zone_codes": null}
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
                            {"code": "adherent:a_jour_2023", "label": "Adhérent à jour 2023"}
                        ],
                        "static_tags": [
                            {"code": "national_event:event-national-2:2023-06-15", "label": "Participant événement national 2023"}
                        ],
                        "elect_tags": null,
                        "instances": [
                            {"type": "assembly", "code": "92", "name": "Hauts-de-Seine (92)"}
                        ],
                        "subscriptions": {
                            "sms": {"available": false, "subscribed": false},
                            "web": {"available": true, "subscribed": true},
                            "email": {"available": true, "subscribed": true},
                            "mobile": {"available": true, "subscribed": true}
                        },
                        "roles": [
                            {"code": "correspondent", "label": "Responsable local", "is_delegated": false, "function": null, "zones": "Hauts-de-Seine", "zone_codes": "92"}
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
                    "type": "cb",
                    "type_label": "Carte bleue",
                    "subscription": false,
                    "membership": true,
                    "status": "finished",
                    "uuid": "@uuid@"
                },
                {
                    "date": "@string@.isDateTime()",
                    "amount": 30,
                    "type": "cb",
                    "type_label": "Carte bleue",
                    "subscription": false,
                    "membership": true,
                    "status": "finished",
                    "uuid": "@uuid@"
                },
                {
                    "date": "@string@.isDateTime()",
                    "amount": 30,
                    "type": "cb",
                    "type_label": "Carte bleue",
                    "subscription": false,
                    "membership": true,
                    "status": "finished",
                    "uuid": "@uuid@"
                },
                {
                    "date": "@string@.isDateTime()",
                    "amount": 30,
                    "type": "cb",
                    "type_label": "Carte bleue",
                    "subscription": false,
                    "membership": true,
                    "status": "finished",
                    "uuid": "@uuid@"
                },
                {
                    "date": "2020-01-06T19:00:00+01:00",
                    "amount": 100,
                    "type": "cb",
                    "type_label": "Carte bleue",
                    "subscription": true,
                    "membership": false,
                    "status": "finished",
                    "uuid": "@uuid@"
                },
                {
                    "date": "2020-01-05T15:00:00+01:00",
                    "amount": 60,
                    "type": "cb",
                    "type_label": "Carte bleue",
                    "subscription": true,
                    "membership": false,
                    "status": "finished",
                    "uuid": "@uuid@"
                },
                {
                    "date": "2020-01-04T12:30:00+01:00",
                    "amount": 40,
                    "type": "cb",
                    "type_label": "Carte bleue",
                    "subscription": false,
                    "membership": false,
                    "status": "finished",
                    "uuid": "@uuid@"
                },
                {
                    "date": "2019-12-05T15:00:00+01:00",
                    "amount": 60,
                    "type": "cb",
                    "type_label": "Carte bleue",
                    "subscription": true,
                    "membership": false,
                    "status": "finished",
                    "uuid": "@uuid@"
                },
                {
                    "date": "2019-12-04T12:00:00+01:00",
                    "amount": 50,
                    "type": "cb",
                    "type_label": "Carte bleue",
                    "subscription": false,
                    "membership": false,
                    "status": "finished",
                    "uuid": "@uuid@"
                }
            ]
            """
