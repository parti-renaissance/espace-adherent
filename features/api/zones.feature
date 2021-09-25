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
    Given I add "Accept" header equal to "application/json"
    And I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
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

  Scenario: As a logged-in user I can filter zones by space and partial name with page limit
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/zone/autocomplete" with parameters:
      | key         | value     |
      | scope       | referent  |
      | q           | pa        |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
        {
            "uuid": "e3ef84ed-906e-11eb-a875-0242ac150002",
            "type": "country",
            "postal_code": [],
            "code": "ES",
            "name": "Espagne"
        },
        {
            "uuid": "e3f33ac7-906e-11eb-a875-0242ac150002",
            "type": "consular_district",
            "postal_code": [],
            "code": "CONS_030-2",
            "name": "Espagne  2ème circo"
        },
        {
            "uuid": "e3f33a66-906e-11eb-a875-0242ac150002",
            "type": "consular_district",
            "postal_code": [],
            "code": "CONS_030-1",
            "name": "Espagne 1ère circo"
        },
        {
            "uuid": "e3f29811-906e-11eb-a875-0242ac150002",
            "type": "city",
            "postal_code": [
                "77430"
            ],
            "code": "77079",
            "name": "Champagne-sur-Seine"
        },
        {
            "uuid": "e3f2a3e7-906e-11eb-a875-0242ac150002",
            "type": "city",
            "postal_code": [
                "77130"
            ],
            "code": "77210",
            "name": "La Grande-Paroisse"
        },
        {
            "uuid": "e3f1cb08-906e-11eb-a875-0242ac150002",
            "type": "city",
            "postal_code": [
                "13610"
            ],
            "code": "13080",
            "name": "Le Puy-Sainte-Réparade"
        },
        {
            "uuid": "e3f28b24-906e-11eb-a875-0242ac150002",
            "type": "city",
            "postal_code": [
                "76570"
            ],
            "code": "76495",
            "name": "Pavilly"
        },
        {
            "uuid": "e3f1e343-906e-11eb-a875-0242ac150002",
            "type": "city",
            "postal_code": [
                "13920"
            ],
            "code": "13098",
            "name": "Saint-Mitre-les-Remparts"
        },
        {
            "uuid": "e3f2b860-906e-11eb-a875-0242ac150002",
            "type": "city",
            "postal_code": [
                "77178"
            ],
            "code": "77430",
            "name": "Saint-Pathus"
        },
        {
            "uuid": "e3f2c010-906e-11eb-a875-0242ac150002",
            "type": "city",
            "postal_code": [
                "77270"
            ],
            "code": "77514",
            "name": "Villeparisis"
        },
        {
            "uuid": "e3f18a2c-906e-11eb-a875-0242ac150002",
            "type": "city_community",
            "postal_code": [],
            "code": "200090504",
            "name": "CA Coulommiers Pays de Brie"
        },
        {
            "uuid": "e3f18848-906e-11eb-a875-0242ac150002",
            "type": "city_community",
            "postal_code": [],
            "code": "200072346",
            "name": "CA du Pays de Fontainebleau"
        },
        {
            "uuid": "e3f187e7-906e-11eb-a875-0242ac150002",
            "type": "city_community",
            "postal_code": [],
            "code": "200072130",
            "name": "CA du Pays de Meaux"
        },
        {
            "uuid": "e3f1828a-906e-11eb-a875-0242ac150002",
            "type": "city_community",
            "postal_code": [],
            "code": "200059228",
            "name": "CA Grand Paris Sud Seine Essonne Sénart"
        },
        {
            "uuid": "e3f18139-906e-11eb-a875-0242ac150002",
            "type": "city_community",
            "postal_code": [],
            "code": "200057958",
            "name": "CA Paris - Vallée de la Marne"
        },
        {
            "uuid": "e3f180d7-906e-11eb-a875-0242ac150002",
            "type": "city_community",
            "postal_code": [],
            "code": "200055655",
            "name": "CA Roissy Pays de France"
        },
        {
            "uuid": "e3f19169-906e-11eb-a875-0242ac150002",
            "type": "city_community",
            "postal_code": [],
            "code": "247600505",
            "name": "CC Campagne-de-Caux"
        },
        {
            "uuid": "e3f197ca-906e-11eb-a875-0242ac150002",
            "type": "city_community",
            "postal_code": [],
            "code": "247700065",
            "name": "CC du Pays de l'Ourcq"
        },
        {
            "uuid": "e3f17e32-906e-11eb-a875-0242ac150002",
            "type": "city_community",
            "postal_code": [],
            "code": "200043321",
            "name": "CC du Pays de Mormal"
        },
        {
            "uuid": "e3f18d97-906e-11eb-a875-0242ac150002",
            "type": "city_community",
            "postal_code": [],
            "code": "245901038",
            "name": "CC du Pays Solesmois"
        },
        {
            "uuid": "e3f15390-906e-11eb-a875-0242ac150002",
            "type": "canton",
            "postal_code": [],
            "code": "7723",
            "name": "Villeparisis"
        }
    ]
    """

  Scenario: As a logged-in user I can filter zones by space and partial name
    Given I add "Accept" header equal to "application/json"
    And I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
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

