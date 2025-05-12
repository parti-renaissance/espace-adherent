@api
Feature:
    In order to display referrals informations
    As an logged in user
    I should be able to list and read referrals

    Scenario: As an logged in user, I can list referrals
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        And I send a "GET" request to "/api/v3/referrals?page_size=10"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 3,
                    "items_per_page": 10,
                    "count": 3,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "uuid": "abeb6804-a88b-478a-8859-0c5e2f549d17",
                        "identifier": "@string@.matchRegex('/^P[A-Z0-9]{5}$/')",
                        "email_address": "jean.martin@dev.test",
                        "first_name": "Jean",
                        "last_name": null,
                        "civility": null,
                        "birthdate": null,
                        "nationality": null,
                        "post_address": {
                            "address": null,
                            "additional_address": null,
                            "city": null,
                            "city_name": null,
                            "country": null,
                            "postal_code": null,
                            "region": null
                        },
                        "phone": null,
                        "referred": null,
                        "type": "invitation",
                        "type_label": "Invitation",
                        "mode": "email",
                        "mode_label": "Email",
                        "status": "invitation_sent",
                        "status_label": "Invitation envoyée",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    },
                    {
                        "uuid": "2055b072-73f4-46c3-a9ab-1fb617c464f1",
                        "identifier": "@string@.matchRegex('/^P[A-Z0-9]{5}$/')",
                        "email_address": "john.doe@dev.test",
                        "first_name": "John",
                        "last_name": "Doe",
                        "civility": "Monsieur",
                        "birthdate": "@string@.isDateTime()",
                        "nationality": "FR",
                        "post_address": {
                            "address": "68 rue du Rocher",
                            "additional_address": null,
                            "city": "75008-75108",
                            "city_name": "Paris 8ème",
                            "country": "FR",
                            "postal_code": "75008",
                            "region": null
                        },
                        "phone": null,
                        "referred": null,
                        "type": "preregistration",
                        "type_label": "Pré-inscription",
                        "mode": "email",
                        "mode_label": "Email",
                        "status": "invitation_sent",
                        "status_label": "Invitation envoyée",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    },
                    {
                        "uuid": "34abd1e0-46e3-4c02-a4ad-8f632e03f7ce",
                        "email_address": "jane.doe@dev.test",
                        "identifier": "@string@.matchRegex('/^P[A-Z0-9]{5}$/')",
                        "first_name": "Jane",
                        "last_name": null,
                        "civility": null,
                        "birthdate": null,
                        "nationality": null,
                        "post_address": {
                            "additional_address": null,
                            "address": null,
                            "city": null,
                            "city_name": null,
                            "country": null,
                            "postal_code": null,
                            "region": null
                        },
                        "phone": null,
                        "referred": null,
                        "type": "invitation",
                        "type_label": "Invitation",
                        "mode": "email",
                        "mode_label": "Email",
                        "status": "reported",
                        "status_label": "Signalé",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    }
                ]
            }
            """

    Scenario: As an logged in user, I can create a new referral with only email and first name
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        And I send a "POST" request to "/api/v3/referrals" with body:
            """
            {
                "email_address": "new-email@dev.test",
                "first_name": "Jane"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "@uuid@",
                "identifier": "@string@.matchRegex('/^P[A-Z0-9]{5}$/')",
                "email_address": "new-email@dev.test",
                "first_name": "Jane",
                "last_name": null,
                "civility": null,
                "birthdate": null,
                "nationality": null,
                "post_address": {
                    "address": null,
                    "additional_address": null,
                    "city": null,
                    "city_name": null,
                    "country": null,
                    "postal_code": null,
                    "region": null
                },
                "phone": null,
                "referred": null,
                "type": "invitation",
                "type_label": "Invitation",
                "mode": "email",
                "mode_label": "Email",
                "status": "invitation_sent",
                "status_label": "Invitation envoyée",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()"
            }
            """
        And I should have 1 email "ReferralAdhesionCreatedMessage" for "new-email@dev.test" with payload:
            """
            {
                "template_name": "referral-adhesion-created",
                "template_content": [],
                "message": {
                    "subject": "Nouveau parrainage",
                    "from_email": "ne-pas-repondre@parti-renaissance.fr",
                    "html": null,
                    "merge_vars": [
                        {
                            "rcpt": "new-email@dev.test",
                            "vars": [
                                {
                                    "content": "Michelle",
                                    "name": "referrer_first_name"
                                },
                                {
                                    "content": "Jane",
                                    "name": "referred_first_name"
                                },
                                {
                                    "content": "http://test.renaissance.code/invitation/@string@",
                                    "name": "adhesion_link"
                                },
                                {
                                    "content": "http://test.renaissance.code/invitation/@string@/signaler",
                                    "name": "report_link"
                                }
                            ]
                        }
                    ],
                    "from_name": "Renaissance",
                    "to": [
                        {
                            "email": "new-email@dev.test",
                            "type": "to",
                            "name": "Jane"
                        }
                    ]
                }
            }
            """

    Scenario: As an logged in user, I can create a new referral with all informations
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        And I send a "POST" request to "/api/v3/referrals" with body:
            """
            {
                "email_address": "new-email@dev.test",
                "first_name": "Jane",
                "last_name": "Doe",
                "civility": "Madame",
                "birthdate": "2001-03-25",
                "nationality": "FR",
                "post_address": {
                    "address": "8 rue Jane Doe",
                    "postal_code": "92270",
                    "city_name": "Bois-Colombes",
                    "country": "FR"
                },
                "phone": "+33601234567"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "@uuid@",
                "identifier": "@string@.matchRegex('/^P[A-Z0-9]{5}$/')",
                "email_address": "new-email@dev.test",
                "first_name": "Jane",
                "last_name": "Doe",
                "civility": "Madame",
                "birthdate": "2001-03-25T00:00:00+01:00",
                "nationality": "FR",
                "post_address": {
                    "address": "8 rue Jane Doe",
                    "additional_address": null,
                    "postal_code": "92270",
                    "city": "92270-92009",
                    "city_name": "Bois-Colombes",
                    "country": "FR",
                    "region": null
                },
                "phone": "+33 6 01 23 45 67",
                "referred": null,
                "type": "preregistration",
                "type_label": "Pré-inscription",
                "mode": "email",
                "mode_label": "Email",
                "status": "invitation_sent",
                "status_label": "Invitation envoyée",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()"
            }
            """

    Scenario: As an logged in user, I can not create a new referral with partial informations
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        And I send a "POST" request to "/api/v3/referrals" with body:
            """
            {
                "email_address": "new-email@dev.test",
                "first_name": "Jane",
                "last_name": "Doe"
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "message": "Veuillez remplir toutes les informations de préinscription (Civilité, Nom, Adresse postale, Nationalité).",
                        "propertyPath": ""
                    }
                ]
            }
            """

    Scenario Outline: As an logged in user, I can not create a new referral with an invalid email address
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        And I send a "POST" request to "/api/v3/referrals" with body:
            """
            {
                "email_address": "<email>",
                "first_name": "John"
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "message": "Cette adresse email ne peut pas être invitée.",
                        "propertyPath": "email_address"
                    }
                ]
            }
            """

        Examples:
            | email                   |
            | carl999@example.fr      |
            | disabled-email@test.com |

    Scenario: As an logged in user, I can get my referral statistics
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        And I send a "GET" request to "/api/v3/referrals/statistics"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "nb_referral_finished": 0,
                "nb_referral_sent": 3,
                "nb_referral_reported": 1
            }
            """

    Scenario: As an logged in user, I can get my referral scoreboard
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        And I send a "GET" request to "/api/v3/referrals/scoreboard"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "global": [
                    {
                        "referrals_count": 2,
                        "first_name": "Lucie",
                        "last_name": "O.",
                        "assembly": "Paris (75)",
                        "position": 1
                    },
                    {
                        "referrals_count": 1,
                        "first_name": "Jacques",
                        "last_name": "P.",
                        "assembly": "Paris (75)",
                        "position": 2
                    }
                ],
                "global_rank": 2,
                "assembly": [
                    {
                        "referrals_count": 2,
                        "first_name": "Lucie",
                        "last_name": "O.",
                        "assembly": "Paris (75)",
                        "position": 1
                    },
                    {
                        "referrals_count": 1,
                        "first_name": "Jacques",
                        "last_name": "P.",
                        "assembly": "Paris (75)",
                        "position": 2
                    }
                ],
                "assembly_rank": 2
            }
            """

    Scenario Outline: As a user with (delegated) referent role I can get referrals of my zones
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/referrals?scope=<scope>"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 3,
                    "items_per_page": 2,
                    "count": 2,
                    "current_page": 1,
                    "last_page": 2
                },
                "items": [
                    {
                        "email_address": null,
                        "first_name": "Jean",
                        "last_name": null,
                        "civility": null,
                        "nationality": null,
                        "phone": null,
                        "birthdate": null,
                        "referred": null,
                        "referrer": {
                            "id": "@string@",
                            "email_address": "@string@",
                            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                            "first_name": "Jacques",
                            "last_name": "Picard"
                        },
                        "identifier": "PAC123",
                        "type": "invitation",
                        "mode": "email",
                        "status": "adhesion_finished",
                        "uuid": "e12d55f2-2a27-49c9-92e5-818320f99749",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()",
                        "post_address": {
                            "address": null,
                            "additional_address": null,
                            "postal_code": null,
                            "city": null,
                            "city_name": null,
                            "country": null,
                            "region": null
                        },
                        "type_label": "Invitation",
                        "mode_label": "Email",
                        "status_label": "Adhésion finalisée"
                    },
                    {
                        "email_address": null,
                        "first_name": "Jane",
                        "last_name": null,
                        "civility": null,
                        "nationality": null,
                        "phone": null,
                        "birthdate": null,
                        "referred": null,
                        "referrer": {
                            "id": "@string@",
                            "email_address": "@string@",
                            "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                            "first_name": "Lucie",
                            "last_name": "Olivera"
                        },
                        "identifier": "PAC124",
                        "type": "invitation",
                        "mode": "email",
                        "status": "adhesion_finished",
                        "uuid": "680a34aa-8f03-4efc-a294-8e6c2bb669ab",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()",
                        "post_address": {
                            "address": null,
                            "additional_address": null,
                            "postal_code": null,
                            "city": null,
                            "city_name": null,
                            "country": null,
                            "region": null
                        },
                        "type_label": "Invitation",
                        "mode_label": "Email",
                        "status_label": "Adhésion finalisée"
                    }
                ]
            }
            """
        When I send a "GET" request to "/api/v3/referrals?scope=<scope>&status=adhesion_finished&referrer=lucie"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 2,
                    "items_per_page": 2,
                    "count": 2,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "email_address": null,
                        "first_name": "Jane",
                        "last_name": null,
                        "civility": null,
                        "nationality": null,
                        "phone": null,
                        "birthdate": null,
                        "referred": null,
                        "referrer": {
                            "id": "@string@",
                            "email_address": "@string@",
                            "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                            "first_name": "Lucie",
                            "last_name": "Olivera"
                        },
                        "identifier": "PAC124",
                        "type": "invitation",
                        "mode": "email",
                        "status": "adhesion_finished",
                        "uuid": "680a34aa-8f03-4efc-a294-8e6c2bb669ab",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()",
                        "post_address": {
                            "address": null,
                            "additional_address": null,
                            "postal_code": null,
                            "city": null,
                            "city_name": null,
                            "country": null,
                            "region": null
                        },
                        "type_label": "Invitation",
                        "mode_label": "Email",
                        "status_label": "Adhésion finalisée"
                    },
                    {
                        "email_address": null,
                        "first_name": "Didier",
                        "last_name": null,
                        "civility": null,
                        "nationality": null,
                        "phone": null,
                        "birthdate": null,
                        "referred": null,
                        "referrer": {
                            "id": "@string@",
                            "email_address": "@string@",
                            "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                            "first_name": "Lucie",
                            "last_name": "Olivera"
                        },
                        "identifier": "PAC125",
                        "type": "invitation",
                        "mode": "email",
                        "status": "adhesion_finished",
                        "uuid": "748e94b8-5316-4885-9f42-99b8aa037efa",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()",
                        "post_address": {
                            "address": null,
                            "additional_address": null,
                            "postal_code": null,
                            "city": null,
                            "city_name": null,
                            "country": null,
                            "region": null
                        },
                        "type_label": "Invitation",
                        "mode_label": "Email",
                        "status_label": "Adhésion finalisée"
                    }
                ]
            }
            """

        Examples:
            | user                            | scope                                          |
            | referent-75-77@en-marche-dev.fr | president_departmental_assembly                |
            | francis.brioul@yahoo.com        | delegated_689757d2-dea5-49d1-95fe-281fc860ff77 |

    Scenario Outline: As a user with (delegated) referent role I can get referral scoreboards of my zones
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "GET" request to "/api/v3/referrals/manager-scoreboard?scope=<scope>"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "75": [
                    {
                        "referrer_pid": "@string@",
                        "referrer_uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                        "referrer_first_name": "Lucie",
                        "referrer_last_name": "Olivera",
                        "count_adhesion_finished": 2,
                        "count_account_created": 0,
                        "count_reported": 0,
                        "local_rank": 1,
                        "national_rank": 1
                    },
                    {
                        "referrer_pid": "@string@",
                        "referrer_uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                        "referrer_first_name": "Jacques",
                        "referrer_last_name": "Picard",
                        "count_adhesion_finished": 1,
                        "count_account_created": 0,
                        "count_reported": 0,
                        "local_rank": 2,
                        "national_rank": 2
                    }
                ],
                "77": []
            }
            """

        Examples:
            | user                            | scope                                          |
            | referent-75-77@en-marche-dev.fr | president_departmental_assembly                |
            | francis.brioul@yahoo.com        | delegated_689757d2-dea5-49d1-95fe-281fc860ff77 |
