@api
Feature:
  In order to see zones
  As a non logged-in user
  I should be able to access API zones

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                |
      | LoadClientData                  |
      | LoadDistrictData                |
      | LoadReferentTagData             |
      | LoadGeoZoneData                 |
      | LoadReferentTagsZonesLinksData  |

  Scenario: As a non logged-in user I can filter zones by exact types and partial name
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/zones" with parameters:
      | key    | value     |
      | type[] | country   |
      | type[] | city      |
      | name   | Bois-Colo |
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
          "uuid": "@uuid@",
          "type": "city",
          "postal_code": [
            "92270"
          ],
          "code": "92009",
          "name": "Bois-Colombes"
        }
      ]
    }
    """

    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/zones" with parameters:
      | key    | value   |
      | type[] | country |
      | type[] | city    |
      | name   | Allema  |
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
          "uuid": "@uuid@",
          "type": "country",
          "postal_code": [],
          "code": "DE",
          "name": "Allemagne"
        }
      ]
    }
    """

  Scenario: As a logged-in user I can filter zones by space and partial name with page limit
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/zone/autocompletion" with parameters:
      | key         | value     |
      | space_type  | referent  |
      | q           | pa        |
      # limit by zone type
      | page_limit  | 3         |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "results":[
          {
             "text":"Pays",
             "children":[
                {
                   "id":72,
                   "text":"Espagne ES"
                }
             ]
          },
          {
             "text":"Circonscription consulaire",
             "children":[
                {
                   "id":2162,
                   "text":"Espagne  2\u00e8me circo CONS_030-2"
                },
                {
                   "id":2161,
                   "text":"Espagne 1\u00e8re circo CONS_030-1"
                }
             ]
          },
          {
             "text":"Ville",
             "children":[
                {
                   "id":1850,
                   "text":"Champagne-sur-Seine 77079"
                },
                {
                   "id":1881,
                   "text":"La Grande-Paroisse 77210"
                },
                {
                   "id":1427,
                   "text":"Le Puy-Sainte-R\u00e9parade 13080"
                }
             ]
          },
          {
             "text":"Communaut\u00e9 de commune",
             "children":[
                {
                   "id":1281,
                   "text":"CA Coulommiers Pays de Brie 200090504"
                },
                {
                   "id":1276,
                   "text":"CA du Pays de Fontainebleau 200072346"
                },
                {
                   "id":1275,
                   "text":"CA du Pays de Meaux 200072130"
                }
             ]
          },
          {
             "text":"Canton",
             "children":[
                {
                   "id":1143,
                   "text":"Villeparisis 7723"
                }
             ]
          }
       ],
       "pagination":{
          "more":false
       }
    }
    """

  Scenario: As a logged-in user I can filter zones by space and partial name
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/zone/autocompletion" with parameters:
      | key         | value     |
      | space_type  | deputy    |
      | q           | pa        |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "results":[
          {
             "text":"Circonscription \u00e9lectorale",
             "children":[
                {
                   "id":776,
                   "text":"Paris (1) 75-1"
                }
             ]
          }
       ],
       "pagination":{
          "more":false
       }
    }
    """

