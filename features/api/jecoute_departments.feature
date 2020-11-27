@api
Feature:
  In order to be localized on JeMarche mobile app
  With a valid oauth token
  I should be able to access a department informations

  Background:
    Given the following fixtures are loaded:
      | LoadJecouteRegionData |
      | LoadClientData        |
      | LoadOAuthTokenData    |

  Scenario: As a non authenticated user I cannot get the informations of a single department for a given postal code
    When I send a "GET" request to "/api/jecoute/departments/06600"
    Then the response status code should be 401

  Scenario: As an authenticated user I can get the informations of a single department for a given postal code
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/departments/06600"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "code": "06",
      "name": "Alpes-Maritimes",
      "region": {
        "uuid": "62c6bf4c-72c9-4a29-bd5e-bf27b8ee2228",
        "code": "93",
        "name": "Provence-Alpes-Côte d'Azur",
        "subtitle": "Bienvenue en PACA",
        "description": "Description PACA",
        "logo": "http://test.enmarche.code/assets/files/jemarche/regions/region-logo.jpg",
        "banner": null,
        "primaryColor": "blue",
        "externalLink": null
      }
    }
    """
    When I send a "GET" request to "/api/jecoute/departments/59640"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "code": "59",
      "name": "Nord",
      "region": {
        "uuid": "c91391e9-4a08-4d14-8960-6c3508c1dddc",
        "code": "32",
        "name": "Hauts-de-France",
        "subtitle": "Bienvenue en Hauts-de-France",
        "description": "Description des Hauts-de-France",
        "logo": "http://test.enmarche.code/assets/files/jemarche/regions/region-logo.jpg",
        "banner": "http://test.enmarche.code/assets/files/jemarche/regions/region-banner.jpg",
        "primaryColor": "green",
        "externalLink": "https://en-marche.fr"
      }
    }
    """

  Scenario: As an authenticated user I should get a 404 for an unknown postal code
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/departments/unknown_code"
    Then the response status code should be 404
    When I send a "GET" request to "/api/jecoute/departments/99999"
    Then the response status code should be 404
