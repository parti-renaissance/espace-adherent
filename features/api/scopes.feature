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
            "uuid": "94415793-872a-11eb-9419-42010a840019",
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
            "uuid": "943fb3ac-872a-11eb-9419-42010a840019",
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
            "uuid": "943fb3ac-872a-11eb-9419-42010a840019",
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
            "uuid": "943fb3ac-872a-11eb-9419-42010a840019",
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
            "uuid": "943fb3ac-872a-11eb-9419-42010a840019",
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
          "uuid": "94415793-872a-11eb-9419-42010a840019",
          "code": "75-1",
          "type": "district",
          "name": "Paris (1)",
          "latitude": null,
          "longitude": null
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
        "survey",
        "elected_representative",
        "adherent_formations",
        "general_meeting_reports",
        "documents"
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
          "uuid": "943fb3ac-872a-11eb-9419-42010a840019",
          "latitude": null,
          "longitude": null
        }
      ],
      "apps": [
        "data_corner"
      ],
      "features": [
        "dashboard",
        "contacts",
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
        "survey",
        "adherent_formations",
        "committee",
        "general_meeting_reports",
        "documents",
        "designation"
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
            "uuid": "943fda85-872a-11eb-9419-42010a840019",
            "code": "CIRCO_FDE-06",
            "name": "Suisse"
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
          "uuid": "9440518c-872a-11eb-9419-42010a840019",
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
            "uuid": "944157f5-872a-11eb-9419-42010a840019",
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
            "uuid": "9440131e-872a-11eb-9419-42010a840019",
            "code": "11",
            "name": "Île-de-France"
          }
        ],
        "apps": ["data_corner"]
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
            "uuid": "94403ac2-872a-11eb-9419-42010a840019"
          },
          {
            "code": "59",
            "name": "Nord",
            "uuid": "9440518c-872a-11eb-9419-42010a840019"
          },
          {
            "code": "76",
            "name": "Seine-Maritime",
            "uuid": "94405a38-872a-11eb-9419-42010a840019"
          },
          {
            "code": "77",
            "name": "Seine-et-Marne",
            "uuid": "94405a9c-872a-11eb-9419-42010a840019"
          },
          {
            "code": "92",
            "name": "Hauts-de-Seine",
            "uuid": "944062a9-872a-11eb-9419-42010a840019"
          },
          {
            "code": "ES",
            "name": "Espagne",
            "uuid": "943fb07b-872a-11eb-9419-42010a840019"
          },
          {
            "code": "CH",
            "name": "Suisse",
            "uuid": "943ffff9-872a-11eb-9419-42010a840019"
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
              "uuid": "944062a9-872a-11eb-9419-42010a840019"
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
                  "uuid": "94415793-872a-11eb-9419-42010a840019"
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
          "uuid": "9440518c-872a-11eb-9419-42010a840019",
          "code": "59",
          "type": "department",
          "name": "Nord",
          "latitude": null,
          "longitude": null
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
                "uuid": "94403ac2-872a-11eb-9419-42010a840019",
                "code": "13",
                "type": "department",
                "name": "Bouches-du-Rhône",
                "latitude": null,
                "longitude": null
            },
            {
                "uuid": "9440518c-872a-11eb-9419-42010a840019",
                "code": "59",
                "type": "department",
                "name": "Nord",
                "latitude": null,
                "longitude": null
            },
            {
                "uuid": "94405a38-872a-11eb-9419-42010a840019",
                "code": "76",
                "type": "department",
                "name": "Seine-Maritime",
                "latitude": null,
                "longitude": null
            },
            {
                "uuid": "94405a9c-872a-11eb-9419-42010a840019",
                "code": "77",
                "type": "department",
                "name": "Seine-et-Marne",
                "latitude": null,
                "longitude": null
            },
            {
                "uuid": "944062a9-872a-11eb-9419-42010a840019",
                "code": "92",
                "type": "department",
                "name": "Hauts-de-Seine",
                "latitude": null,
                "longitude": null
            },
            {
                "uuid": "943fb07b-872a-11eb-9419-42010a840019",
                "code": "ES",
                "type": "country",
                "name": "Espagne",
                "latitude": null,
                "longitude": null
            },
            {
                "uuid": "943ffff9-872a-11eb-9419-42010a840019",
                "code": "CH",
                "type": "country",
                "name": "Suisse",
                "latitude": null,
                "longitude": null
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
            "survey",
            "department_site",
            "elected_representative",
            "adherent_formations",
            "committee",
            "general_meeting_reports",
            "documents",
            "designation"
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
