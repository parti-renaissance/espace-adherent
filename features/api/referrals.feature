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
                "national": [
                    {
                        "nb_referral": 2,
                        "firstName": "Lucie",
                        "lastNameInitial": "O.",
                        "position": 1
                    },
                    {
                        "nb_referral": 1,
                        "firstName": "Jacques",
                        "lastNameInitial": "P.",
                        "position": 2
                    }
                ],
                "national_rank": 2,
                "assembly": [
                    {
                        "nb_referral": 2,
                        "firstName": "Lucie",
                        "lastNameInitial": "O.",
                        "position": 1
                    },
                    {
                        "nb_referral": 1,
                        "firstName": "Jacques",
                        "lastNameInitial": "P.",
                        "position": 2
                    }
                ],
                "assembly_rank": 2
            }
            """
