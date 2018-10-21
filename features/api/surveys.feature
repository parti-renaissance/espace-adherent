Feature:
  In order to get all surveys
  As a non logged-in user
  I should be able to access to the surveys configuration and be able to answer to it

  Scenario: As a non logged-in user I cannot get the surveys
    When I send a "GET" request to "/api/jecoute/survey"
    Then the response status code should be 401

  Scenario: As a logged-in user I can get the surveys and answer it
    Given the following fixtures are loaded:
      | LoadJecouteSurveyData |
      | LoadClientData        |
      | LoadOAuthTokenData    |
    Given I am logged as "michelle.dufour@example.ch"
    And I add the access token "1c33b1711015b5e3d930f65b5dc87c398bfb3b29401028ee119c882bdf87cf9dcbf9a562629535e6" to the Authorization headers
    When I send a "GET" request to "/api/jecoute/survey"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
      {
        "id":@integer@,
        "questions":[
          {
            "id":@integer@,
            "type":"simple_field",
            "content":"Ceci est-il un champ libre ?",
            "choices":[]
          },
          {
            "id":@integer@,
            "type":"multiple_choice",
            "content":"Est-ce une question à choix multiple ?",
            "choices":[
              {
                "id":@integer@,
                "content":"Réponse A"
              },
              {
                "id":@integer@,
                "content":"Réponse B"
              }
            ]
          },
          {
            "id":@integer@,
            "type":"unique_choice",
            "content":"Est-ce une question à choix unique ?",
            "choices":[
              {
                "id":@integer@,
                "content":"Réponse unique 1"
              },
              {
                "id":@integer@,
                "content":"Réponse unique 2"
              }
            ]
          }
        ],
        "name":"Questionnaire numéro 1"
      },
      {
        "id":@integer@,
        "questions":[
          {
            "id":@integer@,
            "type":"simple_field",
            "content":"Ceci est-il un champ libre ?",
            "choices":[]
          }
        ],
        "name":"Un deuxième questionnaire"
      }
    ]
    """

    ## As a logged-in user I can reply to a survey
    Given I add the access token "1c33b1711015b5e3d930f65b5dc87c398bfb3b29401028ee119c882bdf87cf9dcbf9a562629535e6" to the Authorization headers
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/jecoute/survey/reply" with body:
    """
    {
      "survey":1,
      "lastName":"Bonsoirini",
      "firstName":"Ernestino",
      "phone":"+33320202020",
      "emailAddress":"ernestino@bonsoirini.fr",
      "agreedToStayInContact":1,
      "agreedToJoinParisOperation":1,
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
    Then print last JSON response
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "status": "ok"
    }
    """

    ## As a logged-in user I cannot reply to a survey with errors
    Given I add the access token "1c33b1711015b5e3d930f65b5dc87c398bfb3b29401028ee119c882bdf87cf9dcbf9a562629535e6" to the Authorization headers
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/jecoute/survey/reply" with body:
    """
    {
      "survey":1,
      "lastName":"Bonsoirini",
      "firstName":"Ernestino",
      "phone":"+33320202020",
      "emailAddress":"bonsoirini.fr",
      "agreedToStayInContact":1,
      "agreedToJoinParisOperation":1,
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
        "emailAddress":[
          "Cette valeur n'est pas une adresse email valide."
        ],
        "answers":{
          "0":{
            "surveyQuestion":[
              "La question 1 est un champ libre et doit contenir un champ texte."
            ]
          },
          "2":{
            "surveyQuestion":[
              "La question 3 est une question à choix unique et doit contenir un seul choix sélectionné.",
              "La question 3 est une question à choix unique et ne doit pas contenir de champ texte."
            ]
          }
        }
      }
    }
    """
