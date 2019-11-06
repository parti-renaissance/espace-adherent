@api
Feature:
  In order to get the acquisition statistics data
  I should be able to request them via the API

  Scenario:
    Given the following fixtures are loaded:
      | LoadClientData |
    And I add "Accept" header equal to "application/json"
    And I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
      | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
      | grant_type    | client_credentials                           |
      | scope         | read:stats                                   |
    And I add the access token to the Authorization header
    When I send a "GET" request to "/api/statistics/acquisition?start-date=01-01-2018&end-date=31-03-2018&tags%5B%5D=CH"
    Then print last response
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "root" should have 21 elements
    And the JSON should be equal to:
    """
    [
        {
            "title": "Adhérents (total)",
            "category": "Adhésion",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Adhérents (nouveaux)",
            "category": "Adhésion",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Desadhésions (nouveaux)",
            "category": "Adhésion",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Comités (nouveaux)",
            "category": "Adhésion",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Comités en attente (nouveaux)",
            "category": "Adhésion",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Adhérents membres de comités (total)",
            "category": "Adhésion",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Ratio membre de comité par nbr adhérents (total)",
            "category": "Adhésion",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Événements (nouveaux)",
            "category": "Adhésion",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Inscrits à des événements (total)",
            "category": "Adhésion",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Adhérents inscrits à des événements (total)",
            "category": "Adhésion",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Non-adhérents inscrits à des événements (total)",
            "category": "Adhésion",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Inscrits à la liste globale (total)",
            "category": "Inscriptions e-mails",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Inscrits à la lettre du vendredi (total)",
            "category": "Inscriptions e-mails",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Adhérents inscrits à la lettre du vendredi (total)",
            "category": "Inscriptions e-mails",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Adhérents inscrits aux mails de leur référent (total)",
            "category": "Inscriptions e-mails",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Dons ponctuels (total)",
            "category": "Dons",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Dons ponctuels par des adhérents (total)",
            "category": "Dons",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Montant dons ponctuels (total)",
            "category": "Dons",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Dons mensuels (total)",
            "category": "Dons",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Dons mensuels par des adhérents (total)",
            "category": "Dons",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "title": "Montant dons mensuels (total)",
            "category": "Dons",
            "items": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        }
    ]
    """
