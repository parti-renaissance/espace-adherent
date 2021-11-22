@app
Feature: Allow some worker to get the list of configured web hooks
  In order to get configured web hooks (list of urls)
  As an authenticated and allowed OAuth client
  I want to get access to web hooks config by event name

  Background:
    And I add "Accept" header equal to "application/json"

  Scenario: Web hook config can be read when oauth client have web_hook scope (web hook does not exist in DB before that call)
    Given I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | dALH/khq9BcjOS0GB6u5NaJ3R9k2yvSBq5wYUHx1omA= |
      | client_id     | 4222f4ce-f994-45f7-9ff5-f9f09ab3991f         |
      | grant_type    | client_credentials                           |
      | scope         | web_hook                                     |
    And the response status code should be 200
    And I add the access token to the Authorization header
    When I send a "GET" request to "/api/webhooks/user.created"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "event":"user.created",
      "callbacks":[]
    }
    """

  Scenario: Web hook config can be read when oauth client have web_hook scope (web hook already in DB)
    Given I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | dALH/khq9BcjOS0GB6u5NaJ3R9k2yvSBq5wYUHx1omA= |
      | client_id     | 4222f4ce-f994-45f7-9ff5-f9f09ab3991f         |
      | grant_type    | client_credentials                           |
      | scope         | web_hook                                     |
    And the response status code should be 200
    And I add the access token to the Authorization header
    When I send a "GET" request to "/api/webhooks/user.deleted"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "event":"user.deleted",
      "callbacks":{"http://test.com/awesome":[],"https://www.en-marche.fr/webhook/endpoint":[]}
    }
    """

    Given I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | dALH/khq9BcjOS0GB6u5NaJ3R9k2yvSBq5wYUHx1omA= |
      | client_id     | 4222f4ce-f994-45f7-9ff5-f9f09ab3991f         |
      | grant_type    | client_credentials                           |
      | scope         | web_hook                                     |
    And the response status code should be 200
    And I add the access token to the Authorization header
    When I send a "GET" request to "/api/webhooks/user.updated"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "event":"user.updated",
      "callbacks":{
        "http://test.com/awesome":[],
        "https://www.en-marche.fr/webhook/endpoint":[],
        "https://api.mailchimp.com/lists":{
          "services":"mailchimp"
        }
      }
    }
    """

    Given I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | dALH/khq9BcjOS0GB6u5NaJ3R9k2yvSBq5wYUHx1omA= |
      | client_id     | 4222f4ce-f994-45f7-9ff5-f9f09ab3991f         |
      | grant_type    | client_credentials                           |
      | scope         | web_hook                                     |
    And the response status code should be 200
    And I add the access token to the Authorization header
    When I send a "GET" request to "/api/webhooks/user.update_subscriptions"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "event":"user.update_subscriptions",
      "callbacks":{"https://api.mailchimp.com/lists":{"services":"mailchimp"}}
    }
    """

  Scenario: It forbids access when OAuth client does not have web_hook scope or if user is anonymous
    When I send a "GET" request to "/api/webhooks/user.created"
    Then the response status code should be 401

    Given I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | dALH/khq9BcjOS0GB6u5NaJ3R9k2yvSBq5wYUHx1omA= |
      | client_id     | 4222f4ce-f994-45f7-9ff5-f9f09ab3991f         |
      | grant_type    | client_credentials                           |
      | scope         | read:users                                   |
    And the response status code should be 200
    And I add the access token to the Authorization header
    When I send a "GET" request to "/api/webhooks/user.created"
    Then the response status code should be 403
