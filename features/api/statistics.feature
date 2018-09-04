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
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "root" should have 21 elements
    And the JSON should be equal to:
    """
    [
        {
            "Adhérents (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Nouveaux adhérents (new)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Desadhésions (new)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Comités (new)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Comités en attente (new)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Adhérents membres de comités (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Ratio membre de comité par nbr adhérents (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Événements (new)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Inscrits à des événements (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Adhérents inscrits à des événements (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Non-adhérents inscrits à des événements (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Inscrits à la liste globale (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Inscrits à la lettre du vendredi (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Adhérents inscrits à la lettre du vendredi (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Adhérents inscrits aux mails de leur référent (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Dons ponctuels (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Dons ponctuels par des adhérents (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Montant dons ponctuels (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Dons mensuels (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Dons mensuels par des adhérents (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        },
        {
            "Montant dons mensuels (total)": {
                "201801": "@integer@",
                "201802": "@integer@",
                "201803": "@integer@"
            }
        }
    ]
    """
