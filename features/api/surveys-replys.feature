@api
Feature:
  Background:
    Given the following fixtures are loaded:
      | LoadJecouteSurveyData           |
      | LoadClientData                  |
      | LoadOAuthTokenData              |
      | LoadPhoningCampaignHistoryData  |
      | LoadJemarcheDataSurveyData      |

  Scenario: As a logged-in user I can reply to a national survey for phoning campaign (new body structure)
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "J'écoute" with scope "jecoute_surveys"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055/reply" with body:
    """
    {
      "survey":"13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
      "answers":[
        {
          "surveyQuestion":6,
          "textField":"Réponse libre d'un questionnaire national"
        },
        {
          "surveyQuestion":7,
          "selectedChoices":[
            5,
            6
          ]
        }
      ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {"uuid": "@uuid@"}
    """

  Scenario: As a logged-in user I can reply to a national survey for Jemarche data survey (new body structure)
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jecoute_surveys"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/v3/jemarche_data_surveys/5191f388-ccb0-4a93-b7f9-a15f107287fb/reply" with body:
    """
    {
      "survey":"13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
      "answers":[
        {
          "surveyQuestion":6,
          "textField":"Une nouvelle réponse libre d'un questionnaire national"
        },
        {
          "surveyQuestion":7,
          "selectedChoices":[
            6,
            4
          ]
        }
      ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {"uuid": "@uuid@"}
    """

  Scenario: As a logged-in user I can reply to a national survey
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jecoute_surveys"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/v3/jemarche_data_surveys" with body:
    """
    {
      "lastName":"Bonsoirini",
      "firstName":"Ernestino",
      "emailAddress":"ernestino@bonsoirini.fr",
      "agreedToStayInContact":true,
      "agreedToContactForJoin":true,
      "agreedToTreatPersonalData":true,
      "postalCode":"59000",
      "profession":"employees",
      "ageRange": "between_25_39",
      "gender": "male",
      "latitude": 48.856614,
      "longitude": 2.3522219
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {"uuid": "@uuid@"}
    """
