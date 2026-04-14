@app
@renaissance_user
Feature: Using OAuth for 2-legged OAuth flow (client credentials)
    In order to secure API or user data access
    As an API or an En-Marche! user
    I need to be able to access API data

    Scenario: OAuth client_id is malformed
        Given I am logged as "simple-user@example.ch"
        When I am on "/oauth/v2/auth?response_type=code&client_id=-af6d-4ce4-8239-04cfcefd5a19"
        Then the response status code should be 401
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "error": "invalid_client",
                "error_description": "Client authentication failed"
            }
            """

    Scenario: Retrieve authorization code on redirect URI after login
        Given I send a "GET" request to "/oauth/v2/auth" with parameters:
            | key           | value                                |
            | client_id     | 138140b3-1dd2-11b2-ad7e-2348ad4fef66 |
            | response_type | code                                 |
        Then I should be redirected to "/connexion"
        When I fill in the following:
            | _username | carl999@example.fr |
            | _password | secret!12345       |
        And I press "Me connecter"
        Then I should be on "/oauth/v2/auth"
        And the response status code should be 200
        And I should see "Je me connecte à Coalition App avec mon compte En Marche."
        Given I stop following redirections
        Then I press "Accepter"
        Then the response status code should be 302
        And the header "Location" should match "#^http://client-oauth.docker:8000/client/receive_authcode\?code=.+$#"

    Scenario: Tokeninfo for invalid token
        When I send a "GET" request to "/oauth/v2/tokeninfo"
        Then the response should be in JSON
        And the response status code should be 400
        And the JSON should be equal to:
            """
            {
                "message": "No access_token provided"
            }
            """

