@api
Feature:
  As a logged-in user
  I should be able to access my informations

  Background:
    Given the following fixtures are loaded:
      | LoadClientData        |
      | LoadOAuthTokenData    |

  Scenario: As a non logged-in user I cannot get my informations
    When I send a "GET" request to "/api/me"
    Then the response status code should be 401

  Scenario: As a logged-in user I can get my informations with additional informations based on granted scope
    Given I am logged with "carl999@example.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I send a "GET" request to "/api/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
        "email_address": "carl999@example.fr",
        "first_name": "Carl",
        "last_name": "Mirabeau",
        "country": "FR",
        "postal_code": "73100",
        "nickname": "pont",
        "use_nickname": false,
        "comments_cgu_accepted": false,
        "elected": false,
        "larem": false,
        "detailed_roles": [],
        "zipCode": "73100",
        "firstName": "Carl",
        "lastName": "Mirabeau",
        "surveys": {
          "total": 0,
          "last_month": 0
        }
      }
    """
