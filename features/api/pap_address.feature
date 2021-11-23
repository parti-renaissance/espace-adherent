@api
Feature:
  In order to to complete PAP campaigns
  I should be able to retrieve addresses for a given position and additional datas

  Scenario Outline: As an anonymous I can not get address and voters information
    When I send a "GET" request to "<url>"
    Then the response status code should be 401
    Examples:
    | url                                                                         |
    | /api/v3/pap/address/near?latitude=48.879001640&&longitude=2.3187434&zoom=15 |
    | /api/v3/pap/address/a0b9231b-9ff5-49b9-aa7a-1d28abbba32f                    |
    | /api/v3/pap/address/a0b9231b-9ff5-49b9-aa7a-1d28abbba32f/voters             |
    | /api/v3/pap/buildings/faf30370-80c5-4a46-8c31-f6a361bfa23b/building_blocks  |

  Scenario Outline: As a logged-in user I can retrieve addresses near a given position ordered by distance
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I send a "GET" request to "/api/v3/pap/address/near?latitude=<latitude>&longitude=<longitude>&zoom=16"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    <addresses>
    """
    Examples:
      | latitude     | longitude | addresses |
      # 68 rue du rocher, Paris 8ème => 65, 70, 67, 55 rue du rocher
      | 48.879001640 | 2.3187434 | [{"uuid": "ccfd846a-5439-42ad-85ce-286baf4e7269"}, {"uuid": "04e1d76f-c727-4612-afab-2dec2d71a480"}, {"uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"}, {"uuid": "a0b9231b-9ff5-49b9-aa7a-1d28abbba32f"}] |
      # 54 rue du rocher, Paris 8ème => 55, 65, 70, 67 rue du rocher
      | 48.877018    | 2.32154   | [{"uuid": "a0b9231b-9ff5-49b9-aa7a-1d28abbba32f"}, {"uuid": "ccfd846a-5439-42ad-85ce-286baf4e7269"}, {"uuid": "04e1d76f-c727-4612-afab-2dec2d71a480"}, {"uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"}] |

  Scenario: As a logged-in user I can retrieve latitude & longitude of addresses near a given position
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMarche App" with scope "jemarche_app"
    # 62 rue du rocher, Paris 8ème
    When I send a "GET" request to "/api/v3/pap/address/near?latitude=48.877018&longitude=2.32154&zoom=16"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    # 55, 65, 70, 67 rue du rocher, Paris 8ème
    """
    [
      {
        "uuid": "a0b9231b-9ff5-49b9-aa7a-1d28abbba32f",
        "number": "55",
        "address": "Rue du Rocher",
        "insee_code": "75108",
        "city_name": "Paris 8ème",
        "latitude": 48.878708,
        "longitude": 2.319111,
        "building": {
          "uuid": "2fbe7b02-944d-4abd-be3d-f9b2944917a9",
          "building_blocks": [
            {
              "uuid": "@uuid@",
              "name": "Bâtiment A",
              "floors": [
                {
                  "number": 0,
                  "uuid": "@uuid@"
                },
                {
                  "number": 1,
                  "uuid": "@uuid@"
                },
                {
                  "number": 2,
                  "uuid": "@uuid@"
                }
              ]
            }
          ]
        }
      },
      {
        "uuid": "ccfd846a-5439-42ad-85ce-286baf4e7269",
        "number": "65",
        "address": "Rue du Rocher",
        "insee_code": "75108",
        "city_name": "Paris 8ème",
        "latitude": 48.879078,
        "longitude": 2.318631,
        "building": {
          "uuid": "faf30370-80c5-4a46-8c31-f6a361bfa23b",
          "building_blocks": [
            {
              "uuid": "@uuid@",
              "name": "Bâtiment A",
              "floors": [
                {
                  "number": 0,
                  "uuid": "@uuid@"
                },
                {
                  "number": 1,
                  "uuid": "@uuid@"
                }
              ]
            },
            {
              "uuid": "@uuid@",
              "name": "Bâtiment B",
              "floors": [
                {
                  "number": 0,
                  "uuid": "@uuid@"
                },
                {
                  "number": 1,
                  "uuid": "@uuid@"
                }
              ]
            }
          ]
        }
      },
      {
        "uuid": "04e1d76f-c727-4612-afab-2dec2d71a480",
        "number": "70",
        "address": "Rue du Rocher",
        "insee_code": "75108",
        "city_name": "Paris 8ème",
        "latitude": 48.879166,
        "longitude": 2.318761,
        "building": {
          "uuid": "0b81ff3d-f895-4e3f-bf6d-ff2a659c1c6f",
          "building_blocks": [
            {
              "uuid": "@uuid@",
              "name": "Bâtiment A",
              "floors": [
                {
                  "number": 0,
                  "uuid": "@uuid@"
                }
              ]
            }
          ]
        }
      },
      {
        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2",
        "number": "67",
        "address": "Rue du Rocher",
        "insee_code": "75108",
        "city_name": "Paris 8ème",
        "latitude": 48.879246,
        "longitude": 2.318427,
        "building": {
          "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd",
          "building_blocks": [
            {
              "uuid": "@uuid@",
              "name": "Bâtiment A",
              "floors": [
                {
                  "number": 0,
                  "uuid": "@uuid@"
                },
                {
                  "number": 1,
                  "uuid": "@uuid@"
                },
                {
                  "number": 10,
                  "uuid": "@uuid@"
                },
                {
                  "number": 2,
                  "uuid": "@uuid@"
                },
                {
                  "number": 3,
                  "uuid": "@uuid@"
                },
                {
                  "number": 4,
                  "uuid": "@uuid@"
                },
                {
                  "number": 5,
                  "uuid": "@uuid@"
                },
                {
                  "number": 6,
                  "uuid": "@uuid@"
                },
                {
                  "number": 7,
                  "uuid": "@uuid@"
                },
                {
                  "number": 8,
                  "uuid": "@uuid@"
                },
                {
                  "number": 9,
                  "uuid": "@uuid@"
                }
              ]
            }
          ]
        }
      }
    ]
    """

  Scenario: As a logged-in user I can retrieve full address information for a given address identifier
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I send a "GET" request to "/api/v3/pap/address/a0b9231b-9ff5-49b9-aa7a-1d28abbba32f"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "uuid": "a0b9231b-9ff5-49b9-aa7a-1d28abbba32f",
      "number": "55",
      "address": "Rue du Rocher",
      "insee_code": "75108",
      "city_name": "Paris 8ème",
      "voters_count": 2
    }
    """

  Scenario: As a logged-in user I can retrieve the voter list for a given address identifier
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I send a "GET" request to "/api/v3/pap/address/a0b9231b-9ff5-49b9-aa7a-1d28abbba32f/voters"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
      {
        "uuid": "bdb9d49c-20f5-44c0-bc4a-d8b75f85ee95",
        "first_name": "J.",
        "last_name": "Doe",
        "gender": "male",
        "birthdate": "@string@.isDateTime()",
        "vote_place": "75108_0001"
      },
      {
        "uuid": "0cf560f0-c5ec-43ef-9ea1-b6fd2a2dc339",
        "first_name": "J.",
        "last_name": "Doe",
        "gender": "female",
        "birthdate": "@string@.isDateTime()",
        "vote_place": "75108_0001"
      }
    ]
    """

  Scenario: As a logged-in user I can retrieve the building block list for a given building identifier
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I send a "GET" request to "/api/v3/pap/buildings/faf30370-80c5-4a46-8c31-f6a361bfa23b/building_blocks"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
        {
            "uuid": "@uuid@",
            "name": "Bâtiment A",
            "floors": [
                {
                    "uuid": "@uuid@",
                    "number": 0
                },
                {
                    "uuid": "@uuid@",
                    "number": 1
                }
            ]
        },
        {
            "uuid": "@uuid@",
            "name": "Bâtiment B",
            "floors": [
                {
                    "uuid": "@uuid@",
                    "number": 0
                },
                {
                    "uuid": "@uuid@",
                    "number": 1
                }
            ]
        }
    ]
    """

  Scenario: As a logged-in user I can retrieve the building block list for a given building identifier for a PAP campaign
    Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I send a "GET" request to "/api/v3/pap/buildings/faf30370-80c5-4a46-8c31-f6a361bfa23b/building_blocks?campaign_uuid=d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
        {
            "uuid": "@uuid@",
            "name": "Bâtiment A",
            "status": "completed",
            "floors": [
                {
                    "uuid": "@uuid@",
                    "number": 0,
                    "status": "completed"
                },
                {
                    "uuid": "@uuid@",
                    "number": 1,
                    "status": "completed"
                }
            ]
        },
        {
            "uuid": "@uuid@",
            "name": "Bâtiment B",
            "status": "ongoing",
            "floors": [
                {
                    "uuid": "@uuid@",
                    "number": 0,
                    "status": "ongoing"
                },
                {
                    "uuid": "@uuid@",
                    "number": 1,
                    "status": "ongoing"
                }
            ]
        }
    ]
    """
