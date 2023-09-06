@api
Feature:
  In order to manage adherent mandates
  As a logged-in user
  I should be able to access adherent mandates API

  Scenario Outline: As a user granted with local scope, I can get adherent mandates of an adherent
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    And I send a "GET" request to "/api/v3/adherents/d0a0935f-da7c-4caa-b582-a8c2376e5158/elect?scope=<scope>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "mandates": ["depute_europeen"],
        "contribution_status": "eligible",
        "contributed_at": "@string@.isDateTime()",
        "payments": [
            {
                "date": "@string@.isDateTime()",
                "method": "IBAN",
                "amount": 50,
                "uuid": "@uuid@",
                "status_label": "Paiement validé"
            },
            {
                "date": "@string@.isDateTime()",
                "method": "IBAN",
                "amount": 50,
                "uuid": "@uuid@",
                "status_label": "Paiement validé"
            }
        ],
        "uuid": "d0a0935f-da7c-4caa-b582-a8c2376e5158",
        "elect_mandates": [
            {
                "mandate_type": "senateur",
                "delegation": "Sénatrice",
                "zone": {
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                    "code": "92",
                    "name": "Hauts-de-Seine"
                },
                "begin_at": "@string@.isDateTime()",
                "finish_at": null,
                "uuid": "@uuid@"
            }
        ]
    }
    """
    Examples:
        | user                      | scope                                          |
        | referent@en-marche-dev.fr | referent                                       |
        | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can update an adherent mandate
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    And I send a "PUT" request to "/api/v3/elected_adherent_mandates/d91df367-14df-474d-ac9a-8e2176657f71?scope=<scope>" with body:
    """
    {
      "delegation": "Lorem ipsum 2",
      "finish_at": "2023-07-18"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "mandate_type": "senateur",
      "delegation": "Lorem ipsum 2",
      "zone": {
          "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
          "code": "92",
          "name": "Hauts-de-Seine"
      },
      "uuid": "d91df367-14df-474d-ac9a-8e2176657f71",
      "adherent": {"uuid": "@uuid@"},
      "begin_at": "2019-01-11T00:00:00+01:00",
      "finish_at": "2023-07-18T00:00:00+02:00"
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can delete an adherent mandate
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    And I send a "DELETE" request to "/api/v3/elected_adherent_mandates/d91df367-14df-474d-ac9a-8e2176657f71?scope=<scope>"
    Then the response status code should be 204
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can create an adherent mandate
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    And I send a "POST" request to "/api/v3/elected_adherent_mandates?scope=<scope>" with body:
    """
    {
      "adherent": "d0a0935f-da7c-4caa-b582-a8c2376e5158",
      "zone": "e3efe6fd-906e-11eb-a875-0242ac150002",
      "mandate_type": "senateur",
      "delegation": "Lorem ipsum 3",
      "begin_at": "2023-07-18"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "mandate_type": "senateur",
      "delegation": "Lorem ipsum 3",
      "zone": {
          "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
          "code": "92",
          "name": "Hauts-de-Seine"
      },
      "uuid": "@uuid@",
      "adherent": {"uuid": "@uuid@"},
      "begin_at": "2023-07-18T00:00:00+02:00",
      "finish_at": null
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
