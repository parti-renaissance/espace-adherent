@api
Feature:
  In order to see comments
  As a user
  I should be able to access API threads and thread comments

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData          |
      | LoadIdeaData              |
      | LoadIdeaThreadData        |
      | LoadIdeaThreadCommentData |

  Scenario: As a non logged-in user I can see visibled thread comments paginated
    When I send a "GET" request to "/api/ideas-workshop/thread_comments?page=1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | metadata.total_items    | 9                                       |
      | metadata.items_per_page | 3                                       |
      | metadata.count          | 3                                       |
      | metadata.last_page      | 3                                       |
      | items[0].content        | Commentaire d'un adhérent               |
      | items[1].content        | Commentaire de l'adhérent à desadhérer  |
      | items[2].content        | <p>Commentaire signalé</p>              |

  Scenario: As a non logged-in user I can see visible thread comments for a specific thread
    When I send a "GET" request to "/api/ideas-workshop/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1/comments"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 4,
            "items_per_page": 3,
            "count": 3,
            "current_page": 1,
            "last_page": 2
        },
        "items": [
            {
                "uuid": "02bf299f-678a-4829-a6a1-241995339d8d",
                "content": "Deuxième commentaire d'un référent",
                "author": {
                    "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                    "nickname":null,
                    "first_name": "Referent",
                    "last_name": "R."
                },
                "approved": false,
                "created_at": "@string@.isDateTime()"
            },
            {
                "uuid": "f716d3ba-004f-4958-af26-a7b010a6d458",
                "content": "Commentaire d'un référent",
                "author": {
                    "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                    "nickname":null,
                    "first_name": "Referent",
                    "last_name": "R."
                },
                "approved": false,
                "created_at": "@string@.isDateTime()"
            },
            {
                "uuid": "60123090-6cdc-4de6-9cb3-07e2ec411f2f",
                "content": "Lorem Ipsum Commentaris",
                "author": {
                    "uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                    "nickname":null,
                    "first_name": "Francis",
                    "last_name": "B."
                },
                "approved": false,
                "created_at": "@string@.isDateTime()"
            }
        ]
    }
    """

  Scenario: As a logged-in user I can't add my comment to a thread if I didn't send the terms of use checkbox status
    Given I add "Content-Type" header equal to "application/json"
    And I am logged as "martine.lindt@gmail.com"
    When I send a "POST" request to "/api/ideas-workshop/thread_comments" with body:
    """
    {
      "thread": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
      "content": "Phasellus vitae enim faucibus🤘"
    }
    """
    Then the response status code should be 400

  Scenario: As a logged-in user I can't add my comment to a thread if I didn't accept the terms of use
    Given I add "Content-Type" header equal to "application/json"
    And I am logged as "martine.lindt@gmail.com"
    When I send a "POST" request to "/api/ideas-workshop/thread_comments" with body:
    """
    {
      "thread": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
      "content": "Phasellus vitae enim faucibus🤘",
      "comments_cgu_accepted": false
    }
    """
    Then the response status code should be 400

  Scenario: As a logged-in user I can add my comment to a thread
    Given I am logged as "martine.lindt@gmail.com"
    And I add "Content-Type" header equal to "application/json"
    When I send a "POST" request to "/api/ideas-workshop/thread_comments" with body:
    """
    {
      "thread": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
      "content": "Phasellus vitae enim faucibus🤘",
      "comments_cgu_accepted": true
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON nodes should match:
      | thread.uuid | dfd6a2f2-5579-421f-96ac-98993d0edea1 |
      | content     | Phasellus vitae enim faucibus        |

  Scenario Outline: As a logged-in user I can not approve/disapprove other comments
    Given I am logged as "carl999@example.fr"
    When I send a "PUT" request to "<url>"
    Then the response status code should be 403
    Examples:
      | url                                                                                 |
      | /api/ideas-workshop/thread_comments/b99933f3-180c-4248-82f8-1b0eb950740d/approve    |
      | /api/ideas-workshop/thread_comments/b99933f3-180c-4248-82f8-1b0eb950740d/disapprove |

  Scenario: As an idea author, I can approve/disapprove a comment
    Given I am logged as "jacques.picard@en-marche.fr"
    And I add "Content-Type" header equal to "application/json"
    When I send a "PUT" request to "/api/ideas-workshop/thread_comments/b99933f3-180c-4248-82f8-1b0eb950740d/approve"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "content":"Aenean viverra efficitur lorem",
       "author":{
          "nickname":null,
          "uuid":"acc73b03-9743-47d8-99db-5a6c6f55ad67",
          "nickname":null,
          "first_name":"Benjamin",
          "last_name":"Duroc"
       },
       "created_at": "@string@.isDateTime()",
       "uuid":"b99933f3-180c-4248-82f8-1b0eb950740d",
       "approved": true
    }
    """
    And I should have 1 email "ApprovedIdeaCommentMessage" for "benjyd@aol.com" with payload:
    """
    {
      "template_name": "approved-idea-comment",
      "template_content": [],
      "message": {
        "subject": "Votre contribution à une proposition a été approuvée par son auteur !",
        "from_email": "atelier-des-idees@en-marche.fr",
        "global_merge_vars": [
          {
            "name": "first_name",
            "content": "Benjamin"
          },
          {
            "name": "idea_name",
            "content": "Faire la paix"
          },
          {
            "name": "idea_link",
            "content": "http://test.enmarche.code/atelier-des-idees/proposition/e4ac3efc-b539-40ac-9417-b60df432bdc5"
          }
        ],
        "from_name": "La République En Marche !",
        "to": [
          {
            "email": "benjyd@aol.com",
            "type": "to",
            "name": "Benjamin Duroc"
          }
        ]
      }
    }
    """
    When I send a "PUT" request to "/api/ideas-workshop/thread_comments/b99933f3-180c-4248-82f8-1b0eb950740d/disapprove"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | approved | false |

  Scenario: As a non logged-in user I can not delete a comment
    When I send a "DELETE" request to "/api/ideas-workshop/thread_comments/b99933f3-180c-4248-82f8-1b0eb950740d"
    Then the response status code should be 401

  Scenario: As a logged-in user I can not delete a comment that is not mine
    When I am logged as "jacques.picard@en-marche.fr"
    And I send a "DELETE" request to "/api/ideas-workshop/thread_comments/b99933f3-180c-4248-82f8-1b0eb950740d"
    Then the response status code should be 403

  Scenario: As a logged-in user I can delete my comment
    When I am logged as "benjyd@aol.com"
    And I send a "DELETE" request to "/api/ideas-workshop/thread_comments/b99933f3-180c-4248-82f8-1b0eb950740d"
    Then the response status code should be 204
    And the response should be empty
