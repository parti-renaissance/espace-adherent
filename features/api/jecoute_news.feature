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
    When I send a "GET" request to "/api/jecoute/news?page_size=4"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "metadata": {
          "total_items": 11,
          "items_per_page": 4,
          "count": 4,
          "current_page": 1,
          "last_page": 3
        },
        "items": [
          {
            "uuid": "dd938794-2b00-400c-a817-9e04b5d20bc0",
            "title": "Pour toute la France",
            "text": "Nulla eleifend sed nisl eget efficitur. Nunc at ante diam. Phasellus condimentum dui nisi, sed imperdiet elit porttitor ut. Sed bibendum congue hendrerit. Proin pretium augue a urna interdum, ac congue felis egestas.",
            "external_link": "https://en-marche.fr",
            "link_label": "Voir le lien",
            "enriched": false,
            "pinned": true,
            "created_at": "@string@.isDateTime()",
            "visibility": "national",
            "creator": null
          },
          {
            "uuid": "72b68bf7-de51-4325-8933-02d2ff658dc3",
            "title": "[Référent] Actualité épinglée à 92 du référent",
            "text": "Nulla facilisi. Vestibulum vitae neque justo. Aliquam fringilla accumsan metus, sit amet blandit ligula.",
            "external_link": "https://epingle.en-marche.fr",
            "link_label": "Voir le lien",
            "enriched": false,
            "pinned": true,
            "created_at": "@string@.isDateTime()",
            "visibility": "local",
            "creator": "Referent Referent (référent)"
          },
          {
            "uuid": "16373659-fed1-443c-a956-a257e2c2bae4",
            "title": "[Régionales] Nouveau sondage disponible",
            "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
            "creator": "Anonyme (candidat aux départementales)",
            "external_link": null,
            "link_label": null,
            "enriched": false,
            "pinned": false,
            "visibility": "local",
            "created_at": "@string@.isDateTime()"
          },
          {
            "uuid": "0bc3f920-da90-4773-80e1-a388005926fc",
            "title": "[Régionales] Rassemblement",
            "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
            "creator": "Anonyme (candidat aux départementales)",
            "external_link": "https://en-marche.fr",
            "link_label": "Voir",
            "enriched": false,
            "pinned": false,
            "visibility": "local",
            "created_at": "@string@.isDateTime()"
          }
        ]
      }
    """

  Scenario: As an authenticated user I can get the news list with enriched news
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/news?with_enriched"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "metadata": {
          "total_items": 12,
          "items_per_page": 2,
          "count": 2,
          "current_page": 1,
          "last_page": 6
        },
        "items": [
          {
            "uuid": "dd938794-2b00-400c-a817-9e04b5d20bc0",
            "title": "Pour toute la France",
            "text": "Nulla eleifend sed nisl eget efficitur. Nunc at ante diam. Phasellus condimentum dui nisi, sed imperdiet elit porttitor ut. Sed bibendum congue hendrerit. Proin pretium augue a urna interdum, ac congue felis egestas.",
            "external_link": "https://en-marche.fr",
            "link_label": "Voir le lien",
            "enriched": false,
            "pinned": true,
            "created_at": "@string@.isDateTime()",
            "visibility": "national",
            "creator": null
          },
          {
            "uuid": "72b68bf7-de51-4325-8933-02d2ff658dc3",
            "title": "[Référent] Actualité épinglée à 92 du référent",
            "text": "Nulla facilisi. Vestibulum vitae neque justo. Aliquam fringilla accumsan metus, sit amet blandit ligula.",
            "creator": "Anonyme (candidat aux départementales)",
            "external_link": "https://epingle.en-marche.fr",
            "link_label": "Voir le lien",
            "enriched": false,
            "pinned": true,
            "visibility": "local",
            "creator": "Referent Referent (référent)",
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
          "total_items": 11,
          "items_per_page": 1,
          "count": 1,
          "current_page": 1,
          "last_page": 11
        },
        "items": [
          {
            "uuid": "dd938794-2b00-400c-a817-9e04b5d20bc0",
            "title": "Pour toute la France",
            "text": "Nulla eleifend sed nisl eget efficitur. Nunc at ante diam. Phasellus condimentum dui nisi, sed imperdiet elit porttitor ut. Sed bibendum congue hendrerit. Proin pretium augue a urna interdum, ac congue felis egestas.",
            "creator": null,
            "external_link": "https://en-marche.fr",
            "link_label": "Voir le lien",
            "enriched": false,
            "pinned": true,
            "visibility": "national",
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
          "total_items": 11,
          "items_per_page": 1,
          "count": 1,
          "current_page": 2,
          "last_page": 11
        },
        "items": [
          {
            "uuid": "72b68bf7-de51-4325-8933-02d2ff658dc3",
            "title": "[Référent] Actualité épinglée à 92 du référent",
            "text": "Nulla facilisi. Vestibulum vitae neque justo. Aliquam fringilla accumsan metus, sit amet blandit ligula.",
            "creator": "Anonyme (candidat aux départementales)",
            "external_link": "https://epingle.en-marche.fr",
            "link_label": "Voir le lien",
            "enriched": false,
            "pinned": true,
            "visibility": "local",
            "creator": "Referent Referent (référent)",
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
            "creator": "Anonyme (candidat aux départementales)",
            "external_link": "https://en-marche.fr",
            "link_label": "Voir",
            "enriched": false,
            "pinned": false,
            "visibility": "local",
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
          "total_items": 2,
          "items_per_page": 2,
          "count": 2,
          "current_page": 1,
          "last_page": 1
        },
        "items": [
          {
            "uuid": "dd938794-2b00-400c-a817-9e04b5d20bc0",
            "title": "Pour toute la France",
            "text": "Nulla eleifend sed nisl eget efficitur. Nunc at ante diam. Phasellus condimentum dui nisi, sed imperdiet elit porttitor ut. Sed bibendum congue hendrerit. Proin pretium augue a urna interdum, ac congue felis egestas.",
            "creator": null,
            "external_link": "https://en-marche.fr",
            "link_label": "Voir le lien",
            "enriched": false,
            "pinned": true,
            "visibility": "national",
            "created_at": "@string@.isDateTime()"
          },
          {
            "uuid": "16373659-fed1-443c-a956-a257e2c2bae4",
            "title": "[Régionales] Nouveau sondage disponible",
            "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
            "creator": "Anonyme (candidat aux départementales)",
            "external_link": null,
            "link_label": null,
            "enriched": false,
            "pinned": false,
            "visibility": "local",
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
          "total_items": 7,
          "items_per_page": 2,
          "count": 2,
          "current_page": 1,
          "last_page": 4
        },
        "items": [
          {
            "uuid": "dd938794-2b00-400c-a817-9e04b5d20bc0",
            "title": "Pour toute la France",
            "text": "Nulla eleifend sed nisl eget efficitur. Nunc at ante diam. Phasellus condimentum dui nisi, sed imperdiet elit porttitor ut. Sed bibendum congue hendrerit. Proin pretium augue a urna interdum, ac congue felis egestas.",
            "creator": null,
            "external_link": "https://en-marche.fr",
            "link_label": "Voir le lien",
            "enriched": false,
            "pinned": true,
            "visibility": "national",
            "created_at": "@string@.isDateTime()"
          },
          {
            "uuid": "72b68bf7-de51-4325-8933-02d2ff658dc3",
            "title": "[Référent] Actualité épinglée à 92 du référent",
            "text": "Nulla facilisi. Vestibulum vitae neque justo. Aliquam fringilla accumsan metus, sit amet blandit ligula.",
            "external_link": "https://epingle.en-marche.fr",
            "link_label": "Voir le lien",
            "enriched": false,
            "pinned": true,
            "created_at": "@string@.isDateTime()",
            "visibility": "local",
            "creator": "Referent Referent (référent)"
          }
        ]
      }
    """
    When I send a "GET" request to "/api/jecoute/news?zipCode=75008&page_size=5"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 5,
        "items_per_page": 5,
        "count": 5,
        "current_page": 1,
        "last_page": 1
        },
        "items": [
          {
            "uuid": "dd938794-2b00-400c-a817-9e04b5d20bc0",
            "title": "Pour toute la France",
            "text": "Nulla eleifend sed nisl eget efficitur. Nunc at ante diam. Phasellus condimentum dui nisi, sed imperdiet elit porttitor ut. Sed bibendum congue hendrerit. Proin pretium augue a urna interdum, ac congue felis egestas.",
            "external_link": "https://en-marche.fr",
            "link_label": "Voir le lien",
            "pinned": true,
            "enriched": false,
            "created_at": "@string@.isDateTime()",
            "visibility": "national",
            "creator": null
          },
          {
            "uuid": "0bc3f920-da90-4773-80e1-a388005926fc",
            "title": "[Régionales] Rassemblement",
            "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
            "external_link": "https://en-marche.fr",
            "link_label": "Voir",
            "pinned": false,
            "enriched": false,
            "created_at": "@string@.isDateTime()",
            "visibility": "local",
            "creator": "Anonyme (candidat aux départementales)"
          },
          {
            "uuid": "25632c43-c224-4745-84d7-09dfa8249367",
            "title": "[Référent] Une actualité à 75",
            "text": "Quisque interdum lectus et ultrices rhoncus. Cras nunc diam, rutrum eget velit vel, cursus varius justo.",
            "external_link": "https://75.en-marche.fr",
            "link_label": "Voir le lien",
            "pinned": false,
            "enriched": false,
            "created_at": "@string@.isDateTime()",
            "visibility": "local",
            "creator": "Referent75and77 Referent75and77 (référent)"
          },
          {
            "uuid": "4f5db386-1819-4055-abbd-fb5d840cd6c0",
            "title": "Une actualité d'un candidat aux législatives délégué à 75-1",
            "text": "Aenean varius condimentum diam in rutrum.",
            "external_link": "https://un-candidat-aux-legislatives-delegue.en-marche.fr",
            "link_label": "Voir le lien",
            "pinned": false,
            "enriched": false,
            "created_at": "@string@.isDateTime()",
            "visibility": "local",
            "creator": "Gisele Berthoux (candidat aux législatives)"
          },
          {
            "uuid": "2c28b246-b17e-409d-992a-b8a57481fb7a",
            "title": "Une actualité d'un candidat aux législatives à 75-1",
            "text": "Donec viverra odio.",
            "external_link": "https://un-candidat-aux-legislatives.en-marche.fr",
            "link_label": "Voir le lien",
            "pinned": false,
            "enriched": false,
            "created_at": "@string@.isDateTime()",
            "visibility": "local",
            "creator": "Jean-Baptiste Fortin (candidat aux législatives)"
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
        "creator": "Anonyme (candidat aux départementales)",
        "external_link": "https://en-marche.fr",
        "link_label": "Voir",
        "enriched": false,
        "pinned": false,
        "visibility": "local",
        "created_at": "@string@.isDateTime()"
      }
    """
    When I send a "GET" request to "/api/jecoute/news/82068546-47d1-4f78-a6ba-692812984442"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "uuid": "82068546-47d1-4f78-a6ba-692812984442",
        "title": "[Référent] Actualité épinglée et enrichie à 92 du référent",
        "text": "**Tincidunt** Sed vitae erat sagittis, *ultricies* nulla et, tincidunt eros.\n# In hac habitasse platea dictumst  \n## Pellentesque imperdiet erat arcu\nCras hendrerit, mi et malesuada convallis, elit orci hendrerit purus, a euismod erat nisl at lorem. \n\n### Eget varius felis sodales sit amet \n\nNulla at odio non augue congue sollicitudin.  [Mon URL](https://en-marche.fr)\nNulla ac augue sapien. In tincidunt nec massa ac rhoncus.![Mon image](https://cdn.futura-sciences.com/buildsv6/images/mediumoriginal/6/5/2/652a7adb1b_98148_01-intro-773.jpg)\n\nCras vitae fringilla nunc. Suspendisse facilisis rhoncus urna a placerat. \n\n* Vestibulum facilisis convallis mauris eu eleifend. \n* Aenean sit amet elementum ex. \n* In erat enim, pulvinar quis dui et, volutpat imperdiet nulla.\n\nSed eu nibh tempor, pulvinar lectus ac, mattis nunc. \n\n1. Praesent scelerisque sagittis orci in sagittis. \n2. Phasellus iaculis elementum iaculis.\n\nNulla facilisi. Vestibulum vitae neque justo. Aliquam fringilla accumsan metus, sit amet blandit ligula.",
        "external_link": "https://epingle.en-marche.fr",
        "link_label": "Voir le lien",
        "enriched": true,
        "pinned": true,
        "visibility": "local",
        "created_at": "@string@.isDateTime()",
        "creator": "Referent Referent (référent)"
      }
    """

  Scenario: As an authenticated user I should get a 404 for an unknown news uuid
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/jecoute/news/0bc3f920-da90-4773-80e1-a388005926ff"
    Then the response status code should be 404

  Scenario Outline: As a (delegated) referent I can get the news list
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/jecoute/news?scope=<scope>&page_size=10"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 8,
            "items_per_page": 10,
            "count": 8,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "uuid": "16373659-fed1-443c-a956-a257e2c2bae4",
                "title": "[Régionales] Nouveau sondage disponible",
                "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.",
                "external_link": null,
                "link_label": null,
                "created_at": "@string@.isDateTime()",
                "visibility": "local",
                "zone": {
                    "code": "59",
                    "created_at": "@string@.isDateTime()",
                    "name": "Nord",
                    "uuid": "e3eff020-906e-11eb-a875-0242ac150002"
                },
                "notification": true,
                "enriched": false,
                "pinned": false,
                "published": true,
                "creator": "Anonyme"
            },
            {
                "uuid": "b2b8e6a3-f5a9-4b34-a761-37438c3c3602",
                "title": "[Référent] Nouvelle actualité à 92 du référent",
                "text": "Ut porttitor vitae velit sit amet posuere. Mauris semper sagittis diam, convallis viverra lorem rutrum.",
                "external_link": "https://referent.en-marche.fr",
                "link_label": "Voir",
                "created_at": "@string@.isDateTime()",
                "visibility": "local",
                "zone": {
                    "code": "92",
                    "created_at": "@string@.isDateTime()",
                    "name": "Hauts-de-Seine",
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
                },
                "notification": true,
                "enriched": false,
                "pinned": false,
                "published": true,
                "creator": "Referent Referent"
            },
            {
                "uuid": "6c70f8e8-6bce-4376-8b9e-3ce342880673",
                "title": "[Référent] Nouvelle actualité non publiée à 59 du référent délégué",
                "text": "Fusce lacinia, diam et sodales iaculis, velit ante mollis ex, eu commodo felis lectus eu dui.",
                "external_link": "https://referent.en-marche.fr",
                "link_label": "Voir le lien",
                "created_at": "@string@.isDateTime()",
                "visibility": "local",
                "zone": {
                    "code": "59",
                    "created_at": "@string@.isDateTime()",
                    "name": "Nord",
                    "uuid": "e3eff020-906e-11eb-a875-0242ac150002"
                },
                "notification": true,
                "enriched": false,
                "pinned": false,
                "published": false,
                "creator": "Bob Senateur (59)"
            },
            {
                "uuid": "560bab7a-d624-47d6-bf5e-3864c2406daf",
                "title": "Nouvelle actualité à 92 de l'admin",
                "text": "Curabitur in fermentum urna, sit amet venenatis orci. Proin accumsan ultricies congue.",
                "external_link": "https://referent.en-marche.fr",
                "link_label": "Voir",
                "created_at": "@string@.isDateTime()",
                "visibility": "local",
                "zone": {
                    "code": "92",
                    "created_at": "@string@.isDateTime()",
                    "name": "Hauts-de-Seine",
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
                },
                "notification": true,
                "enriched": false,
                "pinned": false,
                "published": true,
                "creator": "Anonyme"
            },
            {
                "created_at": "@string@.isDateTime()",
                "creator": "Jules Fullstack",
                "external_link": "https://92.en-marche.fr",
                "link_label": "Voir le lien",
                "notification": false,
                "enriched": false,
                "pinned": false,
                "published": true,
                "text": "Cras libero mauris, euismod blandit ornare eu, congue quis nulla. Maecenas sodales diam nec tincidunt pulvinar.",
                "title": "Une actualité du correspondent à 92",
                "uuid": "b09185ba-f271-404b-a73f-76d92ca8c120",
                "visibility": "local",
                "zone": {
                    "code": "92",
                    "created_at": "2020-12-04T15:24:38+01:00",
                    "name": "Hauts-de-Seine",
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
                }
            },
            {
                "created_at": "@string@.isDateTime()",
                "creator": "Laura Deloche",
                "external_link": "https://92-delegated.en-marche.fr",
                "link_label": "Voir le lien",
                "notification": false,
                "enriched": false,
                "pinned": false,
                "published": true,
                "text": "Ut at porttitor ipsum. Sed quis volutpat ipsum.",
                "title": "Une actualité à 75",
                "uuid": "6101c6a6-f7ef-4952-95db-8553952d656d",
                "visibility": "local",
                "zone": {
                    "code": "92",
                    "created_at": "2020-12-04T15:24:38+01:00",
                    "name": "Hauts-de-Seine",
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
                }
            },
            {
                "uuid": "72b68bf7-de51-4325-8933-02d2ff658dc3",
                "title": "[Référent] Actualité épinglée à 92 du référent",
                "text": "Nulla facilisi. Vestibulum vitae neque justo. Aliquam fringilla accumsan metus, sit amet blandit ligula.",
                "external_link": "https://epingle.en-marche.fr",
                "link_label": "Voir le lien",
                "created_at": "@string@.isDateTime()",
                "visibility": "local",
                "zone": {
                    "code": "92",
                    "created_at": "2020-12-04T15:24:38+01:00",
                    "name": "Hauts-de-Seine",
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
                },
                "notification": true,
                "enriched": false,
                "pinned": true,
                "published": true,
                "creator": "Referent Referent"
            },
            {
                "uuid": "82068546-47d1-4f78-a6ba-692812984442",
                "title": "[Référent] Actualité épinglée et enrichie à 92 du référent",
                "text": "**Tincidunt** Sed vitae erat sagittis, *ultricies* nulla et, tincidunt eros.\n# In hac habitasse platea dictumst  \n## Pellentesque imperdiet erat arcu\nCras hendrerit, mi et malesuada convallis, elit orci hendrerit purus, a euismod erat nisl at lorem. \n\n### Eget varius felis sodales sit amet \n\nNulla at odio non augue congue sollicitudin.  [Mon URL](https://en-marche.fr)\nNulla ac augue sapien. In tincidunt nec massa ac rhoncus.![Mon image](https://cdn.futura-sciences.com/buildsv6/images/mediumoriginal/6/5/2/652a7adb1b_98148_01-intro-773.jpg)\n\nCras vitae fringilla nunc. Suspendisse facilisis rhoncus urna a placerat. \n\n* Vestibulum facilisis convallis mauris eu eleifend. \n* Aenean sit amet elementum ex. \n* In erat enim, pulvinar quis dui et, volutpat imperdiet nulla.\n\nSed eu nibh tempor, pulvinar lectus ac, mattis nunc. \n\n1. Praesent scelerisque sagittis orci in sagittis. \n2. Phasellus iaculis elementum iaculis.\n\nNulla facilisi. Vestibulum vitae neque justo. Aliquam fringilla accumsan metus, sit amet blandit ligula.",
                "external_link": "https://epingle.en-marche.fr",
                "link_label": "Voir le lien",
                "visibility": "local",
                "created_at": "@string@.isDateTime()",
                "zone": {
                    "code": "92",
                    "created_at": "2020-12-04T15:24:38+01:00",
                    "name": "Hauts-de-Seine",
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
                },
                "notification": true,
                "enriched": true,
                "pinned": true,
                "published": true,
                "creator": "Referent Referent"
            }
        ]
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a (delegated) legislative candidate I can get the news list
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/jecoute/news?scope=<scope>&page_size=10"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 2,
            "items_per_page": 10,
            "count": 2,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "uuid": "4f5db386-1819-4055-abbd-fb5d840cd6c0",
                "title": "Une actualité d'un candidat aux législatives délégué à 75-1",
                "text": "Aenean varius condimentum diam in rutrum.",
                "external_link": "https://un-candidat-aux-legislatives-delegue.en-marche.fr",
                "link_label": "Voir le lien",
                "notification": false,
                "published": true,
                "pinned": false,
                "enriched": false,
                "created_at": "@string@.isDateTime()",
                "visibility": "local",
                "zone": {
                    "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
                    "code": "75-1",
                    "name": "Paris (1)",
                    "created_at": "2020-12-04T15:24:38+01:00"
                },
                "creator": "Gisele Berthoux"
            },
            {
                "uuid": "2c28b246-b17e-409d-992a-b8a57481fb7a",
                "title": "Une actualité d'un candidat aux législatives à 75-1",
                "text": "Donec viverra odio.",
                "external_link": "https://un-candidat-aux-legislatives.en-marche.fr",
                "link_label": "Voir le lien",
                "notification": false,
                "published": true,
                "pinned": false,
                "enriched": false,
                "created_at": "@string@.isDateTime()",
                "visibility": "local",
                "zone": {
                    "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
                    "code": "75-1",
                    "name": "Paris (1)",
                    "created_at": "2020-12-04T15:24:38+01:00"
                },
                "creator": "Jean-Baptiste Fortin"
            }
        ]
    }
    """
    Examples:
      | user                                    | scope                                           |
      | senatorial-candidate@en-marche-dev.fr   | legislative_candidate                           |
      | gisele-berthoux@caramail.com            | delegated_b24fea43-ecd8-4bf4-b500-6f97886ab77c  |

  Scenario: As a user with national role I can get the news list
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
                "link_label": "Voir",
                "created_at": "@string@.isDateTime()",
                "visibility": "national",
                "zone": null,
                "notification": true,
                "enriched": false,
                "pinned": false,
                "published": false,
                "creator": "Anonyme"
            },
            {
                "uuid": "dd938794-2b00-400c-a817-9e04b5d20bc0",
                "title": "Pour toute la France",
                "text": "Nulla eleifend sed nisl eget efficitur. Nunc at ante diam. Phasellus condimentum dui nisi, sed imperdiet elit porttitor ut. Sed bibendum congue hendrerit. Proin pretium augue a urna interdum, ac congue felis egestas.",
                "external_link": "https://en-marche.fr",
                "link_label": "Voir le lien",
                "created_at": "@string@.isDateTime()",
                "visibility": "national",
                "zone": null,
                "notification": false,
                "enriched": false,
                "pinned": true,
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
        "detail": "text: Le texte est obligatoire.\ntitle: Cette valeur ne doit pas être vide.",
        "violations": [
            {
                "propertyPath": "text",
                "message": "Le texte est obligatoire."
            },
            {
                "propertyPath": "title",
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
        "detail": "text: Le texte ne doit pas contenir plus de 1000 caractères.\ntitle: Vous devez saisir au maximum 120 caractères.\nexternal_link: Cette valeur n'est pas une URL valide.",
        "violations": [
            {
                "propertyPath": "text",
                "message": "Le texte ne doit pas contenir plus de 1000 caractères."
            },
            {
                "propertyPath": "title",
                "message": "Vous devez saisir au maximum 120 caractères."
            },
            {
                "propertyPath": "external_link",
                "message": "Cette valeur n'est pas une URL valide."
            }
        ]
    }
    """

  Scenario: As a logged-in user with role national I can create a news
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    Then I should have 0 notification
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/jecoute/news?scope=national" with body:
    """
    {
      "title": "Une nouvelle actualité d'aujourd'hui",
      "text": "Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula.",
      "external_link": "http://test.en-marche.fr",
      "link_label": "Voir",
      "global": true,
      "notification": true,
      "enriched": false,
      "pinned": false,
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
        "link_label": "Voir",
        "visibility": "national",
        "zone": null,
        "created_at": "@string@.isDateTime()",
        "global": true,
        "notification": true,
        "enriched": false,
        "pinned": false,
        "published": true,
        "creator": "Député PARIS I"
    }
    """
    And I should have 1 notification "NewsCreatedNotification" with data:
      | key   | value                                         |
      | topic | staging_jemarche_global                       |
      | title | Une nouvelle actualité d'aujourd'hui          |
      | body  | Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula. |

  Scenario Outline: As a (delegated) referent I can create a news
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    Then I should have 0 notification
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/jecoute/news?scope=<scope>" with body:
    """
    {
      "title": "Une nouvelle actualité d'aujourd'hui",
      "text": "Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula.",
      "external_link": "http://test.en-marche.fr",
      "link_label": "Voir",
      "global": true,
      "notification": true,
      "published": true,
      "enriched": false,
      "pinned": false,
      "zone": "e3efe5c5-906e-11eb-a875-0242ac150002"
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
        "uuid": "@uuid@",
        "title": "[Référent] Une nouvelle actualité d'aujourd'hui",
        "text": "Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula.",
        "external_link": "http://test.en-marche.fr",
        "link_label": "Voir",
        "visibility": "local",
        "zone": {
            "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
            "code": "77",
            "name": "Seine-et-Marne",
            "created_at": "@string@.isDateTime()"
        },
        "created_at": "@string@.isDateTime()",
        "notification": true,
        "published": true,
        "enriched": false,
        "pinned": false,
        "creator": "Referent Referent"
    }
    """
    And I should have 1 notification "NewsCreatedNotification" with data:
      | key   | value                                             |
      | topic | staging_jemarche_department_77                    |
      | title | [Référent] Une nouvelle actualité d'aujourd'hui   |
      | body  | Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula. |
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a (delegated) correspondent I can create a news
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    Then I should have 0 notification
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/jecoute/news?scope=<scope>" with body:
    """
    {
      "title": "Une nouvelle actualité d'aujourd'hui",
      "text": "**Vestibulum** et lectus vehicula. *Sed* eget neque nec dolor gravida luctus.",
      "external_link": "http://correspondent.en-marche.fr",
      "link_label": "Voir",
      "notification": true,
      "published": true,
      "enriched": true,
      "pinned": true,
      "zone": "e3efe6fd-906e-11eb-a875-0242ac150002"
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
        "uuid": "@uuid@",
        "title":  "Une nouvelle actualité d'aujourd'hui",
        "text": "**Vestibulum** et lectus vehicula. *Sed* eget neque nec dolor gravida luctus.",
        "external_link": "http://correspondent.en-marche.fr",
        "link_label": "Voir",
        "visibility": "local",
        "zone": {
            "code": "92",
            "created_at": "2020-12-04T15:24:38+01:00",
            "name": "Hauts-de-Seine",
            "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
        },
        "created_at": "@string@.isDateTime()",
        "notification": true,
        "published": true,
        "enriched": true,
        "pinned": true,
        "creator": "Jules Fullstack"
    }
    """
    And I should have 1 notification "NewsCreatedNotification" with data:
      | key   | value                                                                         |
      | topic | staging_jemarche_department_92                                                |
      | title | Une nouvelle actualité d'aujourd'hui                                          |
      | body  | **Vestibulum** et lectus vehicula. *Sed* eget neque nec dolor gravida luctus. |
      Examples:
        | user                                | scope                                          |
        | je-mengage-user-1@en-marche-dev.fr  | correspondent                                  |
        | laura@deloche.com                   | delegated_2c6134f7-4312-45c4-9ab7-89f2b0731f86 |

  Scenario Outline: As a (delegated) correspondent I can update a news
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/jecoute/news/<news_uuid>?scope=<scope>" with body:
    """
    {
      "title": "Nouveau titre",
      "text": "Nouveau texte",
      "external_link": "http://new.correspondent.en-marche.fr",
      "link_label": "Voir",
      "notification": false,
      "enriched": false,
      "pinned": false,
      "published": false
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "uuid": "<news_uuid>",
        "title":  "Nouveau titre",
        "text": "Nouveau texte",
        "external_link": "http://new.correspondent.en-marche.fr",
        "link_label": "Voir",
        "visibility": "local",
        "zone": {
            "code": "92",
            "created_at": "2020-12-04T15:24:38+01:00",
            "name": "Hauts-de-Seine",
            "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
        },
        "created_at": "@string@.isDateTime()",
        "notification": false,
        "published": false,
        "enriched": false,
        "pinned": false,
        "creator": "<user_name>"
    }
    """
    Examples:
      | user                                | user_name       | news_uuid                             | scope                                          |
      | je-mengage-user-1@en-marche-dev.fr  | Jules Fullstack | b09185ba-f271-404b-a73f-76d92ca8c120  | correspondent                                  |
      | laura@deloche.com                   | Laura Deloche   | 6101c6a6-f7ef-4952-95db-8553952d656d  | delegated_2c6134f7-4312-45c4-9ab7-89f2b0731f86 |

  Scenario Outline: As a (delegated) legislative candidate I can create a news
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    Then I should have 0 notification
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/jecoute/news?scope=<scope>" with body:
    """
    {
      "title": "Une nouvelle actualité d'un candidat aux législatives",
      "text": "**Duis ut elit** vel felis mattis pretium. Curabitur ut dui elementum, mollis ante non, dictum magna.",
      "external_link": "https://candidat-aux-legislatives.en-marche.fr",
      "link_label": "Voir",
      "notification": true,
      "published": true,
      "enriched": true,
      "pinned": true,
      "zone": "e3f0bf9d-906e-11eb-a875-0242ac150002"
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
        "uuid": "@uuid@",
        "title":  "Une nouvelle actualité d'un candidat aux législatives",
        "text": "**Duis ut elit** vel felis mattis pretium. Curabitur ut dui elementum, mollis ante non, dictum magna.",
        "external_link": "https://candidat-aux-legislatives.en-marche.fr",
        "link_label": "Voir",
        "visibility": "local",
        "zone": {
            "code": "75-1",
            "created_at": "2020-12-04T15:24:38+01:00",
            "name": "Paris (1)",
            "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002"
        },
        "created_at": "@string@.isDateTime()",
        "notification": true,
        "published": true,
        "enriched": true,
        "pinned": true,
        "creator": "Jean-Baptiste Fortin"
    }
    """
    And I should have 1 notification "NewsCreatedNotification" with data:
      | key   | value                                                                                                 |
      | topic | staging_jemarche_department_75                                                                        |
      | title | Une nouvelle actualité d'un candidat aux législatives                                                 |
      | body  | **Duis ut elit** vel felis mattis pretium. Curabitur ut dui elementum, mollis ante non, dictum magna. |
    Examples:
      | user                                    | scope                                           |
      | senatorial-candidate@en-marche-dev.fr   | legislative_candidate                           |
      | gisele-berthoux@caramail.com            | delegated_b24fea43-ecd8-4bf4-b500-6f97886ab77c  |

  Scenario Outline: As a (delegated) legislative candidate I can update a news
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/jecoute/news/<news_uuid>?scope=<scope>" with body:
    """
    {
      "title": "Nouveau titre",
      "text": "Nouveau texte",
      "external_link": "http://new.en-marche.fr",
      "link_label": "Voir",
      "notification": false,
      "enriched": false,
      "pinned": true,
      "published": false
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "uuid": "<news_uuid>",
        "title":  "Nouveau titre",
        "text": "Nouveau texte",
        "external_link": "http://new.en-marche.fr",
        "link_label": "Voir",
        "visibility": "local",
        "zone": {
            "code": "75-1",
            "created_at": "2020-12-04T15:24:38+01:00",
            "name": "Paris (1)",
            "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002"
        },
        "created_at": "@string@.isDateTime()",
        "notification": false,
        "published": false,
        "enriched": false,
        "pinned": true,
        "creator": "<user_name>"
    }
    """
    Examples:
      | user                                  | user_name             | news_uuid                             | scope                                           |
      | senatorial-candidate@en-marche-dev.fr | Jean-Baptiste Fortin  | 2c28b246-b17e-409d-992a-b8a57481fb7a  | legislative_candidate                           |
      | gisele-berthoux@caramail.com          | Gisele Berthoux       | 4f5db386-1819-4055-abbd-fb5d840cd6c0  | delegated_b24fea43-ecd8-4bf4-b500-6f97886ab77c  |

  Scenario Outline: As a (delegated) referent I can get a news
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/jecoute/news/6c70f8e8-6bce-4376-8b9e-3ce342880673?scope=<scope>&page_size=10"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "uuid": "6c70f8e8-6bce-4376-8b9e-3ce342880673",
        "title": "[Référent] Nouvelle actualité non publiée à 59 du référent délégué",
        "text": "Fusce lacinia, diam et sodales iaculis, velit ante mollis ex, eu commodo felis lectus eu dui.",
        "external_link": "https://referent.en-marche.fr",
        "link_label": "Voir le lien",
        "created_at": "@string@.isDateTime()",
        "visibility": "local",
        "zone": {
            "code": "59",
            "created_at": "@string@.isDateTime()",
            "name": "Nord",
            "uuid": "e3eff020-906e-11eb-a875-0242ac150002"
        },
        "notification": true,
        "enriched": false,
        "pinned": false,
        "published": false,
        "creator": "Bob Senateur (59)"
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a logged-in user I can update a news of my zone
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/jecoute/news/6c70f8e8-6bce-4376-8b9e-3ce342880673?scope=<scope>" with body:
    """
    {
      "title": "[Référent] Nouveau titre",
      "text": "Nouveau texte",
      "external_link": "https://nouveau.en-marche.fr",
      "link_label": "Voir le lien (nouveau)",
      "notification": false,
      "enriched": false,
      "pinned": false,
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
      "link_label": "Voir le lien (nouveau)",
      "visibility": "local",
      "zone": {
          "code": "59",
          "created_at": "@string@.isDateTime()",
          "name": "Nord",
          "uuid": "e3eff020-906e-11eb-a875-0242ac150002"
      },
      "created_at": "@string@.isDateTime()",
      "notification": false,
      "enriched": false,
      "pinned": false,
      "published": false,
      "creator": "Bob Senateur (59)"
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

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
      "text": "**Nulla dapibus** ornare elementum. *Curabitur* volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula.",
      "external_link": "http://test.en-marche.fr",
      "global": true,
      "notification": true,
      "enriched": true,
      "pinned": true,
      "published": true
    }
    """
    Then the response status code should be 403

  Scenario: As a referent I cannot create a local news without a zone
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/jecoute/news?scope=referent" with body:
    """
    {
      "title": "Une nouvelle actualité d'aujourd'hui",
      "text": "Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula.",
      "external_link": "http://test.en-marche.fr",
      "link_label": "Voir",
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
        "detail": "zone: Veuillez spécifier une zone.",
        "violations": [
            {
                "propertyPath": "zone",
                "message": "Veuillez spécifier une zone."
            }
        ]
    }
    """

  Scenario: As a referent I cannot create a local news with a city zone
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/jecoute/news?scope=referent" with body:
    """
    {
      "title": "Une nouvelle actualité d'aujourd'hui",
      "text": "Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula.",
      "external_link": "http://test.en-marche.fr",
      "link_label": "Voir",
      "zone": "e3f21338-906e-11eb-a875-0242ac150002",
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

  Scenario: As a referent I cannot create a local news with a non managed zone
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/jecoute/news?scope=referent" with body:
    """
    {
      "title": "Une nouvelle actualité d'aujourd'hui",
      "text": "Nulla dapibus ornare elementum. Curabitur volutpat erat justo, et facilisis eros finibus. Sed eget neque nec dolor gravida luctus. Vestibulum et lectus vehicula.",
      "external_link": "http://test.en-marche.fr",
      "link_label": "Voir",
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
      "detail": "zone: La zone spécifiée n'est pas gérée par votre rôle.",
      "violations": [
        {
          "propertyPath": "zone",
          "message": "La zone spécifiée n'est pas gérée par votre rôle."
        }
      ]
    }
    """
