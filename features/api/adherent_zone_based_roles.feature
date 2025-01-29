@api
Feature:
    In order to manage adherent zone based roles
    As a logged-in user granted with a specific feature
    I should be able to create, read and update adherent zone based roles

    Scenario Outline: As a user granted with local scope, I can create an adherent zone based role
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "POST" request to "/api/v3/zone_based_role?scope=<scope>" with body:
            """
            {
                "adherent": "d0a0935f-da7c-4caa-b582-a8c2376e5158",
                "type": "deputy",
                "zones": ["e3efe6fd-906e-11eb-a875-0242ac150002"]
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "type": "deputy",
                "adherent": {
                    "uuid": "d0a0935f-da7c-4caa-b582-a8c2376e5158",
                    "zones": [
                        {
                            "uuid": "@uuid@",
                            "type": "department",
                            "code": "77",
                            "name": "Seine-et-Marne"
                        }
                    ]
                },
                "uuid": "@uuid@",
                "zones": [
                    {
                        "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                        "code": "92",
                        "name": "Hauts-de-Seine",
                        "type": "department"
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
