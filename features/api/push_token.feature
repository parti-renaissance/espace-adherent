@api
@renaissance
@debug
Feature:
    In order to track push tokens
    As a logged-in adherent or device
    I should be able to post and delete push tokens

    Scenario: As a logged-in adherent I can add and remove a push token
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "POST" request to "/api/v3/push-token" with body:
            """
            {
                "identifier": "abc123",
                "source": "je_marche"
            }
            """
        Then the response status code should be 201

        When I send a "POST" request to "/api/v3/push-token/unsubscribe"
        Then the response status code should be 200
