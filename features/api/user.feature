@api
@renaissance
Feature:
    As a logged-in user
    I should be able to access my informations

    Scenario: As a non logged-in user I cannot get my information
        When I send a "GET" request to "/api/me"
        Then the response status code should be 401

    Scenario: As a logged-in user I can get my informations with additional informations based on granted scope
        Given I am logged with "carl999@example.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/me"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "id": "@string@-@string@",
                "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                "email_address": "carl999@example.fr",
                "email_subscribed": true,
                "first_name": "Carl",
                "last_name": "Mirabeau",
                "image_url": null,
                "country": "FR",
                "postal_code": "77190",
                "nickname": "pont",
                "use_nickname": false,
                "certified": false,
                "surveys": {
                    "total": 0,
                    "last_month": 0
                },
                "tags": [
                    {
                        "code": "sympathisant:compte_em",
                        "label": "Ancien compte En Marche",
                        "type": "sympathisant"
                    }
                ],
                "cadre_access": false,
                "canary_tester": false,
                "instances": {
                    "assembly": {
                        "type": "assembly",
                        "code": "77",
                        "name": "Seine-et-Marne (77)",
                        "manager": {
                            "uuid": "@uuid@",
                            "public_id": "@string@",
                            "first_name": "Referent",
                            "last_name": "Referent",
                            "image_url": null,
                            "role": "Président d'Assemblée"
                        }
                    },
                    "circonscription": null,
                    "committee": {
                        "type": "committee",
                        "name": "En Marche Paris 8",
                        "assembly_committees_count": 2,
                        "can_change_committee": true,
                        "members_count": 1,
                        "message": null,
                        "uuid": "@uuid@",
                        "manager": null
                    },
                    "agoras": [
                        {
                            "type": "agora",
                            "uuid": "@uuid@",
                            "name": "Première Agora",
                            "slug": "premiere-agora",
                            "description": "Description première Agora",
                            "max_members_count": 10,
                            "members_count": 2,
                            "manager": {
                                "uuid": "@uuid@",
                                "public_id": "@string@",
                                "first_name": "Michelle",
                                "last_name": "Dufour",
                                "image_url": null,
                                "role": "Présidente d'Agora"
                            }
                        }
                    ]
                },
                "referral_link": "https://adhesion.renaissance.code/@string@-@string@"
            }
            """

        When I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        And I send a "GET" request to "/api/me"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "id": "@string@-@string@",
                "nickname": "kikouslove",
                "email_address": "jacques.picard@en-marche.fr",
                "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                "first_name": "Jacques",
                "last_name": "Picard",
                "image_url": null,
                "use_nickname": true,
                "certified": true,
                "email_subscribed": true,
                "country": "FR",
                "postal_code": "75008",
                "surveys": {
                    "total": 7,
                    "last_month": 7
                },
                "tags": [
                    {
                        "code": "adherent:a_jour_2026:recotisation",
                        "label": "Adhérent à jour 2026",
                        "type": "adherent"
                    }
                ],
                "cadre_access": true,
                "canary_tester": false,
                "cadre_auth_path": "/oauth/v2/auth?scope=jemengage_admin&response_type=code&client_id=4498e44f-f214-110d-8b76-98a83f9d2b0c&redirect_uri=http%3A%2F%2Flocalhost%3A3000%2Fauth",
                "instances": {
                    "assembly": {
                        "type": "assembly",
                        "code": "75",
                        "name": "Paris (75)",
                        "manager": {
                            "uuid": "@uuid@",
                            "public_id": "@string@",
                            "first_name": "Referent75and77",
                            "last_name": "Referent75and77",
                            "image_url": null,
                            "role": "Présidente d'Assemblée"
                        }
                    },
                    "circonscription": {
                        "type": "circonscription",
                        "code": "75-1",
                        "name": "1ère circonscription • Paris (75-1)",
                        "manager": {
                            "uuid": "@uuid@",
                            "public_id": "@string@",
                            "first_name": "Député",
                            "last_name": "PARIS I",
                            "image_url": null,
                            "role": "Délégué de circonscription"
                        }
                    },
                    "committee": {
                        "type": "committee",
                        "name": "En Marche Paris 8",
                        "assembly_committees_count": 2,
                        "can_change_committee": true,
                        "members_count": 1,
                        "message": null,
                        "uuid": "@uuid@",
                        "manager": null
                    },
                    "agoras": []
                },
                "referral_link": "https://adhesion.renaissance.code/@string@-@string@"
            }
            """

    Scenario: As a logged-in device I can get my information with additional information based on granted scope
        Given I am logged with device "dd4SOCS-4UlCtO-gZiQGDA" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/me"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "@string@",
                "device_uuid": "dd4SOCS-4UlCtO-gZiQGDA",
                "postal_code": null
            }
            """

