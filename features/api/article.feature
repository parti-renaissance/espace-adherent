@api
@renaissance
Feature:
  In order to display articles on legislative websites
  As a software developer
  I should be able to access articles API

  Scenario: As a non logged-in user I can retrieve article categories list
    When I send a "GET" request to "/api/article_categories"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
      {
        "name": "Actualités",
        "slug": "actualites"
      },
      {
        "name": "Vidéos",
        "slug": "videos"
      },
      {
        "name": "Discours",
        "slug": "discours"
      },
      {
        "name": "Médias",
        "slug": "medias"
      },
      {
        "name": "Communiqués",
        "slug": "communiques"
      },
      {
        "name": "Opinions",
        "slug": "opinions"
      }
    ]
    """

  Scenario: As a non logged-in user I can get the list of published articles
    When I send a "GET" request to "/api/articles?title=Les outre-me"
    Then the response status code should be 200
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
          "category": {
            "name": "Actualités",
            "slug": "actualites"
          },
          "published_at": "@string@.isDateTime()",
          "media": {
            "type": "image",
            "path": "http://test.enmarche.code/assets/images/article.jpg"
          },
          "slug": "outre-mer",
          "title": "« Les outre-mer sont l’un des piliers de notre richesse culturelle. »"
        },
        {
          "category": {
            "name": "Actualités",
            "slug": "actualites"
          },
          "published_at": "@string@.isDateTime()",
          "media": {
            "type": "image",
            "path": "http://test.enmarche.code/assets/images/article.jpg"
          },
          "slug": "outre-mer-2",
          "title": "« Deuxième actualité: Les outre-mer sont l’un des piliers de notre richesse culturelle. »"
        }
      ]
    }
    """

  Scenario: As a non logged-in user I cat display an article
    When I send a "GET" request to "/api/articles/outre-mer"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "category": {
        "name": "Actualités",
        "slug": "actualites"
      },
      "published_at": "@string@.isDateTime()",
      "media": {
        "type": "image",
        "path": "http://test.enmarche.code/assets/images/article.jpg"
      },
      "slug": "outre-mer",
      "title": "« Les outre-mer sont l’un des piliers de notre richesse culturelle. »",
      "description": "outre-mer",
      "twitter_description": null,
      "content": "@*@"
    }
    """

  Scenario: As a non logged-in user I cannot get a non-existent article
    When I send a "GET" request to "/api/articles/in-qui-aliquam-aperiam-provident-necessitatibus"
    Then the response status code should be 404
    And the JSON node "detail" should be equal to "Not Found"
