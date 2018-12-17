@ap
Feature:
  In order to see idea categories
  As a user
  I should be able to access API idea categories

  Background:
    Given the following fixtures are loaded:
      | LoadIdeaNeedData  |

  Scenario: As a non logged-in user I can see all enabled categories
    When I send a "GET" request to "/api/needs"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
        {
            "id": @integer@,
            "name": "Juridique"
        },
        {
            "id": @integer@,
            "name": "Rédactionnel"
        }
    ]
    """
