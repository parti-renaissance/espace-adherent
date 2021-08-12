@api
Feature:
  In order to see, create, edit and delete adherent messages
  As a logged-in user
  I should be able to access API adherent messages

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentMessageData         |
      | LoadAudienceSegmentData         |
      | LoadAdherentData                |
      | LoadClientData                  |
      | LoadGeoZoneData                 |
      | LoadAudienceData                |
      | LoadReferentTagData             |
      | LoadReferentTagsZonesLinksData  |

  Scenario: As a logged-in user I can not update adherent message filter with not my segment
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f/filter" with body:
    """
    {
      "segment": "f6c36dd7-0517-4caf-ba6f-ec6822f2ec12"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "type":"https://symfony.com/errors/validation",
       "title":"Validation Failed",
       "detail":"segment: Le segment n'est pas autoris\u00e9",
       "violations":[
          {
             "propertyPath":"segment",
             "title":"Le segment n'est pas autorisé",
             "parameters":[
                
             ]
          }
       ]
    }
    """

  Scenario: As a logged-in user I can update adherent message filter with segment
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f/filter" with body:
    """
    {
      "segment": "830d230f-67fb-4217-9986-1a3ed7d3d5e7"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      "OK"
    """
