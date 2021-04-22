@api
Feature:
  In order to see zones
  As a non logged-in user
  I should be able to access API zones

  Background:
    Given the following fixtures are loaded:
      | LoadGeoZoneData |

  Scenario: As a non logged-in user I can filter zones by exact types and partial name
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/zones" with parameters:
      | key    | value     |
      | type[] | country   |
      | type[] | city      |
      | name   | Bois-Colo |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 1,
        "items_per_page": 2,
        "count": 1,
        "current_page": 1,
        "last_page": 1
      },
      "items": [
        {
          "uuid": "@uuid@",
          "type": "city",
          "postal_code": [
            "92270"
          ],
          "code": "92009",
          "name": "Bois-Colombes"
        }
      ]
    }
    """

    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/zones" with parameters:
      | key    | value   |
      | type[] | country |
      | type[] | city    |
      | name   | Allema  |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 1,
        "items_per_page": 2,
        "count": 1,
        "current_page": 1,
        "last_page": 1
      },
      "items": [
        {
          "uuid": "@uuid@",
          "type": "country",
          "postal_code": [],
          "code": "DE",
          "name": "Allemagne"
        }
      ]
    }
    """
