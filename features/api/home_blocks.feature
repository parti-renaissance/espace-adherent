@api
Feature:
  In order to get home blocks
  As a software developer
  I should be able to access homme blocks API

  Scenario: As a non logged-in user I can get the list of home blocks ordered by display position
    When I send a "GET" request to "api/homeblocks"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    [
      {
        "title": "Bandeau PR Ensemble nous réussirons",
        "subtitle": "",
        "type": "article",
        "media": {
          "type": "image",
          "mime_type": "image/jpeg",
          "path": "http://test.enmarche.code/assets/images/bandeau-PR-Ensemble-nous-reussirons.jpeg"
        },
        "link": "http://enmarche.code",
        "display_filter": true,
        "display_titles": false,
        "display_block": true,
        "video_controls": false,
        "video_autoplay_loop": true,
        "title_cta": null,
        "color_cta": null,
        "bg_color": null
      }
    ]
    """

