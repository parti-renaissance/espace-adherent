@api
@renaissance
Feature:
    As a logged-in user
    I should be able to retrieve magic link with redirections

    Scenario: As a logged-in user I can retrieve magic links based on a key
        Given I am logged with "carl999@example.fr" via OAuth client "JeMengage Mobile"
        When I send a "GET" request to "/api/v3/app-link/adhesion"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "url": "@string@.isUrl().matchRegex('/&_target_path=https?.+%2Fadhesion/')",
                "expires_at": "@string@.isDateTime()"
            }
            """
