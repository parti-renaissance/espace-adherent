@api
Feature:
  In order to get scopes of an adherent
  I should be able to request them via the API

  Scenario:
    When I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/profile/me/scopes"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
      {
        "code": "deputy",
        "name": "Député",
        "zones": [
          {
            "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
            "code": "75-1",
            "name": "Paris (1)"
          }
        ],
        "apps": [
          "data_corner"
        ]
      },
      {
        "code": "national_communication",
        "name": "National communication",
        "zones": [
          {
            "uuid": "e3ef8883-906e-11eb-a875-0242ac150002",
            "code": "FR",
            "name": "France"
          }
        ],
        "apps": [
          "data_corner"
        ]
      },
      {
        "code": "national",
        "name": "National",
        "zones": [
          {
            "uuid": "e3ef8883-906e-11eb-a875-0242ac150002",
            "code": "FR",
            "name": "France"
          }
        ],
        "apps": [
          "data_corner"
        ]
      },
      {
        "code": "pap_national_manager",
        "name": "Responsable National PAP",
        "zones": [
          {
            "uuid": "e3ef8883-906e-11eb-a875-0242ac150002",
            "code": "FR",
            "name": "France"
          }
        ],
        "apps": [
          "data_corner"
        ]
      },
      {
        "code": "pap",
        "name": "Porte-à-porteur",
        "zones": [],
        "apps": [
          "jemarche"
        ]
      },
      {
        "code": "phoning_national_manager",
        "name": "Responsable Phoning",
        "zones": [
          {
            "uuid": "e3ef8883-906e-11eb-a875-0242ac150002",
            "code": "FR",
            "name": "France"
          }
        ],
        "apps": [
          "data_corner"
        ]
      },
      {
        "code": "phoning",
        "name": "Appelant",
        "zones": [],
        "apps": [
          "jemarche"
        ]
      }
    ]
    """

  Scenario:
    When I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/profile/me/scope/deputy"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "code": "deputy",
      "name": "Député",
      "zones": [
        {
          "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
          "code": "75-1",
          "type": "district",
          "name": "Paris (1)"
        }
      ],
      "apps": [
        "data_corner"
      ],
      "features": [
        "dashboard",
        "contacts",
        "messages",
        "events",
        "mobile_app",
        "elections",
        "ripostes",
        "survey"
      ],
      "delegated_access": null
    }
    """

  Scenario:
    When I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/profile/me/scope/national"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "code": "national",
      "name": "National",
      "zones": [
        {
          "code": "FR",
          "type": "country",
          "name": "France",
          "uuid": "e3ef8883-906e-11eb-a875-0242ac150002"
        }
      ],
      "apps": [
        "data_corner"
      ],
      "features": [
        "dashboard",
        "contacts",
        "messages",
        "events",
        "my_team",
        "mobile_app",
        "news",
        "elections",
        "ripostes",
        "pap",
        "pap_v2",
        "team",
        "phoning_campaign",
        "survey"
      ],
      "delegated_access": null
    }
    """

  Scenario:
    When I am logged with "gisele-berthoux@caramail.com" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/profile/me/scopes"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
      {
        "code": "delegated_96076afb-2243-4251-97fe-8201d50c3256",
        "name": "Député délégué",
        "zones": [
          {
            "uuid": "e3efabd2-906e-11eb-a875-0242ac150002",
            "code": "LI",
            "name": "Liechtenstein"
          }
        ],
        "apps": [
          "data_corner"
        ]
      },
      {
        "code": "delegated_411faa64-202d-4ff2-91ce-c98b29af28ef",
        "name": "Sénateur délégué",
        "zones": [
        {
          "uuid": "e3eff020-906e-11eb-a875-0242ac150002",
          "code": "59",
          "name": "Nord"
        }
        ],
        "apps": [
          "data_corner"
        ]
      },
      {
        "code": "delegated_d2315289-a3fd-419c-a3dd-3e1ff71b754d",
        "name": "Député délégué",
        "zones": [
          {
            "uuid": "e3f0bfff-906e-11eb-a875-0242ac150002",
            "code": "75-2",
            "name": "Paris (2)"
          }
        ],
        "apps": [
          "data_corner"
        ]
      },
      {
        "code": "delegated_01ddb89b-25be-4ccb-a90f-8338c42e7e58",
        "name": "Candidat délégué",
        "zones": [
          {
            "uuid": "e3efe139-906e-11eb-a875-0242ac150002",
            "code": "11",
            "name": "Île-de-France"
          }
        ],
        "apps": []
      },
      {
        "apps": [
          "data_corner"
        ],
        "code": "delegated_ef339f8e-e9d0-4f22-b98f-1a7526246cad",
        "name": "Référent délégué",
        "zones": [
          {
            "code": "13",
            "name": "Bouches-du-Rhône",
            "uuid": "e3f01553-906e-11eb-a875-0242ac150002"
          },
          {
            "code": "59",
            "name": "Nord",
            "uuid": "e3eff020-906e-11eb-a875-0242ac150002"
          },
          {
            "code": "76",
            "name": "Seine-Maritime",
            "uuid": "e3efef5d-906e-11eb-a875-0242ac150002"
          },
          {
            "code": "77",
            "name": "Seine-et-Marne",
            "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002"
          },
          {
            "code": "92",
            "name": "Hauts-de-Seine",
            "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
          },
          {
            "code": "ES",
            "name": "Espagne",
            "uuid": "e3ef84ed-906e-11eb-a875-0242ac150002"
          },
          {
            "code": "CH",
            "name": "Suisse",
            "uuid": "e3efcea1-906e-11eb-a875-0242ac150002"
          }
        ]
      },
      {
        "apps": [
          "data_corner"
        ],
        "code": "delegated_6d2506a7-bec7-45a1-a5ee-8f8b48daa5ec",
        "name": "Correspondant délégué",
        "zones": [
          {
              "code": "92",
              "name": "Hauts-de-Seine",
              "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
          }
        ]
      },
      {
          "apps": [
              "data_corner"
          ],
          "code": "delegated_b24fea43-ecd8-4bf4-b500-6f97886ab77c",
          "name": "Candidat aux législatives délégué",
          "zones": [
              {
                  "code": "75-1",
                  "name": "Paris (1)",
                  "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002"
              }
          ]
      }
    ]
    """

  Scenario:
    When I am logged with "gisele-berthoux@caramail.com" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/profile/me/scope/delegated_411faa64-202d-4ff2-91ce-c98b29af28ef"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "code": "delegated_411faa64-202d-4ff2-91ce-c98b29af28ef",
      "name": "Sénateur délégué",
      "zones": [
        {
          "uuid": "e3eff020-906e-11eb-a875-0242ac150002",
          "code": "59",
          "type": "department",
          "name": "Nord"
        }
      ],
      "apps": [
        "data_corner"
      ],
      "features": [
          "dashboard",
          "contacts",
          "messages",
          "mobile_app"
      ],
      "delegated_access": {
          "delegator": {
              "uuid": "021268fe-d4b3-44a7-bce9-c001191249a7",
              "first_name": "Bob",
              "last_name": "Senateur (59)"
          },
          "type": "senator",
          "role": "Collaborateur parlementaire"
      }
    }
    """

  Scenario:
    When I am logged with "senateur@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/profile/me/scope/delegated_08f40730-d807-4975-8773-69d8fae1da74"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "code": "delegated_08f40730-d807-4975-8773-69d8fae1da74",
        "name": "Référent délégué",
        "zones": [
            {
                "uuid": "e3f01553-906e-11eb-a875-0242ac150002",
                "code": "13",
                "type": "department",
                "name": "Bouches-du-Rhône"
            },
            {
                "uuid": "e3eff020-906e-11eb-a875-0242ac150002",
                "code": "59",
                "type": "department",
                "name": "Nord"
            },
            {
                "uuid": "e3efef5d-906e-11eb-a875-0242ac150002",
                "code": "76",
                "type": "department",
                "name": "Seine-Maritime"
            },
            {
                "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                "code": "77",
                "type": "department",
                "name": "Seine-et-Marne"
            },
            {
                "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                "code": "92",
                "type": "department",
                "name": "Hauts-de-Seine"
            },
            {
                "uuid": "e3ef84ed-906e-11eb-a875-0242ac150002",
                "code": "ES",
                "type": "country",
                "name": "Espagne"
            },
            {
                "uuid": "e3efcea1-906e-11eb-a875-0242ac150002",
                "code": "CH",
                "type": "country",
                "name": "Suisse"
            }
        ],
        "apps": [
            "data_corner"
        ],
        "features": [
            "dashboard",
            "contacts",
            "messages",
            "events",
            "mobile_app",
            "news",
            "elections",
            "ripostes",
            "pap",
            "pap_v2",
            "team",
            "phoning_campaign",
            "survey"
        ],
        "delegated_access": {
            "delegator": {
                "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                "first_name": "Referent",
                "last_name": "Referent"
            },
            "role": "Collaborateur parlementaire",
            "type": "referent"
        }
    }
    """

  Scenario:
    When I am logged with "gisele-berthoux@caramail.com" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/profile/me/scope/test"
    Then the response status code should be 403
