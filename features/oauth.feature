Feature: Using OAuth for 2-legged OAuth flow (client credentials)
  In order to secure API or user data access
  As an API or an En-Marche! user
  I need to be able to access API data

  Background:
    Given the following fixtures are loaded:
      | LoadClientData |

  Scenario: Client credentials authentication
    Given I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | dALH/khq9BcjOS0GB6u5NaJ3R9k2yvSBq5wYUHx1omA= |
      | client_id     | 4122f4ce-f994-45f7-9ff5-f9f09ab3991e         |
      | grant_type    | client_credentials                           |
      | scope         | public                                       |
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
      "grant_types": ["client_credentials"]
    }
    """

    Given I add the access token to the Authorization header
    When I send a "GET" request to "/api/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "uuid":"4122f4ce-f994-45f7-9ff5-f9f09ab3991e",
      "username":"oauth_client_user_4122f4ce-f994-45f7-9ff5-f9f09ab3991e"
    }
    """

  Scenario: Grant type not allowed
    Given I add "Accept" header equal to "application/json"
    And I send a "POST" request to "/oauth/v2/token" with parameters:
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
      "message":"Client authentication failed"
    }
    """

  Scenario: Scope is not allowed for this client
    Given I add "Accept" header equal to "application/json"
    And I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | dALH/khq9BcjOS0GB6u5NaJ3R9k2yvSBq5wYUHx1omA= |
      | client_id     | 4122f4ce-f994-45f7-9ff5-f9f09ab3991e         |
      | grant_type    | client_credentials                           |
      | scope         | public user_profile                          |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "error":"invalid_scope",
      "message":"The requested scope is invalid, unknown, or malformed",
      "hint":"Check the `user_profile` scope"
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
