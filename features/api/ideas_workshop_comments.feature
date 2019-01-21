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
    And the JSON should be equal to:
    """
    {
       "metadata":{
          "total_items":9,
          "items_per_page":3,
          "count":3,
          "current_page":1,
          "last_page":3
       },
       "items":[
          {
             "uuid":"9e49e935-ba51-4ae5-981c-5f48e55fdf28",
             "content":"Commentaire d'un adhérent",
             "author":{
                "uuid":"acc73b03-9743-47d8-99db-5a6c6f55ad67",
                "first_name":"Benjamin",
                "last_name":"Duroc"
             },
             "approved":false,
             "created_at":"@string@.isDateTime()"
          },
          {
             "uuid":"37116c8b-a36e-4a0d-8346-baba91cd1330",
             "content":"Commentaire de l'adhérent à desadhérer",
             "author":{
                "uuid":"46ab0600-b5a0-59fc-83a7-cc23ca459ca0",
                "first_name":"Michel",
                "last_name":"VASSEUR"
             },
             "approved":false,
             "created_at":"@string@.isDateTime()"
          },
          {
             "uuid":"ecbe9136-3dc0-477d-b817-a25878dd639a",
             "content":"Commentaire signalé",
             "author":{
                "uuid":"93de5d98-383a-4863-9f47-eb7a348873a8",
                "first_name":"Laura",
                "last_name":"Deloche"
             },
             "approved":false,
             "created_at":"@string@.isDateTime()"
          }
       ]
    }
    """

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
                    "first_name": "Referent",
                    "last_name": "Referent"
                },
                "approved": false,
                "created_at": "@string@.isDateTime()"
            },
            {
                "uuid": "f716d3ba-004f-4958-af26-a7b010a6d458",
                "content": "Commentaire d'un référent",
                "author": {
                    "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                    "first_name": "Referent",
                    "last_name": "Referent"
                },
                "approved": false,
                "created_at": "@string@.isDateTime()"
            },
            {
                "uuid": "60123090-6cdc-4de6-9cb3-07e2ec411f2f",
                "content": "Lorem Ipsum Commentaris",
                "author": {
                    "uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                    "first_name": "Francis",
                    "last_name": "Brioul"
                },
                "approved": false,
                "created_at": "@string@.isDateTime()"
            }
        ]
    }
    """

  Scenario: As a logged-in user I can add my comment to a thread
    Given I am logged as "martine.lindt@gmail.com"
    And I add "Content-Type" header equal to "application/json"
    When I send a "POST" request to "/api/ideas-workshop/thread_comments" with body:
    """
    {
      "thread": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
      "content": "Phasellus vitae enim faucibus"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "thread":{
          "answer":{
             "id":1
          },
          "content":"J'ouvre une discussion sur le probl\u00e8me.",
          "author":{
             "uuid":"e6977a4d-2646-5f6c-9c82-88e58dca8458",
             "first_name":"Carl",
             "last_name":"Mirabeau"
          },
          "created_at": "@string@.isDateTime()",
          "uuid":"dfd6a2f2-5579-421f-96ac-98993d0edea1",
          "approved": false
       },
       "content":"Phasellus vitae enim faucibus",
       "author":{
          "uuid":"d4b1e7e1-ba18-42a9-ace9-316440b30fa7",
          "first_name":"Martine",
          "last_name":"Lindt"
       },
       "created_at": "@string@.isDateTime()",
       "uuid": "@uuid@",
       "approved": false
    }
    """

  Scenario: As a logged-in user I can not approve a comment
    Given I am logged as "carl999@example.fr"
    When I send a "PUT" request to "/api/ideas-workshop/thread_comments/b99933f3-180c-4248-82f8-1b0eb950740d/approval-toggle"
    Then the response status code should be 403

  Scenario: As an idea author, I can approve my comment
    Given I am logged as "jacques.picard@en-marche.fr"
    And I add "Content-Type" header equal to "application/json"
    When I send a "PUT" request to "/api/ideas-workshop/thread_comments/b99933f3-180c-4248-82f8-1b0eb950740d/approval-toggle" with body:
    """
    {
        "approved": true
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "content":"Aenean viverra efficitur lorem",
       "author":{
          "uuid":"acc73b03-9743-47d8-99db-5a6c6f55ad67",
          "first_name":"Benjamin",
          "last_name":"Duroc"
       },
       "created_at": "@string@.isDateTime()",
       "uuid":"b99933f3-180c-4248-82f8-1b0eb950740d",
       "approved": true
    }
    """

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
