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
    When I add "Content-Type" header equal to "application/json"
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
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "code": "already_replied",
      "message": "La réponse a été déjà envoyée"
    }
    """

  Scenario: As a logged-in user I can reply to a survey for permanent phoning campaign
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jecoute_surveys"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/v3/phoning_campaign_histories/a80248ff-384a-4f80-972a-177c3d0a77c4/reply" with body:
    """
    {
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
    And I should have 1 email "DataSurveyAnsweredMessage" for "maria@mozzarella.com" with payload:
    """
    {
      "template_name": "data-survey-answered",
      "template_content": [],
      "message": {
        "subject": "Votre adhésion à La République En Marche !",
        "from_email": "contact@en-marche.fr",
        "global_merge_vars": [
          {
            "name": "first_name",
            "content": "Maria"
          }
        ],
        "from_name": "La République En Marche !",
        "to": [
          {
            "email": "maria@mozzarella.com",
            "type": "to"
          }
        ]
      }
    }
    """

  Scenario: As a logged-in user I cannot reply to a national survey with invalid data
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jecoute_surveys"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/v3/jemarche_data_surveys" with body:
    """
    {
      "lastName":"Bonsoirini Bonsoirini Bonsoirini Bonsoirini Bonsoirini",
      "firstName":"Ernestino Ernestino Ernestino Ernestino Ernestino Ernestino",
      "emailAddress":"ernestinobonsoirini",
      "postalCode":"59",
      "profession":"test",
      "ageRange": "test",
      "gender": "test",
      "genderOther": "other other other other other other other other other other other other"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "gender: Vous devez sélectionner le choix Autre ou ne pas saisir d'autre genre.\nfirst_name: Votre prénom ne peut pas dépasser 50 caractères.\nlast_name: Votre nom ne peut pas dépasser 50 caractères.\nemail_address: Cette valeur n'est pas une adresse email valide.\npostal_code: Vous devez saisir exactement 5 caractères.\nprofession: Cette valeur doit être l'un des choix proposés.\nage_range: Cette valeur doit être l'un des choix proposés.\ngender: Cette valeur doit être l'un des choix proposés.\ngender_other: Vous devez saisir au maximum 50 caractères.",
      "violations": [
        {
          "propertyPath": "gender",
          "message": "Vous devez sélectionner le choix Autre ou ne pas saisir d'autre genre."
        },
        {
          "message": "Votre prénom ne peut pas dépasser 50 caractères.",
          "propertyPath": "first_name"
        },
        {
          "message": "Votre nom ne peut pas dépasser 50 caractères.",
          "propertyPath": "last_name"
        },
        {
          "propertyPath": "email_address",
          "message": "Cette valeur n'est pas une adresse email valide."
        },
        {
          "propertyPath": "postal_code",
          "message": "Vous devez saisir exactement 5 caractères."
        },
        {
          "propertyPath": "profession",
          "message": "Cette valeur doit être l'un des choix proposés."
        },
        {
          "propertyPath": "age_range",
          "message": "Cette valeur doit être l'un des choix proposés."
        },
        {
          "propertyPath": "gender",
          "message": "Cette valeur doit être l'un des choix proposés."
        },
        {
          "message": "Vous devez saisir au maximum 50 caractères.",
          "propertyPath": "gender_other"
        }
      ]
    }
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
