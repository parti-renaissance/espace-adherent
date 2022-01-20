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
        "ripostes"
      ]
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
        "phoning",
        "pap",
        "team",
        "phoning_campaign",
        "survey"
      ]
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
          "name": "Nord"
        }
      ],
      "apps": [
        "data_corner"
      ],
      "features": [
        "dashboard",
        "contacts",
        "messages"
      ]
    }
    """

  Scenario:
    When I am logged with "gisele-berthoux@caramail.com" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/profile/me/scope/test"
    Then the response status code should be 403
    And the response should be in JSON
    And the JSON node "detail" should be equal to "User has no required scope."
