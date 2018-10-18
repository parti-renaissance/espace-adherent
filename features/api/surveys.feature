@api
Feature:
  In order to get all surveys
  As a non logged-in user
  I should be able to access the surveys

  Background:
    Given the following fixtures are loaded:
      | LoadJecouteSurveyData |

  Scenario: As a non logged-in user I can get the surveys
    When I am on "/api/jecoute/survey"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      [
        {
          "uuid":"@uuid@",
          "questions":[
            {
              "content":"Ceci est-il un champ libre ?",
              "type":"simple_field",
              "choices":[

              ]
            },
            {
              "content":"Est-ce une question à choix multiple ?",
              "type":"multiple_choice",
              "choices":[
                {
                  "content":"Réponse A"
                },
                {
                  "content":"Réponse B"
                }
              ]
            },
            {
              "content":"Est-ce une question à choix unique ?",
              "type":"unique_choice",
              "choices":[
                {
                  "content":"Réponse unique 1"
                },
                {
                  "content":"Réponse unique 2"
                }
              ]
            }
          ],
          "name":"Questionnaire numéro 1"
        },
        {
          "uuid":"@uuid@",
          "questions":[
            {
              "content":"Ceci est-il un champ libre ?",
              "type":"simple_field",
              "choices":[

              ]
            }
          ],
          "name":"Un deuxième questionnaire"
        }
      ]
    """
