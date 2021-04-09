@api
Feature:
  In order to get all surveys
  As a non logged-in user
  I should be able to access to the surveys configuration and be able to answer to it

  Background:
    Given the following fixtures are loaded:
      | LoadJecouteSurveyData |
      | LoadClientData        |
      | LoadOAuthTokenData    |

  Scenario: As a non logged-in user I cannot get the surveys
    When I send a "GET" request to "/api/jecoute/survey"
    Then the response status code should be 401

  Scenario: As a logged-in user I can get the surveys of my referent(s) and the national surveys
    Given I am logged with "francis.brioul@yahoo.com" via OAuth client "J'écoute" with scope "jecoute_surveys"
    When I send a "GET" request to "/api/jecoute/survey"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
      {
        "id":3,
        "type": "local",
        "questions":[
          {
            "id":1,
            "type":"simple_field",
            "content":"Ceci est-il un champ libre ?",
            "choices":[]
          },
          {
            "id":2,
            "type":"multiple_choice",
            "content":"Est-ce une question à choix multiple ?",
            "choices":[
              {
                "id":1,
                "content":"Réponse A"
              },
              {
                "id":2,
                "content":"Réponse B"
              }
            ]
          },
          {
            "id":3,
            "type":"unique_choice",
            "content":"Est-ce une question à choix unique ?",
            "choices":[
              {
                "id":3,
                "content":"Réponse unique 1"
              },
              {
                "id":4,
                "content":"Réponse unique 2"
              }
            ]
          },
          {
            "id": 4,
            "type": "simple_field",
            "content": "Ceci est-il un champ libre d'une question suggérée ?",
            "choices": []
          }
        ],
        "name":"Questionnaire numéro 1",
        "zone":{
           "code":"77",
           "name":"Seine-et-Marne"
        },
        "city":null
      },
      {
        "id":1,
        "type": "national",
        "questions":[
          {
            "id":6,
            "type":"simple_field",
            "content":"Une première question du 1er questionnaire national ?",
            "choices":[

            ]
          },
          {
            "id":7,
            "type":"multiple_choice",
            "content":"Une deuxième question du 1er questionnaire national ?",
            "choices":[
              {
                "id":5,
                "content":"Réponse nationale A"
              },
              {
                "id":6,
                "content":"Réponse nationale B"
              },
              {
                "id":7,
                "content":"Réponse nationale C"
              },
              {
                "id":8,
                "content":"Réponse nationale D"
              }
            ]
          }
        ],
        "name":"Questionnaire national numéro 1"
      },
      {
        "id":2,
        "type":"national",
        "questions":[
          {
            "id":8,
            "type":"unique_choice",
            "content":"La question du 2eme questionnaire national ?",
            "choices":[
              {
                "id":9,
                "content":"Réponse nationale E"
              },
              {
                "id":10,
                "content":"Réponse nationale F"
              },
              {
                "id":11,
                "content":"Réponse nationale G"
              }
            ]
          }
        ],
        "name":"Le deuxième questionnaire national"
      }
    ]
    """

  Scenario: As a logged-in device I can not get the surveys with an invalid postal code
    Given I am logged with device "dd4SOCS-4UlCtO-gZiQGDA" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/survey"
    Then the response status code should be 400
    And the JSON should be equal to:
    """
      {
        "error": "Parameter \"postalCode\" missing when using a Device token."
      }
    """
    Given I am logged with device "dd4SOCS-4UlCtO-gZiQGDA" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/survey?postalCode=76"
    Then the response status code should be 400
    And the JSON should be equal to:
    """
      {
        "error": "Parameter \"postalCode\" must be 5 numbers."
      }
    """

  Scenario: As a logged-in device I can get the surveys of my postal code and the national surveys
    Given I am logged with device "dd4SOCS-4UlCtO-gZiQGDA" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/survey?postalCode=77300"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
      {
        "id":3,
        "type": "local",
        "questions":[
          {
            "id":1,
            "type":"simple_field",
            "content":"Ceci est-il un champ libre ?",
            "choices":[]
          },
          {
            "id":2,
            "type":"multiple_choice",
            "content":"Est-ce une question à choix multiple ?",
            "choices":[
              {
                "id":1,
                "content":"Réponse A"
              },
              {
                "id":2,
                "content":"Réponse B"
              }
            ]
          },
          {
            "id":3,
            "type":"unique_choice",
            "content":"Est-ce une question à choix unique ?",
            "choices":[
              {
                "id":3,
                "content":"Réponse unique 1"
              },
              {
                "id":4,
                "content":"Réponse unique 2"
              }
            ]
          },
          {
            "id": 4,
            "type": "simple_field",
            "content": "Ceci est-il un champ libre d'une question suggérée ?",
            "choices": []
          }
        ],
        "name":"Questionnaire numéro 1",
        "zone":{
           "code":"77",
           "name":"Seine-et-Marne"
        },
        "city":null
      },
      {
        "id":1,
        "type": "national",
        "questions":[
          {
            "id":6,
            "type":"simple_field",
            "content":"Une première question du 1er questionnaire national ?",
            "choices":[

            ]
          },
          {
            "id":7,
            "type":"multiple_choice",
            "content":"Une deuxième question du 1er questionnaire national ?",
            "choices":[
              {
                "id":5,
                "content":"Réponse nationale A"
              },
              {
                "id":6,
                "content":"Réponse nationale B"
              },
              {
                "id":7,
                "content":"Réponse nationale C"
              },
              {
                "id":8,
                "content":"Réponse nationale D"
              }
            ]
          }
        ],
        "name":"Questionnaire national numéro 1"
      },
      {
        "id":2,
        "type":"national",
        "questions":[
          {
            "id":8,
            "type":"unique_choice",
            "content":"La question du 2eme questionnaire national ?",
            "choices":[
              {
                "id":9,
                "content":"Réponse nationale E"
              },
              {
                "id":10,
                "content":"Réponse nationale F"
              },
              {
                "id":11,
                "content":"Réponse nationale G"
              }
            ]
          }
        ],
        "name":"Le deuxième questionnaire national"
      }
    ]
    """

  Scenario: As a logged-in user I can reply to a national survey
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jecoute_surveys"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/jecoute/survey/reply" with body:
    """
    {
      "survey":1,
      "type": "national",
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
      "longitude": 2.3522219,
      "answers":[
        {
          "surveyQuestion":6,
          "textField":"Réponse libre d'un questionnaire national"
        },
        {
          "surveyQuestion":7,
          "selectedChoices":[
            "5",
            "6"
          ]
        }
      ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "status": "ok"
    }
    """
    And I should have 1 email "DataSurveyAnsweredMessage" for "ernestino@bonsoirini.fr" with payload:
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
            "content": "Ernestino"
          }
        ],
        "from_name": "La R\u00e9publique En Marche !",
        "to": [
          {
            "email": "ernestino@bonsoirini.fr",
            "type": "to"
          }
        ]
      }
    }
    """

  Scenario: As a logged-in user I can reply to a local survey
    Given I am logged with "francis.brioul@yahoo.com" via OAuth client "J'écoute" with scope "jecoute_surveys"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/jecoute/survey/reply" with body:
    """
    {
      "survey":3,
      "type": "local",
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
      "longitude": 2.3522219,
      "answers":[
        {
          "surveyQuestion":1,
          "textField":"Réponse libre"
        },
        {
          "surveyQuestion":2,
          "selectedChoices":[
            "1",
            "2"
          ]
        },
        {
          "surveyQuestion":3,
          "selectedChoices":[
            "1"
          ]
        }
      ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "status": "ok"
    }
    """
    And I should have 1 email "DataSurveyAnsweredMessage" for "ernestino@bonsoirini.fr" with payload:
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
            "content": "Ernestino"
          }
        ],
        "from_name": "La R\u00e9publique En Marche !",
        "to": [
          {
            "email": "ernestino@bonsoirini.fr",
            "type": "to"
          }
        ]
      }
    }
    """

  Scenario: As a logged-in user I cannot reply to a local survey with errors
    Given I am logged with "francis.brioul@yahoo.com" via OAuth client "J'écoute" with scope "jecoute_surveys"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/jecoute/survey/reply" with body:
    """
    {
      "survey":null,
      "type": "local",
      "lastName":"Bonsoirini",
      "firstName":"Ernestino",
      "emailAddress":"bonsoirini.fr",
      "agreedToStayInContact":true,
      "postalCode":"59",
      "profession":"bonsoir",
      "ageRange": "between_00_00",
      "latitude": "bad_latitude",
      "longitude": "bad_longitude",
      "answers":[
        {
          "surveyQuestion":1
        },
        {
          "surveyQuestion":2,
          "selectedChoices":[
            "1",
            "2"
          ]
        },
        {
          "surveyQuestion":3,
          "textField":"Réponse non autorisé",
          "selectedChoices":[
            "1",
            "2"
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
      "status":"error",
      "errors":{
        "survey":[
          "Il n'existe aucun sondage correspondant à cet ID."
        ],
        "emailAddress":[
          "Cette valeur n'est pas une adresse email valide."
        ],
        "profession":[
          "Cette valeur n'est pas valide."
        ],
        "ageRange":[
          "Cette valeur n'est pas valide."
        ],
        "latitude": [
          "Cette valeur n'est pas valide."
        ],
        "longitude": [
          "Cette valeur n'est pas valide."
        ],
        "answers":{
          "2":{
            "surveyQuestion":[
              "La question 3 est une question à choix unique et doit contenir un seul choix sélectionné.",
              "La question 3 est une question à choix unique et ne doit pas contenir de champ texte."
            ]
          }
        },
        "postalCode": [
          "Vous devez saisir exactement 5 caractères."
        ]
      }
    }
    """

  Scenario: As a logged-in user I cannot reply to a local survey with custom validations errors
    Given I am logged with "francis.brioul@yahoo.com" via OAuth client "J'écoute" with scope "jecoute_surveys"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/jecoute/survey/reply" with body:
    """
    {
      "survey":3,
      "type": "local",
      "lastName":"Bonsoirini",
      "firstName":"Ernestino",
      "emailAddress":"ernestino@bonsoirini.fr",
      "agreedToStayInContact":false,
      "agreedToContactForJoin":true,
      "postalCode": "59000",
      "ageRange" : "foobar",
      "gender" : "other",
      "answers":[
        {
          "surveyQuestion":1,
          "textField":"Réponse libre"
        },
        {
          "surveyQuestion":2,
          "selectedChoices":[
            "1",
            "2"
          ]
        },
        {
          "surveyQuestion":3,
          "selectedChoices":[
            "1"
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
      "status": "error",
      "errors": {
        "ageRange": [
          "Cette valeur n'est pas valide."
        ],
        "genderOther": [
          "Vous avez sélectionné un autre choix, ce champ est donc obligatoire."
        ],
        "agreedToStayInContact": [
          "Si vous acceptez d'adhérer, vous devez accepter que l'on vous contacte."
        ]
      }
    }
    """
