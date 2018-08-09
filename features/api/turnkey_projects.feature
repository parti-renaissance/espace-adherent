Feature:
  In order to get a turnkey projects
  As a non logged-in user
  I should be able to access API Turnkey projects

  Background:
    Given the following fixtures are loaded:
      | LoadTurnkeyProjectData  |

  Scenario: As a non logged-in user I can get approved turnkey projects count
    When I send a "GET" request to "/api/turnkey-projects/count"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "total":4
    }
    """

  Scenario: As a non logged-in user I can get a turnkey project
    When I send a "GET" request to "/api/turnkey-project/la-sante-pour-tous"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "slug":"la-sante-pour-tous",
      "title":"La santé pour tous !",
      "subtitle": "Sensibilisation à la santé dans les écoles",
      "description": "Les étudiants et professeurs d'université rencontrent des difficultés dans sa mise en œuvre locale.",
      "video_id": "7-aBc9deF_",
      "category": "Santé",
      "is_favorite": true
    }
    """
