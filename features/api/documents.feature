@api
Feature:
  In order to see documents
  As a logged-in user
  I should be able to access documents API

  Scenario Outline: As a granted user, I can get the documents list
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    And I send a "GET" request to "/api/v3/documents?scope=<scope>"
    Then the response status code should be 200
    And the response should be in JSON
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
                "title": "Document #1",
                "comment": "@string@",
                "uuid": "29eec30e-8f30-41f9-87b9-821d275d19dc",
                "file_path": "@string@.isUrl()"
            },
            {
                "title": "Document #2",
                "comment": "@string@",
                "uuid": "648e7b13-ef89-4b8a-8302-19c66654ed15",
                "file_path": "@string@.isUrl()"
            }
        ]
    }

    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
