@api
Feature:
  In order to get all news
  With a valid oauth token
  I should be able to access to the news

  Background:
    Given the following fixtures are loaded:
      | LoadGeoZoneData                 |
      | LoadJecouteNewsData             |
      | LoadClientData                  |
      | LoadOAuthTokenData              |
      | LoadAdherentData                |
      | LoadGeoZoneData                 |
      | LoadReferentTagData             |
      | LoadReferentTagsZonesLinksData  |
      | LoadScopeData                   |

  Scenario: As a non authenticated user I cannot get the news list
    When I send a "GET" request to "/api/jecoute/news"
    Then the response status code should be 401

  Scenario: As an authenticated user I can get the news list
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/news"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "metadata": {
          "total_items": 4,
          "items_per_page": 2,
          "count": 2,
          "current_page": 1,
          "last_page": 2
        },
        "items": [
          {
            "uuid": "16373659-fed1-443c-a956-a257e2c2bae4",
            "title": "[Régionales] Nouveau sondage disponible",
            "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
            "external_link": null,
            "created_at": "@string@.isDateTime()"
          },
          {
            "uuid": "0bc3f920-da90-4773-80e1-a388005926fc",
            "title": "[Régionales] Rassemblement",
            "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
            "external_link": "https://en-marche.fr",
            "created_at": "@string@.isDateTime()"
          }
        ]
      }
    """

  Scenario: As an authenticated user I can get the news list with a specific page size and page number
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/news?page_size=1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "metadata": {
          "total_items": 4,
          "items_per_page": 1,
          "count": 1,
          "current_page": 1,
          "last_page": 4
        },
        "items": [
          {
            "uuid": "16373659-fed1-443c-a956-a257e2c2bae4",
            "title": "[Régionales] Nouveau sondage disponible",
            "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
            "external_link": null,
            "created_at": "@string@.isDateTime()"
          }
        ]
      }
    """
    When I send a "GET" request to "/api/jecoute/news?page_size=1&page=2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "metadata": {
          "total_items": 4,
          "items_per_page": 1,
          "count": 1,
          "current_page": 2,
          "last_page": 4
        },
        "items": [
          {
            "uuid": "0bc3f920-da90-4773-80e1-a388005926fc",
            "title": "[Régionales] Rassemblement",
            "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
            "external_link": "https://en-marche.fr",
            "created_at": "@string@.isDateTime()"
          }
        ]
      }
    """

  Scenario: As an authenticated user I can filter the news list
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/news?title=rassem"
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
            "uuid": "0bc3f920-da90-4773-80e1-a388005926fc",
            "title": "[Régionales] Rassemblement",
            "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
            "external_link": "https://en-marche.fr",
            "created_at": "@string@.isDateTime()"
          }
        ]
      }
    """

  Scenario: As an authenticated user I can filter the news list by postal code
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/news?zipCode=59000"
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
            "uuid": "16373659-fed1-443c-a956-a257e2c2bae4",
            "title": "[Régionales] Nouveau sondage disponible",
            "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
            "external_link": null,
            "created_at": "@string@.isDateTime()"
          }
        ]
      }
    """
    When I send a "GET" request to "/api/jecoute/news?zipCode=92270"
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
            "uuid": "0bc3f920-da90-4773-80e1-a388005926fc",
            "title": "[Régionales] Rassemblement",
            "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
            "external_link": "https://en-marche.fr",
            "created_at": "@string@.isDateTime()"
          },
          {
            "uuid": "b2b8e6a3-f5a9-4b34-a761-37438c3c3602",
            "title": "[Référent] Nouvelle actualité à 92 du référent",
            "text": "Ut porttitor vitae velit sit amet posuere. Mauris semper sagittis diam, convallis viverra lorem rutrum.",
            "external_link": "https://referent.en-marche.fr",
            "created_at":  "@string@.isDateTime()"
          }
        ]
      }
    """

  Scenario: As a non authenticated user I cannot get a single news for a given uuid
    When I send a "GET" request to "/api/jecoute/news/0bc3f920-da90-4773-80e1-a388005926fc"
    Then the response status code should be 401

  Scenario: As an authenticated user I can get a single news for a given uuid
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/news/0bc3f920-da90-4773-80e1-a388005926fc"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "uuid": "0bc3f920-da90-4773-80e1-a388005926fc",
        "title": "[Régionales] Rassemblement",
        "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
        "external_link": "https://en-marche.fr",
        "created_at": "@string@.isDateTime()"
      }
    """

  Scenario: As an authenticated user I should get a 404 for an unknown news uuid
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/news/0bc3f920-da90-4773-80e1-a388005926ff"
    Then the response status code should be 404

  Scenario: As a DC referent I can get the news list
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/jecoute/news?scope=referent&page_size=10"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 4,
            "items_per_page": 10,
            "count": 4,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "uuid": "16373659-fed1-443c-a956-a257e2c2bae4",
                "title": "[Régionales] Nouveau sondage disponible",
                "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
                "external_link": null,
                "created_at": "@string@.isDateTime()",
                "notification": true,
                "published": true
            },
            {
                "uuid": "b2b8e6a3-f5a9-4b34-a761-37438c3c3602",
                "title": "[Référent] Nouvelle actualité à 92 du référent",
                "text": "Ut porttitor vitae velit sit amet posuere. Mauris semper sagittis diam, convallis viverra lorem rutrum.",
                "external_link": "https://referent.en-marche.fr",
                "created_at": "@string@.isDateTime()",
                "notification": true,
                "published": true
            },
            {
                "uuid": "6c70f8e8-6bce-4376-8b9e-3ce342880673",
                "title": "[Référent] Nouvelle actualité non publié à 92 du référent délégué",
                "text": "Fusce lacinia, diam et sodales iaculis, velit ante mollis ex, eu commodo felis lectus eu dui.",
                "external_link": "https://referent.en-marche.fr",
                "created_at": "@string@.isDateTime()",
                "notification": true,
                "published": false
            },
            {
                "uuid": "560bab7a-d624-47d6-bf5e-3864c2406daf",
                "title": "Nouvelle actualité à 92 de l'admin",
                "text": "Curabitur in fermentum urna, sit amet venenatis orci. Proin accumsan ultricies congue.",
                "external_link": "https://referent.en-marche.fr",
                "created_at": "@string@.isDateTime()",
                "notification": true,
                "published": true
            }
        ]
    }
    """

  Scenario: As a DC user with national role I can get the news list
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/jecoute/news?scope=national"
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
                "uuid": "232f99b8-7a0c-40ed-ba9e-bf8f33e19052",
                "title": "Nouveau assemblement",
                "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
                "external_link": "https://en-marche.fr",
                "created_at": "@string@.isDateTime()",
                "notification": true,
                "published": false
            },
            {
                "uuid": "560bab7a-d624-47d6-bf5e-3864c2406daf",
                "title": "Nouvelle actualité à 92 de l'admin",
                "text": "Curabitur in fermentum urna, sit amet venenatis orci. Proin accumsan ultricies congue.",
                "external_link": "https://referent.en-marche.fr",
                "created_at": "@string@.isDateTime()",
                "notification": true,
                "published": true
            }
        ]
    }
    """
