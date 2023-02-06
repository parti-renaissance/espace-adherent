@api
Feature:
  In order to manager elected representatives
  As a logged-in user
  I should be able to access elected representatives API

  Scenario Outline: As a user granted with local scope, I can get elected representatives in a zone I am manager of
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    And I send a "GET" request to "/api/v3/elected_representatives?scope=<scope>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 3,
            "items_per_page": 100,
            "count": 3,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "last_name": "92",
                "first_name": "Département",
                "gender": "male",
                "contact_phone": null,
                "uuid": "09638957-3a4a-4c2e-93d2-a7b0a56d9487",
                "current_mandates": [
                    {
                        "type": "senateur",
                        "geo_zone": {
                            "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                            "code": "92",
                            "name": "Hauts-de-Seine"
                        }
                    }
                ],
                "current_political_functions": []
            },
            {
                "last_name": "DUFOUR",
                "first_name": "Michelle",
                "gender": "female",
                "contact_phone": null,
                "uuid": "34b0b236-b72e-4161-8f9f-7f23f935758f",
                "current_mandates": [
                    {
                        "type": "conseiller_municipal",
                        "geo_zone": {
                            "uuid": "e3f18016-906e-11eb-a875-0242ac150002",
                            "code": "200054781",
                            "name": "Métropole du Grand Paris"
                        }
                    }
                ],
                "current_political_functions": {
                    "1": {
                        "name": "other_member"
                    }
                }
            },
            {
                "last_name": "LOBELL",
                "first_name": "André",
                "gender": "male",
                "contact_phone": null,
                "uuid": "82ec811a-45f7-4527-97ef-3dea61af131b",
                "current_mandates": [
                    {
                        "type": "depute",
                        "geo_zone": {
                            "uuid": "e3f01553-906e-11eb-a875-0242ac150002",
                            "code": "13",
                            "name": "Bouches-du-Rhône"
                        }
                    },
                    {
                        "type": "conseiller_regional",
                        "geo_zone": {
                            "uuid": "e3f28ca9-906e-11eb-a875-0242ac150002",
                            "code": "76540",
                            "name": "Rouen"
                        }
                    }
                ],
                "current_political_functions": {
                    "1": {
                        "name": "vice_president_of_epci"
                    },
                    "2": {
                        "name": "mayor_assistant"
                    }
                }
            }
        ]
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
