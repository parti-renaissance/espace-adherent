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
                        "mode": "email",
                        "status": "invitation_sent"
                    },
                    {
                        "uuid": "2055b072-73f4-46c3-a9ab-1fb617c464f1",
                        "identifier": "@string@.matchRegex('/^P[A-Z0-9]{5}$/')",
                        "email_address": "john.doe@dev.test",
                        "first_name": "John",
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
                        "mode": "email",
                        "status": "invitation_sent"
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
                        "mode": "email",
                        "status": "reported"
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
                "mode": "email",
                "status": "invitation_sent"
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
                                    "content": "http://test.renaissance.code/invitation/adhesion/@string@",
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
                "mode": "email",
                "status": "invitation_sent"
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
