@api
Feature:
  As a non logged-in user
  I should be able to retrieve intl informations

  Scenario: As a non logged-in user I can retrieve countries informations
    When I send a "GET" request to "/api/countries"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    [
      {
        "name": "Afghanistan",
        "region": "AF",
        "code": 93
      }
    ]
    """
