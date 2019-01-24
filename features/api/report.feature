@api
Feature:
  In order to see report resources
  As a user
  I should be able to retrieve the list of report reasons
  And be able to post reports

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData    |
      | LoadIdeaThreadData  |

  Scenario: As a non logged-in user I can not see the report reasons
    When I send a "GET" request to "/api/report/reasons"
    Then the response status code should be 401

  Scenario: As a logged-in user I can see all report reasons
    When I am logged as "jacques.picard@en-marche.fr"
    And I send a "GET" request to "/api/report/reasons"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "intellectual_property": "Le contenu porte atteinte aux droits de propriété intellectuelle",
      "illicit_content": "C'est un contenu manifestement illicite ou choquant",
      "commercial_content": "Il s'agit de contenu commercial",
      "other": "Autre"
    }
    """

  Scenario: As a logged-in user I can post a report
    When I am logged as "jacques.picard@en-marche.fr"
    And I send a "POST" request to "/api/report/comites/515a56c0-bde8-56ef-b90c-4745b1c93818" with body:
    """
    {
      "reasons": ["intellectual_property", "other"],
      "comment": "Je suis scandalisé, choqué et déçu par ce comité."
    }
    """
    Then the response status code should be 201

  Scenario: As a logged-in user I can post a report with "other" reason and no comment
    When I am logged as "jacques.picard@en-marche.fr"
    And I send a "POST" request to "/api/report/comites/515a56c0-bde8-56ef-b90c-4745b1c93818" with body:
    """
    {
      "reasons": ["other"]
    }
    """
    Then the response status code should be 201

  Scenario: As a logged-in user I can not post a report with a comment without reason "other"
    When I am logged as "jacques.picard@en-marche.fr"
    And I send a "POST" request to "/api/report/comites/515a56c0-bde8-56ef-b90c-4745b1c93818" with body:
    """
    {
      "reasons": ["intellectual_property"],
      "comment": "Bonsoir, les plats servis dans ce comité sont beaucoup trop sveltes."
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "@string@.isUrl()",
      "title": "An error occurred",
      "detail": "comment: Vous devez cocher la case \"Autre\" afin de renseigner un commentaire.",
      "violations":[
        {
          "propertyPath": "comment",
          "message": "Vous devez cocher la case \"Autre\" afin de renseigner un commentaire."
        }
      ]
    }
    """

  Scenario: As a logged-in user I can not post a report with no reason
    When I am logged as "jacques.picard@en-marche.fr"
    And I send a "POST" request to "/api/report/comites/515a56c0-bde8-56ef-b90c-4745b1c93818" with body:
    """
    {
      "reasons": []
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "@string@.isUrl()",
      "title": "An error occurred",
      "detail": "reasons: Afin de valider votre signalement, veuillez sélectionner au moins une raison.",
      "violations":[
        {
          "propertyPath": "reasons",
          "message": "Afin de valider votre signalement, veuillez sélectionner au moins une raison."
        }
      ]
    }
    """

  Scenario: As a logged-in user I can post a report
    When I am logged as "jacques.picard@en-marche.fr"
    And I send a "POST" request to "/api/report/atelier-des-idees-commentaires/6b077cc4-1cbd-4615-b607-c23009119406" with body:
    """
    {
      "reasons": ["other"],
      "comment": "Je suis scandalisé, choqué et déçu!"
    }
    """
    Then the response status code should be 201
