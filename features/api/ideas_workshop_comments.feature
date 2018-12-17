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

  Scenario: As a non logged-in user I can see visibled threads paginated
    When I send a "GET" request to "/api/threads?page=1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 6,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 3
        },
        "items": [
            {
                "uuid": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
                "answer": {
                    "id": @integer@
                },
                "content": "J'ouvre une discussion sur le problème.",
                "author": {
                    "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                    "first_name": "Carl",
                    "last_name": "Mirabeau"
                }
            },
            {
                "uuid": "6b077cc4-1cbd-4615-b607-c23009119406",
                "answer": {
                    "id": @integer@
                },
                "content": "J'ouvre une discussion sur la solution.",
                "author": {
                    "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                    "first_name": "Lucie",
                    "last_name": "Olivera"
                }
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can see visibled thread comments paginated
    When I send a "GET" request to "/api/thread_comments?page=1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 6,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 3
        },
        "items": [
            {
                "uuid":"b99933f3-180c-4248-82f8-1b0eb950740d",
                "thread": {
                    "uuid":"dfd6a2f2-5579-421f-96ac-98993d0edea1",
                    "answer": {
                        "id": @integer@
                    },
                    "content": "J'ouvre une discussion sur le problème.",
                    "author": {
                        "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                        "first_name": "Carl",
                        "last_name": "Mirabeau"
                    }
                },
                "content": "Aenean viverra efficitur lorem",
                "author": {
                    "uuid": "acc73b03-9743-47d8-99db-5a6c6f55ad67",
                    "first_name": "Benjamin",
                    "last_name": "Duroc"
                }
            },
            {
                "uuid":"60123090-6cdc-4de6-9cb3-07e2ec411f2f",
                "thread": {
                    "uuid":"dfd6a2f2-5579-421f-96ac-98993d0edea1",
                    "answer": {
                        "id": @integer@
                    },
                    "content": "J'ouvre une discussion sur le problème.",
                    "author": {
                        "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                        "first_name": "Carl",
                        "last_name": "Mirabeau"
                    }
                },
                "content": "Lorem Ipsum Commentaris",
                "author": {
                    "uuid":"a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                    "first_name": "Francis",
                    "last_name": "Brioul"
                }
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can see visibled threads for an idea
    When I send a "GET" request to "/api/threads?answer.idea=2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 1,
            "items_per_page": 2,
            "count": 1,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "uuid": "7857957c-2044-4469-bd9f-04a60820c8bd",
                "answer": {
                    "id": @integer@
                },
                "content": "[Help Ecology] J'ouvre une discussion sur le problème.",
                "author": {
                    "uuid":"b4219d47-3138-5efd-9762-2ef9f9495084",
                    "first_name": "Gisele",
                    "last_name": "Berthoux"
                }
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can see visibled thread comments for an idea
    When I send a "GET" request to "/api/threads/1/comments"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 3,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 2
        },
        "items": [
            {
                "uuid": "b99933f3-180c-4248-82f8-1b0eb950740d",
                "thread": {
                    "uuid": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
                    "answer": {
                        "id": @integer@
                    },
                    "content": "J'ouvre une discussion sur le problème.",
                    "author": {
                        "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                        "first_name": "Carl",
                        "last_name": "Mirabeau"
                    }
                },
                "content": "Aenean viverra efficitur lorem",
                "author": {
                    "uuid": "acc73b03-9743-47d8-99db-5a6c6f55ad67",
                    "first_name": "Benjamin",
                    "last_name": "Duroc"
                }
            },
            {
                "uuid": "60123090-6cdc-4de6-9cb3-07e2ec411f2f",
                "thread": {
                    "uuid": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
                    "answer": {
                        "id": @integer@
                    },
                    "content": "J'ouvre une discussion sur le problème.",
                    "author": {
                        "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                        "first_name": "Carl",
                        "last_name": "Mirabeau"
                    }
                },
                "content": "Lorem Ipsum Commentaris",
                "author": {
                    "uuid":"a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                    "first_name": "Francis",
                    "last_name": "Brioul"
                }
            }
        ]
    }
    """
