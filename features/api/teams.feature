@api
Feature:
  In order to see phoning teams
  As a logged-in user
  I should be able to access API phoning teams

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                |
      | LoadClientData                  |
      | LoadScopeData                   |
      | LoadTeamData                    |

  Scenario: As a logged-in user without phoning team manager right I can not get phoning teams
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/teams?scope=phoning_national_manager"
    Then the response status code should be 403

  Scenario: As a logged-in user with phoning team manager right I can get only phoning teams
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/teams?scope=phoning_national_manager"
    Then the response status code should be 200
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
          "name": "Première équipe de phoning",
          "uuid": "3deeb1f5-819e-4629-85a1-eb75c916ce2f",
          "members_count": 3
        },
        {
          "name": "Deuxième équipe de phoning",
          "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
          "members_count": 4
        }
      ]
    }
    """
