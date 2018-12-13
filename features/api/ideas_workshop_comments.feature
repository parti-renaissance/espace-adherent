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
                "answer": {
                    "id": @integer@
                },
                "id": 1,
                "content": "J'ouvre une discussion sur le problème.",
                "author": {
                    "first_name": "Carl",
                    "last_name": "Mirabeau"
                }
            },
            {
                "answer": {
                    "id": @integer@
                },
                "id": 2,
                "content": "J'ouvre une discussion sur la solution.",
                "author": {
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
                "thread": {
                    "answer": {
                        "id": @integer@
                    },
                    "id": 1,
                    "content": "J'ouvre une discussion sur le problème.",
                    "author": {
                        "first_name": "Carl",
                        "last_name": "Mirabeau"
                    }
                },
                "id": 1,
                "content": "Aenean viverra efficitur lorem",
                "author": {
                    "first_name": "Benjamin",
                    "last_name": "Duroc"
                }
            },
            {
                "thread": {
                    "answer": {
                        "id": @integer@
                    },
                    "id": 1,
                    "content": "J'ouvre une discussion sur le problème.",
                    "author": {
                        "first_name": "Carl",
                        "last_name": "Mirabeau"
                    }
                },
                "id": 2,
                "content": "Lorem Ipsum Commentaris",
                "author": {
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
                "answer": {
                    "id": @integer@
                },
                "id": 6,
                "content": "[Help Ecology] J'ouvre une discussion sur le problème.",
                "author": {
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
                "thread": {
                    "answer": {
                        "id": @integer@
                    },
                    "id": 1,
                    "content": "J'ouvre une discussion sur le problème.",
                    "author": {
                        "first_name": "Carl",
                        "last_name": "Mirabeau"
                    }
                },
                "id": 1,
                "content": "Aenean viverra efficitur lorem",
                "author": {
                    "first_name": "Benjamin",
                    "last_name": "Duroc"
                }
            },
            {
                "thread": {
                    "answer": {
                        "id": @integer@
                    },
                    "id": 1,
                    "content": "J'ouvre une discussion sur le problème.",
                    "author": {
                        "first_name": "Carl",
                        "last_name": "Mirabeau"
                    }
                },
                "id": 2,
                "content": "Lorem Ipsum Commentaris",
                "author": {
                    "first_name": "Francis",
                    "last_name": "Brioul"
                }
            }
        ]
    }
    """
