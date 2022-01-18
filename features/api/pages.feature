@api
Feature:
  In order to see some statics pages
  As a user
  I should be able to access Page API

  Scenario: As a non logged-in user I can get legalities texts
  Given I send a "GET" request to "/api/pages/je-mengage-mentions-legales"
  Then the response status code should be 200
  And the JSON should be equal to:
  """
    {
      "title": "Mentions légales",
      "content": "@string@"
    }
  """

  Scenario: As a non logged-in user I can get legalities texts
  Given I send a "GET" request to "/api/pages/je-mengage-politique-protection-donnees"
  Then the response status code should be 200
  And the JSON should be equal to:
  """
    {
      "title": "Politique de protection des données à caractère personnel",
      "content": "@string@"
    }
  """

  Scenario: As a non logged-in user I can get legalities texts
  Given I send a "GET" request to "/api/pages/je-mengage-mentions"
  Then the response status code should be 404
