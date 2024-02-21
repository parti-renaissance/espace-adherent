@api
@renaissance
Feature:
  In order to get all resource links
  I should be able to access API of resource links

  Scenario: As a non authenticated user I cannot get the resource links list
    When I send a "GET" request to "/api/v3/jecoute/resource-links"
    Then the response status code should be 401

  Scenario: As a logged in user I can get the resource links list
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
    When I send a "GET" request to "/api/v3/jecoute/resource-links"
    Then the response status code should be 200
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
          "label": "On l'a dit, On le fait",
          "url": "https://transformer.en-marche.fr",
          "uuid": "94bc6088-ff8f-4d07-a216-6eebd12f317c",
          "image_url": "http://test.renaissance.code/assets/images/jecoute/resources/bb4bb38ba549eea93700225f7be0fbd7.png"
        },
        {
          "label": "Ce qui a changé près de chez vous",
          "url": "https://chezvous.en-marche.fr",
          "uuid": "8ddd92d7-fc9e-43c0-8d03-57eccdce9547",
          "image_url": "http://test.renaissance.code/assets/images/jecoute/resources/caff9632c5627323baee674dcde434fb.png"
        }
      ]
    }
    """
