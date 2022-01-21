@api
Feature:
  In order to see my teams
  As a logged-in user
  I should be able to access API my teams

  Scenario Outline: As Anonymous user I cannot access teams endpoints
    When I send a "<method>" request to "<url>"
    Then the response status code should be 401
    Examples:
      | method | url                              |
      | POST   | /api/v3/my_teams                 |

  Scenario Outline: As a logged-in user without correct right I cannot access teams endpoints
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 403

    Examples:
      | method | url                              |
      | POST   | /api/v3/my_teams                 |

  Scenario: As a referent I cannot create my team if no scope
    Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "POST" request to "/api/v3/my_teams"
    Then the response status code should be 403

  Scenario: As a referent I cannot create my team for the scope I don't have
    Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "POST" request to "/api/v3/my_teams?scope=correspondent"
    Then the response status code should be 403

  Scenario: As a referent I can create my team
    Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "POST" request to "/api/v3/my_teams?scope=referent"
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
        "uuid": "@uuid@"
    }
    """

  Scenario: As a referent I will get my team when I want to create it, but it already exists
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "POST" request to "/api/v3/my_teams?scope=referent"
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
        "uuid": "7fab9d6c-71a1-4257-b42b-c6b9b2350a26"
    }
    """

