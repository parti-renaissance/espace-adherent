@api
Feature:
  In order to get all regions
  With a valid oauth token
  I should be able to access to the regions configuration

  Background:
    Given the following fixtures are loaded:
      | LoadJecouteRegionData |
      | LoadClientData        |
      | LoadOAuthTokenData    |

  Scenario: As a non authenticated user I cannot get the regions configuration
    When I send a "GET" request to "/api/jecoute/regions"
    Then the response status code should be 401

  Scenario: As an authenticated user I can get the regions configuration
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/regions"
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
            "uuid": "88275043-adb5-463a-8a62-5248fe7aacbf",
            "name": "Normandie",
            "code": "28",
            "created_at": "@string@.isDateTime()",
            "subtitle": "Bienvenue en Normandie",
            "description": "Description de la normandie",
            "primary_color": "red",
            "external_link": "https://en-marche.fr",
            "slug": "normandie",
            "logo": "http://test.enmarche.code/assets/files/jemarche/regions/region-logo.jpg",
            "banner": "http://test.enmarche.code/assets/files/jemarche/regions/region-banner.jpg"
          },
          {
            "uuid": "c91391e9-4a08-4d14-8960-6c3508c1dddc",
            "name": "Hauts-de-France",
            "code": "32",
            "created_at": "@string@.isDateTime()",
            "subtitle": "Bienvenue en Hauts-de-France",
            "description": "Description des Hauts-de-France",
            "primary_color": "green",
            "external_link": null,
            "slug": "hauts-de-france",
            "logo": "http://test.enmarche.code/assets/files/jemarche/regions/region-logo.jpg",
            "banner": null
          }
        ]
      }
    """

  Scenario: As an authenticated user I can get the regions configuration with a specific page size and page number
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/regions?page_size=1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "metadata": {
          "total_items": 2,
          "items_per_page": 1,
          "count": 1,
          "current_page": 1,
          "last_page": 2
        },
        "items": [
          {
            "uuid": "88275043-adb5-463a-8a62-5248fe7aacbf",
            "name": "Normandie",
            "code": "28",
            "created_at": "@string@.isDateTime()",
            "subtitle": "Bienvenue en Normandie",
            "description": "Description de la normandie",
            "primary_color": "red",
            "external_link": "https://en-marche.fr",
            "slug": "normandie",
            "logo": "http://test.enmarche.code/assets/files/jemarche/regions/region-logo.jpg",
            "banner": "http://test.enmarche.code/assets/files/jemarche/regions/region-banner.jpg"
          }
        ]
      }
    """
    When I send a "GET" request to "/api/jecoute/regions?page_size=1&page=2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "metadata": {
          "total_items": 2,
          "items_per_page": 1,
          "count": 1,
          "current_page": 2,
          "last_page": 2
        },
        "items": [
          {
            "uuid": "c91391e9-4a08-4d14-8960-6c3508c1dddc",
            "name": "Hauts-de-France",
            "code": "32",
            "created_at": "@string@.isDateTime()",
            "subtitle": "Bienvenue en Hauts-de-France",
            "description": "Description des Hauts-de-France",
            "primary_color": "green",
            "external_link": null,
            "slug": "hauts-de-france",
            "logo": "http://test.enmarche.code/assets/files/jemarche/regions/region-logo.jpg",
            "banner": null
          }
        ]
      }
    """

  Scenario: As an authenticated user I can filter the regions configuration
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/regions?name=Norma"
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
            "uuid": "88275043-adb5-463a-8a62-5248fe7aacbf",
            "name": "Normandie",
            "code": "28",
            "created_at": "@string@.isDateTime()",
            "subtitle": "Bienvenue en Normandie",
            "description": "Description de la normandie",
            "primary_color": "red",
            "external_link": "https://en-marche.fr",
            "slug": "normandie",
            "logo": "http://test.enmarche.code/assets/files/jemarche/regions/region-logo.jpg",
            "banner": "http://test.enmarche.code/assets/files/jemarche/regions/region-banner.jpg"
          }
        ]
      }
    """
    When I send a "GET" request to "/api/jecoute/regions?code=32"
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
            "uuid": "c91391e9-4a08-4d14-8960-6c3508c1dddc",
            "name": "Hauts-de-France",
            "code": "32",
            "created_at": "@string@.isDateTime()",
            "subtitle": "Bienvenue en Hauts-de-France",
            "description": "Description des Hauts-de-France",
            "primary_color": "green",
            "external_link": null,
            "slug": "hauts-de-france",
            "logo": "http://test.enmarche.code/assets/files/jemarche/regions/region-logo.jpg",
            "banner": null
          }
        ]
      }
    """

  Scenario: As a non authenticated user I cannot get the configuration of a single region for a given region code
    When I send a "GET" request to "/api/jecoute/regions/28"
    Then the response status code should be 401

  Scenario: As an authenticated user I can get the configuration of a single region for a given region code
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/regions/28"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "uuid": "88275043-adb5-463a-8a62-5248fe7aacbf",
        "name": "Normandie",
        "code": "28",
        "created_at": "@string@.isDateTime()",
        "subtitle": "Bienvenue en Normandie",
        "description": "Description de la normandie",
        "primary_color": "red",
        "external_link": "https://en-marche.fr",
        "slug": "normandie",
        "logo": "http://test.enmarche.code/assets/files/jemarche/regions/region-logo.jpg",
        "banner": "http://test.enmarche.code/assets/files/jemarche/regions/region-banner.jpg"
      }
    """

  Scenario: As an authenticated user I should get a 404 for an unknown region code
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/regions/unknown_code"
    Then the response status code should be 404
