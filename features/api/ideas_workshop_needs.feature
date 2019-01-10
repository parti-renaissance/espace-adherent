@api
Feature:
  In order to see idea needs
  As a user
  I should be able to access API idea needs

  Background:
    Given the following fixtures are loaded:
      | LoadIdeaNeedData  |

  Scenario: As a non logged-in user I can see all enabled needs
    When I send a "GET" request to "/api/ideas-workshop/needs"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 2,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "id": @integer@,
                "name": "Juridique"
            },
            {
                "id": @integer@,
                "name": "Rédactionnel"
            }
        ]
    }
    """
