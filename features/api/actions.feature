@api
@renaissance
Feature:
    In order to get and manipulate actions from API
    As a client of different apps
    I should be able to access to actions API

    Scenario: As a logged-in VOX user I can create and update an actions
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        And I send a "POST" request to "/api/v3/actions?scope=president_departmental_assembly" with body:
            """
            {
                "type": "pap",
                "date": "2024-06-01 10:00:00",
                "description": "<p>Bonjour, voici une description</p><ul><li>élément 1</li><li>élément 2</li></ul>",
                "post_address": {
                    "address": "92 bd Victor Hugo",
                    "postal_code": "92110",
                    "city_name": "Clichy",
                    "country": "FR"
                }
            }
            """
        Then the response status code should be 201
        And the JSON should be equal to:
            """
            {
                "type": "pap",
                "date": "@string@.isDateTime()",
                "uuid": "@uuid@",
                "status": "scheduled",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()",
                "user_registered_at": "@string@.isDateTime()",
                "participants": [
                    {
                        "is_present": false,
                        "adherent": {
                            "uuid": "9fec3385-8cfb-46e8-8305-c9bae10e4517",
                            "first_name": "Damien",
                            "last_name": "Durock",
                            "image_url": null
                        },
                        "uuid": "@uuid@",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    }
                ],
                "description": "<p>Bonjour, voici une description</p><ul><li>élément 1</li><li>élément 2</li></ul>",
                "post_address": {
                    "address": "92 bd Victor Hugo",
                    "postal_code": "92110",
                    "city": "92110-92024",
                    "city_name": "Clichy",
                    "country": "FR",
                    "latitude": 48.901058,
                    "longitude": 2.318325
                },
                "author": {
                    "uuid": "@uuid@",
                    "first_name": "Damien",
                    "last_name": "Durock",
                    "image_url": null,
                    "scope": "president_departmental_assembly",
                    "role": "Président",
                    "zone": "Hauts-de-Seine",
                    "instance": "Assemblée départementale",
                    "theme": {
                        "active": "#1C5CD8",
                        "hover": "#2F6FE0",
                        "primary": "#3A7DFF",
                        "soft": "#E8F0FF"
                    }
                },
                "zones": [
                    {
                        "uuid": "e3f0ee1a-906e-11eb-a875-0242ac150002",
                        "type": "district",
                        "code": "92-5",
                        "name": "Hauts-de-Seine (5)",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    },
                    {
                        "uuid": "e3f2c4a0-906e-11eb-a875-0242ac150002",
                        "type": "city",
                        "code": "92024",
                        "name": "Clichy",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    }
                ],
                "editable": true
            }
            """
        And I should have 1 notification "ActionCreatedNotification" with data:
            | key   | value                                                                                                 |
            | data  | {"link":"http://vox.code/actions?uuid=@uuid@"}                                                        |
            | scope | zone:92                                                                                               |
            | title | 🚪 Porte à porte le 1 juin à Clichy                                                                   |
            | body  | Damien vient de créer une nouvelle action de porte à porte le samedi 1 juin à 10h00 à Clichy (92110). |
        When I save this response
        And I send a "PUT" request to "/api/v3/actions/:last_response.uuid:?scope=president_departmental_assembly" with body:
            """
            {
                "type": "boitage"
            }
            """
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "type": "boitage",
                "date": "@string@.isDateTime()",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()",
                "status": "scheduled",
                "user_registered_at": "@string@.isDateTime()",
                "participants": [
                    {
                        "is_present": false,
                        "adherent": {
                            "uuid": "9fec3385-8cfb-46e8-8305-c9bae10e4517",
                            "first_name": "Damien",
                            "last_name": "Durock",
                            "image_url": null
                        },
                        "uuid": "@uuid@",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    }
                ],
                "description": "<p>Bonjour, voici une description</p><ul><li>élément 1</li><li>élément 2</li></ul>",
                "post_address": {
                    "address": "92 bd Victor Hugo",
                    "postal_code": "92110",
                    "city": "92110-92024",
                    "city_name": "Clichy",
                    "country": "FR",
                    "latitude": 48.901058,
                    "longitude": 2.318325
                },
                "author": {
                    "uuid": "@uuid@",
                    "first_name": "Damien",
                    "last_name": "Durock",
                    "image_url": null,
                    "scope": "president_departmental_assembly",
                    "role": "Président",
                    "zone": "Hauts-de-Seine",
                    "instance": "Assemblée départementale",
                    "theme": {
                        "active": "#1C5CD8",
                        "hover": "#2F6FE0",
                        "primary": "#3A7DFF",
                        "soft": "#E8F0FF"
                    }
                },
                "zones": [
                    {
                        "uuid": "e3f0ee1a-906e-11eb-a875-0242ac150002",
                        "type": "district",
                        "code": "92-5",
                        "name": "Hauts-de-Seine (5)",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    },
                    {
                        "uuid": "e3f2c4a0-906e-11eb-a875-0242ac150002",
                        "type": "city",
                        "code": "92024",
                        "name": "Clichy",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    }
                ],
                "editable": true
            }
            """
        And I should have 1 notification "ActionUpdatedNotification" with data:
            | key   | value                                                                              |
            | data  | {"link":"http://vox.code/actions?uuid=@uuid@"}                                     |
            | scope | action:@number@                                                                    |
            | title | 📬 Boitage le 1 juin à Clichy                                                      |
            | body  | Le boitage du samedi 1 juin à 10h00 auquel vous êtes inscrit vient d'être modifié. |
        When I send a "DELETE" request to "/api/v3/actions/:last_response.uuid:/register"
        Then the response status code should be 400
        And the JSON node "message" should be equal to "Vous ne pouvez pas vous désinscrire d'une action que vous avez créé."

    Scenario: As a logged-in VOX user I can get actions around me
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        And I send a "GET" request to "/api/v3/actions?latitude=48.866667&longitude=2.333333"
        Then the response status code should be 200
        And the JSON node "metadata.items_per_page" should be equal to "300"
        And the JSON node "metadata.total_items" should be equal to "50"
        When I send a "GET" request to "/api/v3/actions?latitude=48.866667&longitude=2.333333&subscribedOnly=1"
        Then the response status code should be 200
        And the JSON node "metadata.items_per_page" should be equal to "300"
        And the JSON node "metadata.total_items" should be equal to "25"
