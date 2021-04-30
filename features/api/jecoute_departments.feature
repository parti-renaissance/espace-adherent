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
        "code": "93",
        "name": "Provence-Alpes-Côte d'Azur",
        "campaign" : {
          "subtitle": "Bienvenue en PACA",
          "description": "Description PACA",
          "logo": "http://test.enmarche.code/assets/files/jemarche/regions/region-logo.jpg",
          "banner": null,
          "primary_color": "blue",
          "external_link": null
        }
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
        "code": "32",
        "name": "Hauts-de-France",
        "campaign": {
          "subtitle": "Bienvenue en Hauts-de-France",
          "description": "Description des Hauts-de-France",
          "logo": "http://test.enmarche.code/assets/files/jemarche/regions/region-logo.jpg",
          "banner": "http://test.enmarche.code/assets/files/jemarche/regions/region-banner.jpg",
          "primary_color": "green",
          "external_link": "https://en-marche.fr"
        }
      }
    }
    """

    When I send a "GET" request to "/api/jecoute/departments/77700"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "region": {
            "code": "11",
            "name": "Île-de-France",
            "campaign": null
        },
        "code": "77",
        "name": "Seine-et-Marne"
    }
    """

    When I send a "GET" request to "/api/jecoute/departments/75116"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "region": {
            "code": "11",
            "name": "Île-de-France",
            "campaign": null
        },
        "code": "75",
        "name": "Paris"
    }
    """

  Scenario: As an authenticated user I should get a 404 for an unknown postal code
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/departments/unknown_code"
    Then the response status code should be 404
    When I send a "GET" request to "/api/jecoute/departments/99999"
    Then the response status code should be 404
