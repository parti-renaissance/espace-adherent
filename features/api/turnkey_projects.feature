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

  Scenario: As a non logged-in user I can get approved turnkey projects
    When I send a "GET" request to "/api/turnkey-projects"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [{
      "category":"Nature et Environnement",
      "title":"Stop mégots !",
      "slug":"stop-megots",
      "subtitle":"Campagnes de sensibilisation et de revalorisation des mégots jetés"
    },
    {
      "category":"Culture",
      "title":"Art's connection",
      "slug":"art-s-connection",
      "subtitle":"Ateliers de rencontre autour de l'art"
    },
    {
      "category":"Lien social et aide aux personnes en difficulté",
      "title":"Cafés Citoyens",
      "slug":"cafes-citoyens",
      "subtitle":"Citoyens de la Cité, vous avez des projets ? Nous vous aidons à les concrétiser!"
    },
    {
      "category":"Santé",
      "title":"La santé pour tous !",
      "slug":"la-sante-pour-tous",
      "subtitle":"Sensibilisation à la santé dans les écoles"
    }]
    """

  Scenario: As a non logged-in user I can get a turnkey project
    When I send a "GET" request to "/api/turnkey-projects/la-sante-pour-tous"
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

  Scenario: As a non logged-in user I can get a pinned turnkey project
    When I send a "GET" request to "/api/turnkey-projects/pinned"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "slug":"stop-megots",
      "title":"Stop mégots !",
      "subtitle": "Campagnes de sensibilisation et de revalorisation des mégots jetés",
      "description": "Les mégots sont jetés en abondance dans la rue.",
      "category": "Nature et Environnement",
      "is_favorite": false
    }
    """
