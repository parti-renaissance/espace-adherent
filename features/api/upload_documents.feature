@api
Feature:
  In order to upload documents
  With a valid oauth token
  I should be able to access to document upload API

  Scenario: As a non authenticated user I cannot upload a document
    When I send a "POST" request to "/api/v3/upload/news"
    Then the response status code should be 401

  Scenario: As an authenticated user I cannot upload a document for invalid type
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "POST" request to "/api/v3/upload/invalid?scope=referent" with parameters:
      | key         | value      |
      | upload      | @image.jpg |
    Then the response status code should be 403

  Scenario: As an authenticated user I cannot upload a document without a document
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "POST" request to "/api/v3/upload/news?scope=referent"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "message": "Aucun document uploadé."
    }
    """

  Scenario Outline: As an authenticated user I can upload a document
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "POST" request to "/api/v3/upload/news?scope=<scope>" with parameters:
      | key         | value      |
      | upload      | @image.jpg |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "url": "http://test.enmarche.code/documents-partages/@uuid@/upload",
        "message": "Le document a été uploadé avec succès."
    }
    """
    Examples:
      | user                                    | scope                                           |
      | referent@en-marche-dev.fr               | referent                                        |
      | senateur@en-marche-dev.fr               | delegated_08f40730-d807-4975-8773-69d8fae1da74  |
      | senatorial-candidate@en-marche-dev.fr   | legislative_candidate                           |
      | gisele-berthoux@caramail.com            | delegated_b24fea43-ecd8-4bf4-b500-6f97886ab77c  |
