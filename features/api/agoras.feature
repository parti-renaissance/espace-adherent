@api
@renaissance
Feature:
    In order to see and join Agoras
    I should be able to access API of Agoras

    Scenario: As a non logged-in user I can not see Agoras
        When I send a "GET" request to "/api/v3/agoras"
        Then the response status code should be 401

    Scenario: As a logged-in user I can retrieve active Agoras
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/agoras"
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
                        "uuid": "82ad6422-cb82-4c04-b478-bfb421c740e0",
                        "name": "Première Agora",
                        "slug": "premiere-agora",
                        "description": "Description première Agora",
                        "max_members_count": 2,
                        "members_count": 2,
                        "published": true,
                        "president": {
                            "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9697",
                            "id": "@string@",
                            "first_name": "Michelle",
                            "last_name": "Dufour",
                            "image_url": null
                        },
                        "general_secretaries": [
                            {
                                "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                                "id": "@string@",
                                "first_name": "Jacques",
                                "last_name": "Picard",
                                "image_url": null
                            }
                        ],
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    },
                    {
                        "uuid": "75d47004-db80-4586-8fc5-e97cec58e5b4",
                        "name": "Deuxième Agora",
                        "slug": "deuxieme-agora",
                        "description": "Description deuxième Agora",
                        "max_members_count": 40,
                        "members_count": 0,
                        "published": true,
                        "president": {
                            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                            "id": "@string@",
                            "first_name": "Jacques",
                            "last_name": "Picard",
                            "image_url": null
                        },
                        "general_secretaries": [
                            {
                                "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9697",
                                "id": "@string@",
                                "first_name": "Michelle",
                                "last_name": "Dufour",
                                "image_url": null
                            },
                            {
                                "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                                "id": "@string@",
                                "first_name": "Lucie",
                                "image_url": null,
                                "last_name": "Olivera"
                            }
                        ],
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    }
                ]
            }
            """

    Scenario: As a logged-in user I can filter Agoras by name
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/agoras?name=Première"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 1,
                    "items_per_page": 2,
                    "count": 1,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "uuid": "82ad6422-cb82-4c04-b478-bfb421c740e0",
                        "name": "Première Agora",
                        "slug": "premiere-agora",
                        "description": "Description première Agora",
                        "max_members_count": 2,
                        "members_count": 2,
                        "published": true,
                        "president": {
                            "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9697",
                            "id": "@string@",
                            "first_name": "Michelle",
                            "last_name": "Dufour",
                            "image_url": null
                        },
                        "general_secretaries": [
                            {
                                "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                                "id": "@string@",
                                "first_name": "Jacques",
                                "last_name": "Picard",
                                "image_url": null
                            }
                        ],
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    }
                ]
            }
            """

    Scenario: As a logged-in user (not adherent) I can not join an Agora
        Given I am logged with "carl999@example.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "POST" request to "/api/v3/agoras/82ad6422-cb82-4c04-b478-bfb421c740e0/join"
        Then the response status code should be 403

    Scenario: As a logged-in adherent I can not join an Agora I am already member of
        Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "POST" request to "/api/v3/agoras/82ad6422-cb82-4c04-b478-bfb421c740e0/join"
        Then the response status code should be 400
        And the JSON should be equal to:
            """
            {
                "status": "error",
                "message": "Vous êtes déjà membre de cette Agora."
            }
            """

    Scenario: As a logged-in adherent I can not join an unpublished Agora
        Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "POST" request to "/api/v3/agoras/c3d0fb57-1ce9-441a-9978-8445fc01fa5c/join"
        Then the response status code should be 404

    Scenario: As a logged-in adherent I can not join an Agora that is already full of members
        Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "POST" request to "/api/v3/agoras/82ad6422-cb82-4c04-b478-bfb421c740e0/join"
        Then the response status code should be 400
        And the JSON should be equal to:
            """
            {
                "status": "error",
                "message": "La limite de membres pour cette Agora a été atteinte."
            }
            """

    Scenario: As a logged-in adherent I can join an Agora
        Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "POST" request to "/api/v3/agoras/75d47004-db80-4586-8fc5-e97cec58e5b4/join"
        Then the response status code should be 201
        And the JSON should be equal to:
            """
            "OK"
            """

    Scenario: As a logged-in adherent I can not leave an Agora I am not member of
        Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "DELETE" request to "/api/v3/agoras/82ad6422-cb82-4c04-b478-bfb421c740e0/leave"
        Then the response status code should be 400
        And the JSON should be equal to:
            """
            {
                "status": "error",
                "message": "Vous n'êtes pas membre de cette Agora."
            }
            """

    Scenario: As a logged-in user I can leave an Agora
        Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "DELETE" request to "/api/v3/agoras/82ad6422-cb82-4c04-b478-bfb421c740e0/leave"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            "OK"
            """

    Scenario: As a logged-in Agora Manager I can see the Agoras I am manager of
        Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/agoras?scope=agora_manager"
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
                        "uuid": "75d47004-db80-4586-8fc5-e97cec58e5b4",
                        "name": "Deuxième Agora",
                        "slug": "deuxieme-agora",
                        "description": "Description deuxième Agora",
                        "max_members_count": 40,
                        "members_count": 0,
                        "published": true,
                        "president": {
                            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                            "id": "@string@",
                            "first_name": "Jacques",
                            "last_name": "Picard",
                            "image_url": null
                        },
                        "general_secretaries": [
                            {
                                "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9697",
                                "id": "@string@",
                                "first_name": "Michelle",
                                "last_name": "Dufour",
                                "image_url": null
                            },
                            {
                                "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                                "id": "@string@",
                                "first_name": "Lucie",
                                "last_name": "Olivera",
                                "image_url": null
                            }
                        ],
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    },
                    {
                        "uuid": "c3d0fb57-1ce9-441a-9978-8445fc01fa5c",
                        "name": "Agora non publiée",
                        "slug": "agora-non-publiee",
                        "description": "Description Agora non publiée",
                        "max_members_count": 30,
                        "members_count": 0,
                        "published": false,
                        "president": {
                            "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                            "id": "@string@",
                            "first_name": "Lucie",
                            "last_name": "Olivera",
                            "image_url": null
                        },
                        "general_secretaries": [],
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    }
                ]
            }
            """

    Scenario: As a logged-in Agora Manager I can see the members of an Agora
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents?scope=agora_manager&agora_uuids[]=82ad6422-cb82-4c04-b478-bfb421c740e0"
        Then the response status code should be 200
        And the JSON should be a superset of:
            """
            {
                "items": [
                    {
                        "agora": "Première Agora",
                        "agora_uuid": "82ad6422-cb82-4c04-b478-bfb421c740e0"
                    }
                ]
            }
            """
