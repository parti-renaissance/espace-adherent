@app
Feature: Using OAuth for 2-legged OAuth flow (client credentials)
  In order to secure API or user data access
  As an API or an En-Marche! user
  I need to be able to access API data

  Scenario: OAuth is not allowed for admin
    Given I am logged as "superadmin@en-marche-dev.fr" admin
    When I am on "/oauth/v2/auth?response_type=code&client_id=f80ce2df-af6d-4ce4-8239-04cfcefd5a19&redirect_uri=http%3A%2F%2Fclient-oauth.docker%3A8000%2Fclient%2Freceive_authcode&state=m94bmt522o81gtch7pj0kd7hdf"
    Then the response status code should be 403

  Scenario: OAuth client_id is malformed
    Given I am logged as "simple-user@example.ch"
    When I am on "/oauth/v2/auth?response_type=code&client_id=-af6d-4ce4-8239-04cfcefd5a19"
    Then the response status code should be 401
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "error":"invalid_client",
        "error_description":"Client authentication failed",
        "message":"Client authentication failed"
      }
    """

  Scenario: Retrieve authorization code on redirect URI after login
    Given I send a "GET" request to "/oauth/v2/auth" with parameters:
      | key           | value                                |
      | client_id     | 138140b3-1dd2-11b2-ad7e-2348ad4fef66 |
      | response_type | code                                 |
    Then I should be redirected to "/connexion"
    Then I should see "Connectez-vous avec votre compte"
    When I fill in the following:
      | _login_email    | carl999@example.fr |
      | _login_password | secret!12345       |
    And I press "Connexion"
    Then I should be on "/oauth/v2/auth"
    And the response status code should be 200
    And I should see "Je me connecte à Coalition App avec mon compte En Marche."
    Given I stop following redirections
    Then I press "Accepter"
    Then the response status code should be 302
    And the header "Location" should match "#^http://client-oauth.docker:8000/client/receive_authcode\?code=.+$#"

  Scenario: Client credentials authentication
    Given I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | dALH/khq9BcjOS0GB6u5NaJ3R9k2yvSBq5wYUHx1omA= |
      | client_id     | 4122f4ce-f994-45f7-9ff5-f9f09ab3991e         |
      | grant_type    | client_credentials                           |
      | scope         | read:users                                   |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "token_type":"Bearer",
      "expires_in":"@integer@.lowerThan(3601).greaterThan(3595)",
      "access_token":"@string@"
    }
    """

    When I send a "GET" request to "/oauth/v2/tokeninfo" with the access token
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "token_type":"Bearer",
      "expires_in":@integer@,
      "access_token":"@string@",
      "grant_types": ["client_credentials"],
      "scopes": ["read:users"]
    }
    """

    Given I add the access token to the Authorization header
    When I send a "GET" request to "/api/me"
    Then the response status code should be 401
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "error": "access_denied",
      "error_description": "The resource owner or authorization server denied the request.",
      "message": "The resource owner or authorization server denied the request.",
      "hint": "API user does not have access to this route"
    }
    """

  Scenario: Client credentials authentication with device id
    Given I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | MWFod6bOZb2mY3wLE=4THZGbOfHJvRHk8bHdtZP3BTr  |
      | client_id     | 1931b955-560b-41b2-9eb9-c232157f1471         |
      | grant_type    | client_credentials                           |
      | scope         | jemarche_app                                 |
      | device_id     | dd4SOCS-4UlCtO-gZiQGDA                       |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "token_type":"Bearer",
      "expires_in":"@integer@.lowerThan(3601).greaterThan(3595)",
      "access_token":"@string@"
    }
    """

    When I send a "GET" request to "/oauth/v2/tokeninfo" with the access token
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "token_type":"Bearer",
      "expires_in":@integer@,
      "access_token":"@string@",
      "grant_types": ["password", "client_credentials", "refresh_token"],
      "scopes": ["jemarche_app"]
    }
    """

  Scenario: Password authentication
    When I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | 4THZGbOfHJvRHk8bHdtZP3BTrMWFod6bOZb2mY3wLE=  |
      | client_id     | 4222f4ce-f994-45f7-9ff5-f9f09ab3992b         |
      | grant_type    | password                                     |
      | scope         | jecoute_surveys                              |
      | device_id     | dd4SOCS-4UlCtO-gZiQGDA                       |
      | username      | carl999@example.fr                           |
      | password      | bad_password                                 |
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON should be equal to:
    """
    {
      "error": "invalid_grant",
      "error_description": "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas.",
      "message": "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."
    }
    """
    When I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | 4THZGbOfHJvRHk8bHdtZP3BTrMWFod6bOZb2mY3wLE=  |
      | client_id     | 4222f4ce-f994-45f7-9ff5-f9f09ab3992b         |
      | grant_type    | password                                     |
      | scope         | jecoute_surveys                              |
      | device_id     | dd4SOCS-4UlCtO-gZiQGDA                       |
      | username      | carl999@example.fr                           |
      | password      | secret!12345                                 |
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "token_type": "Bearer",
      "expires_in": @integer@,
      "access_token": "@string@",
      "refresh_token": "@string@"
    }
    """

  Scenario: Grant type not allowed
    Given I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                              |
      | client_secret | 2x26pszrpag408so88w4wwo4gs8o8ok4osskcw00ow80sgkkcs |
      | client_id     | f80ce2df-af6d-4ce4-8239-04cfcefd5a19               |
      | grant_type    | client_credentials                                 |
    Then the response should be in JSON
    And the response status code should be 401
    And the JSON should be equal to:
    """
    {
      "error":"invalid_client",
      "error_description":"Client authentication failed",
      "message":"Client authentication failed"
    }
    """

  Scenario: Scope is not allowed for this client
    Given I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | dALH/khq9BcjOS0GB6u5NaJ3R9k2yvSBq5wYUHx1omA= |
      | client_id     | 4122f4ce-f994-45f7-9ff5-f9f09ab3991e         |
      | grant_type    | client_credentials                           |
      | scope         | read:users write:users                       |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "error":"invalid_scope",
      "error_description":"The requested scope is invalid, unknown, or malformed",
      "message":"The requested scope is invalid, unknown, or malformed",
      "hint":"Check the `write:users` scope"
    }
    """

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

  Scenario: Register a user with callback URI
    Given I am on "/inscription-utilisateur?client_id=f80ce2df-af6d-4ce4-8239-04cfcefd5a19&redirect_uri=https%3A%2F%2Fen-marche.fr%2Fcallback"
    When I fill in the following:
      | Prénom               | Jean-pierre |
      | Nom                  | D'ARTAGNAN  |
      | E-mail               | jp@test.com |
      | Re-saisir l'e-mail   | jp@test.com |
      | Mot de passe         | testtest    |
      | Code postal          | 38000       |
      | Pays                 | FR          |
      | Nationalité          | FR          |
    And I resolved the captcha
    And I press "Créer mon compte"
    Then I should be on "/presque-fini"
    And the response status code should be 200

    Given I should have 1 email "AdherentAccountActivationMessage" for "jp@test.com" with payload:
    """
    {
      "template_name": "adherent-account-activation",
      "template_content": [],
      "message": {
        "subject": "Confirmez votre compte En-Marche.fr",
        "from_email": "contact@en-marche.fr",
        "merge_vars": [
          {
            "rcpt": "jp@test.com",
            "vars": [
              {
                "name": "first_name",
                "content": "Jean-Pierre"
              },
              {
                "name": "activation_link",
                "content": "http:\/\/test.enmarche.code\/inscription\/finaliser\/@string@/@string@?redirect_uri=https:\/\/en-marche.fr\/callback&client_id=f80ce2df-af6d-4ce4-8239-04cfcefd5a19"
              }
            ]
          }
        ],
        "from_name": "La R\u00e9publique En Marche !",
        "to": [
          {
            "email": "jp@test.com",
            "type": "to",
            "name": "Jean-Pierre d'Artagnan"
          }
        ]
      }
    }
    """
    When I click on the email link "activation_link"
    Then I should be on "https://enmarche.fr/callback"

    # Already logged in user returning to register are redirected to the redirect_uri
    Given I am logged as "jp@test.com"
    When I am on "/inscription-utilisateur?client_id=f80ce2df-af6d-4ce4-8239-04cfcefd5a19&redirect_uri=https%3A%2F%2Fen-marche.fr%2Fcallback"
    Then I should be on "https://en-marche.fr/callback"
