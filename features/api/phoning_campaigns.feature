@api
Feature:
  In order to see phoning campaigns
  As a non logged-in user
  I should be able to access API phoning campaigns

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData        |
      | LoadClientData          |
      | LoadTeamData            |
      | LoadPhoningCampaignData |

  Scenario: As a logged-in user with no correct rights I cannot get my phoning campaigns
    Given I am logged with "benjyd@aol.com" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/phoning_campaigns/scores"
    Then the response status code should be 403

  Scenario: As a logged-in user I can get my phoning campaigns
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/phoning_campaigns/scores"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
      {
        "title": "Campagne pour les hommes",
        "finish_at": "@string@.isDateTime()",
        "goal": 500,
        "uuid": "4ebb184c-24d9-4aeb-bb36-afe44f294387"
      },
      {
        "title": "Campagne pour les femmes",
        "finish_at": "@string@.isDateTime()",
        "goal": 500,
        "uuid": "4d91b94c-4b39-43c7-9c88-f4be7e2fe0bc"
      }
    ]
    """
