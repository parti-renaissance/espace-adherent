@api
Feature:
  In order to see idea themes
  As a user
  I should be able to access API idea themes

  Background:
    Given the following fixtures are loaded:
      | LoadIdeaThemeData  |

  Scenario: As a non logged-in user I can see all enabled themes
    When I send a "GET" request to "/api/themes"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 3,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 2
        },
        "items": [
            {
                "id": @integer@,
                "name": "Armées et défense",
                "thumbnail": "http://test.enmarche.code/assets/images/ideas_workshop/themes/default.png"
            },
            {
                "id": @integer@,
                "name": "Écologie",
                "thumbnail": null
            }
        ]
    }
    """
