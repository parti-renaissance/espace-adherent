@api
Feature:
  In order to get all news
  With a valid oauth token
  I should be able to access to the news

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
          "total_items": 5,
          "items_per_page": 2,
          "count": 2,
          "current_page": 1,
          "last_page": 3
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
          "total_items": 5,
          "items_per_page": 1,
          "count": 1,
          "current_page": 1,
          "last_page": 5
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
          "total_items": 5,
          "items_per_page": 1,
          "count": 1,
          "current_page": 2,
          "last_page": 5
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
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
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
                "zone": {
                    "code": "59",
                    "created_at": "@string@.isDateTime()",
                    "name": "Nord",
                    "uuid": "e3eff020-906e-11eb-a875-0242ac150002"
                },
                "notification": true,
                "published": true,
                "creator": "Anonyme"
            },
            {
                "uuid": "b2b8e6a3-f5a9-4b34-a761-37438c3c3602",
                "title": "[Référent] Nouvelle actualité à 92 du référent",
                "text": "Ut porttitor vitae velit sit amet posuere. Mauris semper sagittis diam, convallis viverra lorem rutrum.",
                "external_link": "https://referent.en-marche.fr",
                "created_at": "@string@.isDateTime()",
                "zone": {
                    "code": "92",
                    "created_at": "@string@.isDateTime()",
                    "name": "Hauts-de-Seine",
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
                },
                "notification": true,
                "published": true,
                "creator": "Referent Referent"
            },
            {
                "uuid": "6c70f8e8-6bce-4376-8b9e-3ce342880673",
                "title": "[Référent] Nouvelle actualité non publiée à 59 du référent délégué",
                "text": "Fusce lacinia, diam et sodales iaculis, velit ante mollis ex, eu commodo felis lectus eu dui.",
                "external_link": "https://referent.en-marche.fr",
                "created_at": "@string@.isDateTime()",
                "zone": {
                    "code": "59",
                    "created_at": "@string@.isDateTime()",
                    "name": "Nord",
                    "uuid": "e3eff020-906e-11eb-a875-0242ac150002"
                },
                "notification": true,
                "published": false,
                "creator": "Bob Senateur (59)"
            },
            {
                "uuid": "560bab7a-d624-47d6-bf5e-3864c2406daf",
                "title": "Nouvelle actualité à 92 de l'admin",
                "text": "Curabitur in fermentum urna, sit amet venenatis orci. Proin accumsan ultricies congue.",
                "external_link": "https://referent.en-marche.fr",
                "created_at": "@string@.isDateTime()",
                "zone": {
                    "code": "92",
                    "created_at": "@string@.isDateTime()",
                    "name": "Hauts-de-Seine",
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
                },
                "notification": true,
                "published": true,
                "creator": "Anonyme"
            }
        ]
    }
    """

  Scenario: As a DC user with national role I can get the news list
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
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
                "zone": null,
                "notification": true,
                "published": false,
                "creator": "Anonyme"
            },
            {
                "uuid": "560bab7a-d624-47d6-bf5e-3864c2406daf",
                "title": "Nouvelle actualité à 92 de l'admin",
                "text": "Curabitur in fermentum urna, sit amet venenatis orci. Proin accumsan ultricies congue.",
                "external_link": "https://referent.en-marche.fr",
                "created_at": "@string@.isDateTime()",
                "zone": {
                    "code": "92",
                    "created_at": "@string@.isDateTime()",
                    "name": "Hauts-de-Seine",
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
                },
                "notification": true,
                "published": true,
                "creator": "Anonyme"
            }
        ]
    }
    """

  Scenario: As a logged-in user with no correct rights I cannot create a news
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/jecoute/news?scope=deputy" with body:
    """
    {
      "title": "Une nouvelle actualité"
    }
    """
    Then the response status code should be 403

  Scenario: As a logged-in user I cannot create a news with no data
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/jecoute/news?scope=national" with body:
    """
    {}
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "title: Cette valeur ne doit pas être vide.\ntext: Cette valeur ne doit pas être vide.",
        "violations": [
            {
                "propertyPath": "title",
                "message": "Cette valeur ne doit pas être vide."
            },
            {
                "propertyPath": "text",
                "message": "Cette valeur ne doit pas être vide."
            }
        ]
    }
    """

  Scenario: As a logged-in user I cannot create a news with invalid data
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/jecoute/news?scope=national" with body:
    """
    {
      "title": "Une nouvelle actualité d'aujourd'hui - Une nouvelle actualité d'aujourd'hui - Une nouvelle actualité d'aujourd'hui - Une nouvelle actualité d'aujourd'hui - Une nouvelle actualité d'aujourd'hui - Une nouvelle actualité d'aujourd'hui - Une nouvelle actualité d'aujourd'hui",
      "text": "Fusce quam lorem, lacinia ut erat a, ultrices eleifend urna. Aenean sit amet tristique ante, at malesuada dui. Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus at. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula, dapibus nibh quis, hendrerit ante. Etiam suscipit dolor vitae leo congue, quis vestibulum massa porttitor. Phasellus diam urna, tempor vitae neque vel, tempor pellentesque orci. Vivamus vel ipsum a sapien interdum rhoncus sit amet vitae quam. Nunc blandit consectetur odio in porttitor. In pellentesque nibh id arcu efficitur, sed finibus nisi consectetur. Sed laoreet rutrum mauris, a semper tellus ultricies vitae. Aenean lacus urna, sollicitudin sed tristique sed, auctor et lorem. In mattis placerat suscipit. Nullam congue felis quis massa mollis placerat. Integer scelerisque faucibus nulla ac luctus. Vivamus lobortis consectetur sodales. Maecenas urna dui, egestas ut ligula sit amet, volutpat commodo sapien. Etiam ac libero est.",
      "external_link": "testlink"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "title: Vous devez saisir au maximum 120 caractères.\ntext: Vous devez saisir au maximum 1000 caractères.\nexternal_link: Cette valeur n'est pas une URL valide.",
        "violations": [
            {
                "propertyPath": "title",
                "message": "Vous devez saisir au maximum 120 caractères."
            },
            {
                "propertyPath": "text",
                "message": "Vous devez saisir au maximum 1000 caractères."
            },
            {
                "propertyPath": "external_link",
                "message": "Cette valeur n'est pas une URL valide."
            }
        ]
    }
    """

  Scenario: As a logged-in user I can create a news
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    Then I should have 0 notification
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/jecoute/news?scope=national" with body:
    """
    {
      "title": "Une nouvelle actualité d'aujourd'hui",
      "text": "Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula.",
      "external_link": "http://test.en-marche.fr",
      "global": true,
      "notification": true,
      "published": true
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
        "uuid": "@uuid@",
        "title": "Une nouvelle actualité d'aujourd'hui",
        "text": "Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula.",
        "external_link": "http://test.en-marche.fr",
        "zone": null,
        "created_at": "@string@.isDateTime()",
        "global": true,
        "notification": true,
        "published": true,
        "creator": "Député PARIS I"
    }
    """
    And I should have 1 notification "NewsCreatedNotification" with data:
      | key   | value                                         |
      | topic | staging_jemarche_global                       |
      | title | Une nouvelle actualité d'aujourd'hui          |
      | body  | Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula. |

  Scenario: As a logged-in user I can update a news of my zone
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/jecoute/news/6c70f8e8-6bce-4376-8b9e-3ce342880673?scope=referent" with body:
    """
    {
      "title": "[Référent] Nouveau titre",
      "text": "Nouveau texte",
      "external_link": "https://nouveau.en-marche.fr",
      "notification": false,
      "published": false
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "uuid": "@uuid@",
      "title": "[Référent] Nouveau titre",
      "text": "Nouveau texte",
      "external_link": "https://nouveau.en-marche.fr",
      "zone": {
          "code": "59",
          "created_at": "@string@.isDateTime()",
          "name": "Nord",
          "uuid": "e3eff020-906e-11eb-a875-0242ac150002"
      },
      "created_at": "@string@.isDateTime()",
      "notification": false,
      "published": false,
      "creator": "Bob Senateur (59)"
    }
    """

  Scenario: As a logged-in user with National role I can update a news
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/jecoute/news/232f99b8-7a0c-40ed-ba9e-bf8f33e19052?scope=national" with body:
    """
    {
      "published": false
    }
    """
    Then the response status code should be 200

  Scenario: As a logged-in user I cannot update a news out of my zone
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/jecoute/news/25632c43-c224-4745-84d7-09dfa8249367?scope=referent" with body:
    """
    {
      "title": "Une nouvelle actualité d'aujourd'hui",
      "text": "Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula.",
      "external_link": "http://test.en-marche.fr",
      "global": true,
      "notification": true,
      "published": true
    }
    """
    Then the response status code should be 403

  Scenario: As a DC referent I cannot create a local news without a zone
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/jecoute/news?scope=referent" with body:
    """
    {
      "title": "Une nouvelle actualité d'aujourd'hui",
      "text": "Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula.",
      "external_link": "http://test.en-marche.fr",
      "notification": true,
      "published": true
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "zone: Une zone est nécessaire pour une actualité référente",
        "violations": [
            {
                "propertyPath": "zone",
                "message": "Une zone est nécessaire pour une actualité référente"
            }
        ]
    }
    """

  Scenario: As a DC referent I cannot create a local news with a city zone
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/jecoute/news?scope=referent" with body:
    """
    {
      "title": "Une nouvelle actualité d'aujourd'hui",
      "text": "Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula.",
      "external_link": "http://test.en-marche.fr",
      "zone": "e3f1a8e8-906e-11eb-a875-0242ac150002",
      "notification": true,
      "published": true
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "zone: Cette zone ne correspond pas à une région, un département ou un arrondissement",
      "violations": [
        {
          "propertyPath": "zone",
          "message": "Cette zone ne correspond pas à une région, un département ou un arrondissement"
        }
      ]
    }
    """

  Scenario: As a DC referent I cannot create a local news with a non managed zone
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/jecoute/news?scope=referent" with body:
    """
    {
      "title": "Une nouvelle actualité d'aujourd'hui",
      "text": "Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula.",
      "external_link": "http://test.en-marche.fr",
      "zone": "e3efe7bf-906e-11eb-a875-0242ac150002",
      "notification": true,
      "published": true
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "zone: Oups, vous n'avez pas accès à cette zone !",
      "violations": [
        {
          "propertyPath": "zone",
          "message": "Oups, vous n'avez pas accès à cette zone !"
        }
      ]
    }
    """
