@api
Feature:
  In order to get all surveys
  As a non logged-in user
  I should be able to access to the surveys configuration and be able to answer to it

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
        "id": @integer@,
        "uuid": "@uuid@",
        "type": "local",
        "name": "Questionnaire numéro 1",
        "zone":{
           "code": "77",
           "name": "Seine-et-Marne"
        },
        "city":null,
        "questions": [
          {
            "id":1,
            "type": "simple_field",
            "content": "Ceci est-il un champ libre ?",
            "choices": []
          },
          {
            "id": @integer@,
            "type": "multiple_choice",
            "content": "Est-ce une question à choix multiple ?",
            "choices": [
              {
                "id": @integer@,
                "content": "Réponse A"
              },
              {
                "id": @integer@,
                "content": "Réponse B"
              }
            ]
          },
          {
            "id": @integer@,
            "type": "unique_choice",
            "content": "Est-ce une question à choix unique ?",
            "choices": [
              {
                "id": @integer@,
                "content": "Réponse unique 1"
              },
              {
                "id": @integer@,
                "content": "Réponse unique 2"
              }
            ]
          },
          {
            "id": @integer@,
            "type": "simple_field",
            "content": "Ceci est-il un champ libre d'une question suggérée ?",
            "choices": []
          }
        ]
      },
      {
        "id": @integer@,
        "uuid": "@uuid@",
        "type": "national",
        "name": "Le deuxième questionnaire national",
        "questions": [
          {
            "id": 9,
            "type": "simple_field",
            "content": "A votre avis quels seront les enjeux des 10 prochaines années?",
            "choices": []
          },
          {
            "id": 10,
            "type": "multiple_choice",
            "content": "L'écologie est selon vous, importante pour :",
            "choices": [
              {
                "id": 12,
                "content": "L'héritage laissé aux générations futures"
              },
              {
                "id": 13,
                "content": "Le bien-être sanitaire"
              },
              {
                "id": 14,
                "content": "L'aspect financier"
              },
              {
                "id": 15,
                "content": "La préservation de l'environnement"
              }
            ]
          }
        ]
      },
      {
        "id":2,
        "uuid":"@uuid@",
        "type":"national",
        "questions":[
          {
            "id": @integer@,
            "type": "unique_choice",
            "content": "La question du 2eme questionnaire national ?",
            "choices": [
              {
                "id": @integer@,
                "content": "Réponse nationale E"
              },
              {
                "id": @integer@,
                "content": "Réponse nationale F"
              },
              {
                "id": @integer@,
                "content": "Réponse nationale G"
              }
            ]
          }
        ]
      },
      {
        "id": @integer@,
        "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
        "type": "national",
        "name": "Questionnaire national numéro 1",
        "questions": [
          {
            "id": @integer@,
            "type": "simple_field",
            "content": "Une première question du 1er questionnaire national ?",
            "choices": [

            ]
          },
          {
            "id": @integer@,
            "type": "multiple_choice",
            "content": "Une deuxième question du 1er questionnaire national ?",
            "choices": [
              {
                "id": @integer@,
                "content": "Réponse nationale A"
              },
              {
                "id": @integer@,
                "content": "Réponse nationale B"
              },
              {
                "id": @integer@,
                "content": "Réponse nationale C"
              },
              {
                "id": @integer@,
                "content": "Réponse nationale D"
              }
            ]
          }
        ]
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
        "id": @integer@,
        "uuid": "@uuid@",
        "type": "local",
        "name": "Questionnaire numéro 1",
        "zone":{
           "code": "77",
           "name": "Seine-et-Marne"
        },
        "city":null,
        "questions": [
          {
            "id":1,
            "type": "simple_field",
            "content": "Ceci est-il un champ libre ?",
            "choices": []
          },
          {
            "id": @integer@,
            "type": "multiple_choice",
            "content": "Est-ce une question à choix multiple ?",
            "choices": [
              {
                "id": @integer@,
                "content": "Réponse A"
              },
              {
                "id": @integer@,
                "content": "Réponse B"
              }
            ]
          },
          {
            "id": @integer@,
            "type": "unique_choice",
            "content": "Est-ce une question à choix unique ?",
            "choices": [
              {
                "id": @integer@,
                "content": "Réponse unique 1"
              },
              {
                "id": @integer@,
                "content": "Réponse unique 2"
              }
            ]
          },
          {
            "id": @integer@,
            "type": "simple_field",
            "content": "Ceci est-il un champ libre d'une question suggérée ?",
            "choices": []
          }
        ]
      },
      {
        "uuid": "@uuid@",
        "id": 3,
        "type": "national",
        "name": "Les enjeux des 10 prochaines années ",
        "questions": [
          {
            "id": 9,
            "type": "simple_field",
            "content": "A votre avis quels seront les enjeux des 10 prochaines années?",
            "choices": []
          },
          {
            "id": 10,
            "type": "multiple_choice",
            "content": "L'écologie est selon vous, importante pour :",
            "choices": [
              {
                "id": 12,
                "content": "L'héritage laissé aux générations futures"
              },
              {
                "id": 13,
                "content": "Le bien-être sanitaire"
              },
              {
                "id": 14,
                "content": "L'aspect financier"
              },
              {
                "id": 15,
                "content": "La préservation de l'environnement"
              }
            ]
          }
        ]
      },
      {
        "id": @integer@,
        "uuid": "@uuid@",
        "type": "national",
        "name": "Le deuxième questionnaire national",
        "questions": [
          {
            "id": @integer@,
            "type": "unique_choice",
            "content": "La question du 2eme questionnaire national ?",
            "choices": [
              {
                "id": @integer@,
                "content": "Réponse nationale E"
              },
              {
                "id": @integer@,
                "content": "Réponse nationale F"
              },
              {
                "id": @integer@,
                "content": "Réponse nationale G"
              }
            ]
          }
        ]
      },
      {
        "id": @integer@,
        "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
        "type": "national",
        "name": "Questionnaire national numéro 1",
        "questions": [
          {
            "id": @integer@,
            "type": "simple_field",
            "content": "Une première question du 1er questionnaire national ?",
            "choices": [

            ]
          },
          {
            "id": @integer@,
            "type": "multiple_choice",
            "content": "Une deuxième question du 1er questionnaire national ?",
            "choices": [
              {
                "id": @integer@,
                "content": "Réponse nationale A"
              },
              {
                "id": @integer@,
                "content": "Réponse nationale B"
              },
              {
                "id": @integer@,
                "content": "Réponse nationale C"
              },
              {
                "id": @integer@,
                "content": "Réponse nationale D"
              }
            ]
          }
        ]
      }
    ]
    """

  Scenario: As a logged-in user I can reply to a national survey (new body structure)
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jecoute_surveys"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/jecoute/survey/reply" with body:
    """
    {
      "dataSurvey":{
        "survey":1,
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
      },
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

  Scenario: As a logged-in user I can reply to a national survey without agreeing to join
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
      "emailAddress":"ernestino2@bonsoirini.fr",
      "agreedToStayInContact":true,
      "agreedToContactForJoin":false,
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
    And I should have 0 email

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
      "lastName":"Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
      "firstName":"Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
      "emailAddress":"bonsoirini.fr",
      "agreedToStayInContact":true,
      "postalCode":"59",
      "profession":"bonsoir",
      "ageRange": "between_00_00",
      "gender": "invalid_gender",
      "genderOther": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
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
          "textField":"Réponse non autorisée",
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
        "dataSurvey":{
          "survey":[
            "Il n'existe aucun sondage correspondant à cet ID."
          ],
          "answers":{
            "2":{
              "surveyQuestion":[
                "La question 3 est une question à choix unique et doit contenir un seul choix sélectionné.",
                "La question 3 est une question à choix unique et ne doit pas contenir de champ texte."
              ]
            }
          }
        },
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
        "gender": [
          "Cette valeur n'est pas valide."
        ]
      }
    }
    """

  Scenario: As a logged-in user I cannot reply to a local survey with errors (new body structure)
    Given I am logged with "francis.brioul@yahoo.com" via OAuth client "J'écoute" with scope "jecoute_surveys"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/jecoute/survey/reply" with body:
    """
    {
      "dataSurvey":{
        "survey":null,
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
            "textField":"Réponse non autorisée",
            "selectedChoices":[
              "1",
              "2"
            ]
          }
        ]
      },
      "lastName":"Bonsoirini",
      "firstName":"Ernestino",
      "emailAddress":"bonsoirini.fr",
      "agreedToStayInContact":true,
      "postalCode":"59",
      "profession":"bonsoir",
      "ageRange": "between_00_00",
      "latitude": "bad_latitude",
      "longitude": "bad_longitude"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "status":"error",
      "errors":{
        "dataSurvey":{
          "survey":[
            "Il n'existe aucun sondage correspondant à cet ID."
          ],
          "answers":{
            "2":{
              "surveyQuestion":[
                "La question 3 est une question à choix unique et doit contenir un seul choix sélectionné.",
                "La question 3 est une question à choix unique et ne doit pas contenir de champ texte."
              ]
            }
          }
        },
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
        ]
      }
    }
    """

  Scenario: As a logged-in user I can reply to a local survey with custom validations errors
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
      "ageRange": "between_25_39",
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
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "status": "ok"
    }
    """

  Scenario: As a DC referent I can get the survey list filtered by the name
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/surveys?name=national&scope=phoning_national_manager"
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
          "id": @integer@,
          "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
          "type": "national",
          "name": "Questionnaire national numéro 1",
          "questions": [
            {
              "id": @integer@,
              "type": "simple_field",
              "content": "Une première question du 1er questionnaire national ?",
              "choices": [

              ]
            },
            {
              "id": @integer@,
              "type": "multiple_choice",
              "content": "Une deuxième question du 1er questionnaire national ?",
              "choices": [
                {
                  "id": @integer@,
                  "content": "Réponse nationale A"
                },
                {
                  "id": @integer@,
                  "content": "Réponse nationale B"
                },
                {
                  "id": @integer@,
                  "content": "Réponse nationale C"
                },
                {
                  "id": @integer@,
                  "content": "Réponse nationale D"
                }
              ]
            }
          ]
        },
        {
          "id": @integer@,
          "uuid": "@uuid@",
          "type": "national",
          "name": "Le deuxième questionnaire national",
          "questions": [
            {
              "id": @integer@,
              "type": "unique_choice",
              "content": "La question du 2eme questionnaire national ?",
              "choices": [
                {
                  "id": @integer@,
                  "content": "Réponse nationale E"
                },
                {
                  "id": @integer@,
                  "content": "Réponse nationale F"
                },
                {
                  "id": @integer@,
                  "content": "Réponse nationale G"
                }
              ]
            }
          ]
        }
      ]
    }
    """
