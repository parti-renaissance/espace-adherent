@api
Feature:
  As a logged-in user
  I should be able to access my informations

  Background:
    Given the following fixtures are loaded:
      | LoadClientData          |
      | LoadOAuthTokenData      |
      | LoadAdherentData        |
      | LoadTeamData            |
      | LoadPhoningCampaignData |

  Scenario Outline: As a non logged-in user I can not manage charters
    Given I send a "<method>" request to "<url>"
    Then the response status code should be 401
    Examples:
      | method  | url                                             |
      | GET     | /api/v3/profile/charter/phoning_campaign        |
      | PUT     | /api/v3/profile/charter/phoning_campaign/accept |

  Scenario Outline: As a logged-in user with no correct rights I cannot manage charters
    Given I am logged with "benjyd@aol.com" via OAuth client "JeMarche App"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 403
    Examples:
      | method  | url                                             |
      | GET     | /api/v3/profile/charter/phoning_campaign        |
      | PUT     | /api/v3/profile/charter/phoning_campaign/accept |

  Scenario: As a logged-in user with correct rights I cannot get a charter, if I have accepted it
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/profile/charter/phoning_campaign"
    Then the response status code should be 204

  Scenario: As a logged-in user with correct rights I can get a charter, if I have not yet accepted it
    Given I am logged with "kiroule.p@blabla.tld" via OAuth client "JeMarche App"
    And I send a "GET" request to "/api/v3/profile/charter/phoning_campaign"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "content": "**Texte de la charte** pour la *campagne* de phoning avec le Markdown"
    }
    """

  Scenario: As a logged-in user with correct rights I can accept a charter
    Given I am logged with "kiroule.p@blabla.tld" via OAuth client "JeMarche App"
    And I send a "PUT" request to "/api/v3/profile/charter/phoning_campaign/accept"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    "OK"
    """
