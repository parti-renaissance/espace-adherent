@api
@renaissance
Feature:
    In order to get and manipulate actions from API
    As a client of different apps
    I should be able to access to actions API

    Scenario: As a logged-in VOX user I can create and update an actions
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        And I send a "POST" request to "/api/v3/actions" with body:
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
                "last_name": "Durock"
            },
            "zones": [
                {
                    "uuid": "e3f2c4a0-906e-11eb-a875-0242ac150002",
                    "type": "city",
                    "code": "92024",
                    "name": "Clichy"
                },
                {
                    "uuid": "e3f0ee1a-906e-11eb-a875-0242ac150002",
                    "type": "district",
                    "code": "92-5",
                    "name": "Hauts-de-Seine (5)"
                }
            ]
        }
        """
        When I save this response
        And I send a "PUT" request to "/api/v3/actions/:last_response.uuid:" with body:
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
                "last_name": "Durock"
            },
            "zones": [
                {
                    "uuid": "e3f2c4a0-906e-11eb-a875-0242ac150002",
                    "type": "city",
                    "code": "92024",
                    "name": "Clichy"
                },
                {
                    "uuid": "e3f0ee1a-906e-11eb-a875-0242ac150002",
                    "type": "district",
                    "code": "92-5",
                    "name": "Hauts-de-Seine (5)"
                }
            ]
        }
        """

    Scenario: As a logged-in VOX simple user without scope I cannot create an actions
        Given I am logged with "je-mengage-user-1@en-marche-dev.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        And I send a "POST" request to "/api/v3/actions" with body:
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
        Then the response status code should be 403
