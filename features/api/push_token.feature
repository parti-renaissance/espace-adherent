@api
Feature:
  In order to track push token
  As a logged-in user
  I should be able to post and delete push tokens

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData |
      | LoadClientData   |

  Scenario: As a logged-in user I can add and remove a push token
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/v3/push-token" with body:
    """
    {
      "identifier": "abc123",
      "source": "je_marche"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "identifier": "abc123",
      "source": "je_marche"
    }
    """

    When I send a "DELETE" request to "/api/v3/push-token/abc123"
    Then the response status code should be 204
