@api
Feature:
  In order to see, add and delete idea votes
  As a user
  I should be able to access API idea votes and post my vote

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData    |
      | LoadIdeaData        |
      | LoadIdeaVoteData    |

  Scenario: As a logged-in user I can add my vote to an idea
    When I add "Content-Type" header equal to "application/json"
    And I am logged as "martine.lindt@gmail.com"
    And I send a "POST" request to "/api/votes" with body:
    """
    {
      "idea": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
      "type": "important"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "id": @integer@,
        "idea": {
            "name": "Faire la paix"
        },
        "author": {
            "first_name": "Martine",
            "last_name": "Lindt"
        },
        "type": "important"
    }
    """

    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/votes" with body:
    """
    {
      "idea": e4ac3efc-b539-40ac-9417-b60df432bdc5,
      "type": "important"
    }
    """
    Then the response status code should be 400

  Scenario: As a non logged-in user I can not delete a vote
    When I send a "DELETE" request to "/api/votes/1"
    Then the response status code should be 401

  Scenario: As a logged-in user I can not delete a vote that is not mine
    When I am logged as "jacques.picard@en-marche.fr"
    And I send a "DELETE" request to "/api/votes/1"
    Then the response status code should be 403

  Scenario: As a logged-in user I can delete my vote
    When I am logged as "jacques.picard@en-marche.fr"
    And I send a "DELETE" request to "/api/votes/10"
    Then the response status code should be 204
    And the response should be empty
