@api
Feature:
  In order to create and delete threads
  As a user
  I should be able to access API threads

  Background:
    Given the following fixtures are loaded:
      | LoadIdeaAnswerData        |
      | LoadIdeaThreadData        |
      | LoadIdeaThreadCommentData |

  Scenario: As a non logged-in user I can see visibled threads paginated
    When I send a "GET" request to "/api/ideas-workshop/threads?page=1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
    | metadata.total_items    | 9                                           |
    | metadata.items_per_page | 3                                           |
    | metadata.count          | 3                                           |
    | metadata.last_page      | 3                                           |
    | items[0].content        | J'ouvre une discussion sur le problème.     |
    | items[1].content        | J'ouvre une discussion sur la solution.     |
    | items[2].content        | J'ouvre une discussion sur la comparaison.  |

  Scenario: As a non logged-in user I can see visible threads for an answer
    When I send a "GET" request to "/api/ideas-workshop/threads?answer.id=3&page=1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | items[0].content | J'ouvre une discussion sur la comparaison. |
      | items[1].content | Une nouvelle discussion.                   |
      | items[2].content | Une discussion signalée.                   |
    When I send a "GET" request to "/api/ideas-workshop/threads?answer.id=3&page=2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | items[0].content | Une discussion en 2 eme page. |

  Scenario: As a logged-in user I can't add my thread to an answer if I didn't send the terms of use checkbox status
    Given I add "Content-Type" header equal to "application/json"
    And I am logged as "martine.lindt@gmail.com"
    When I send a "POST" request to "/api/ideas-workshop/threads" with body:
    """
    {
        "answer": "1",
        "content": "LOREM IPSUM🤘"
    }
    """
    Then the response status code should be 400

  Scenario: As a logged-in user I can't add my thread to an answer if I didn't accept the terms of use
    Given I add "Content-Type" header equal to "application/json"
    And I am logged as "martine.lindt@gmail.com"
    When I send a "POST" request to "/api/ideas-workshop/threads" with body:
    """
    {
        "answer": "1",
        "content": "LOREM IPSUM🤘",
        "comments_cgu_accepted": false
    }
    """
    Then the response status code should be 400

  Scenario: As a logged-in user I can add my thread to an answer
    Given I add "Content-Type" header equal to "application/json"
    And I am logged as "martine.lindt@gmail.com"
    When I send a "POST" request to "/api/ideas-workshop/threads" with body:
    """
    {
        "answer": "1",
        "content": "LOREM IPSUM🤘",
        "comments_cgu_accepted": true
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON nodes should match:
      | answer.id | 1           |
      | content   | LOREM IPSUM |

  Scenario Outline: As a logged-in user I can not approve/disapprove other threads
    Given I am logged as "carl999@example.fr"
    When I send a "PUT" request to "<url>"
    Then the response status code should be 403
    Examples:
      | url                                                                         |
      | /api/ideas-workshop/threads/6b077cc4-1cbd-4615-b607-c23009119406/approve    |
      | /api/ideas-workshop/threads/6b077cc4-1cbd-4615-b607-c23009119406/disapprove |

  Scenario: As an idea author, I can approve/disapprove a thread
    Given I am logged as "jacques.picard@en-marche.fr"
    And I add "Content-Type" header equal to "application/json"
    When I send a "PUT" request to "/api/ideas-workshop/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1/approve"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "answer":{
          "id":1
       },
       "uuid":"dfd6a2f2-5579-421f-96ac-98993d0edea1",
       "content":"J'ouvre une discussion sur le probl\u00e8me.",
       "author":{
          "uuid":"e6977a4d-2646-5f6c-9c82-88e58dca8458",
          "nickname":null,
          "first_name":"Carl",
          "last_name":"Mirabeau"
       },
       "created_at":"@string@.isDateTime()",
       "approved": true
    }
    """
    And I should have 1 email "ApprovedIdeaCommentMessage" for "carl999@example.fr" with payload:
    """
    {
      "FromEmail": "atelier-des-idees@en-marche.fr",
      "FromName": "La République En Marche !",
      "Subject": "Votre contribution à une proposition a été approuvée par son auteur !",
      "MJ-TemplateID": "645030",
      "MJ-TemplateLanguage": true,
      "Recipients": [
          {
              "Email": "carl999@example.fr",
              "Name": "Carl Mirabeau",
              "Vars": {
                  "first_name": "Carl",
                  "idea_name": "Faire la paix",
                  "idea_link": "http://test.enmarche.code/atelier-des-idees/proposition/e4ac3efc-b539-40ac-9417-b60df432bdc5"
              }
          }
      ]
    }
    """
    When I send a "PUT" request to "/api/ideas-workshop/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1/disapprove"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | approved | false |

  Scenario: As a non logged-in user I can not delete a thread
    When I send a "DELETE" request to "/api/ideas-workshop/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1"
    Then the response status code should be 401

  Scenario: As a logged-in user I can not delete a thread that is not mine
    Given I am logged as "jacques.picard@en-marche.fr"
    When I send a "DELETE" request to "/api/ideas-workshop/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1"
    Then the response status code should be 403

  Scenario: As a logged-in user I can delete my thread
    Given I am logged as "carl999@example.fr"
    When I send a "DELETE" request to "/api/ideas-workshop/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1"
    Then the response status code should be 204
    And the response should be empty
