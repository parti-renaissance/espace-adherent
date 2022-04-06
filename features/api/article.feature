@api
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
    When I send a "GET" request to "/api/articles"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 153,
        "items_per_page": 2,
        "count": 2,
        "current_page": 1,
        "last_page": 77
      },
      "items": [
        {
          "category": {
            "name": "Médias",
            "slug": "medias"
          },
          "published_at": "@string@.isDateTime()",
          "media": {
            "type": "image",
            "path": "http://test.enmarche.code/assets/images/article.jpg"
          },
          "slug": "in-qui-aliquam-aperiam-provident-necessitatibus-quo",
          "title": "In qui aliquam aperiam provident necessitatibus quo."
        },
        {
          "category": {
            "name": "Communiqués",
            "slug": "communiques"
          },
          "published_at": "@string@.isDateTime()",
          "media": {
            "type": "image",
            "path": "http://test.enmarche.code/assets/images/article.jpg"
          },
          "slug": "aut-facilis-quis-ex-voluptates-corporis-odit-aliquam",
          "title": "Aut facilis quis ex voluptates corporis odit aliquam."
        }
      ]
    }
    """

  Scenario: As a non logged-in user I cat display an article
    When I send a "GET" request to "/api/articles/in-qui-aliquam-aperiam-provident-necessitatibus-quo"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "category": {
        "name": "Médias",
        "slug": "medias"
      },
      "published_at": "@string@.isDateTime()",
      "media": {
        "type": "image",
        "path": "http://test.enmarche.code/assets/images/article.jpg"
      },
      "slug": "in-qui-aliquam-aperiam-provident-necessitatibus-quo",
      "title": "In qui aliquam aperiam provident necessitatibus quo.",
      "description": "Iste labore quas sit corrupti. Repudiandae facere numquam molestiae repellat. Accusantium necessitatibus asperiores laborum repellendus et ullam. Laudantium corrupti nostrum hic ea nihil qui velit.",
      "twitter_description": null,
      "content": "@*@"
    }
    """

  Scenario: As a non logged-in user I cannot get a non-existent article
    When I send a "GET" request to "/api/articles/in-qui-aliquam-aperiam-provident-necessitatibus"
    Then the response status code should be 404
    And the JSON node "detail" should be equal to "Article with slug in-qui-aliquam-aperiam-provident-necessitatibus not found."



