@api
Feature:
  In order to see idea categories
  As a user
  I should be able to access API idea categories

  Background:
    Given the following fixtures are loaded:
      | LoadIdeaCategoryData  |

  Scenario: As a non logged-in user I can see all enabled categories
    When I send a "GET" request to "/api/ideas-workshop/categories"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
      {
          "id": @integer@,
          "name": "Echelle Européenne"
      },
      {
          "id": @integer@,
          "name": "Echelle Locale"
      },
      {
          "id": @integer@,
          "name": "Echelle Nationale"
      }
    ]
    """
