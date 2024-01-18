@api
@renaissance
Feature:
    In order to get the acquisition statistics data
    I should be able to request them via the API

    Scenario:
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
                "category": "Inscriptions emails",
                "items": {
                    "201801": "@integer@",
                    "201802": "@integer@",
                    "201803": "@integer@"
                }
            },
            {
                "title": "Inscrits à la lettre du vendredi (total)",
                "category": "Inscriptions emails",
                "items": {
                    "201801": "@integer@",
                    "201802": "@integer@",
                    "201803": "@integer@"
                }
            },
            {
                "title": "Adhérents inscrits à la lettre du vendredi (total)",
                "category": "Inscriptions emails",
                "items": {
                    "201801": "@integer@",
                    "201802": "@integer@",
                    "201803": "@integer@"
                }
            },
            {
                "title": "Adhérents inscrits aux emails de leur référent (total)",
                "category": "Inscriptions emails",
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

    Scenario: As a non logged-in user I can not access the committee supervisors count managed by referent information
        When I am on "/api/statistics/committees/count-for-referent-area"
        Then the response status code should be 401

    Scenario: As an adherent I can not access the committee supervisors count managed by referent information
        When I am logged as "jacques.picard@en-marche.fr"
        And I am on "/api/statistics/committees/count-for-referent-area"
        Then the response status code should be 401

    Scenario: As a referent I can access the committee supervisors count managed by referent information
        Given I send a "POST" request to "/oauth/v2/token" with parameters:
            | key           | value                                        |
            | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
            | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
            | grant_type    | client_credentials                           |
            | scope         | read:stats                                   |
        And I add the access token to the Authorization header
        When I send a "GET" request to "/api/statistics/committees/count-for-referent-area?referent=referent@en-marche-dev.fr"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
          "committees":4,
          "members": {
            "female":3,
            "male":7,
            "total":10
          },
          "supervisors": {
            "female":2,
            "male":2,
            "total":4
          }
        }
        """

    Scenario: As a non logged-in user I can not get the most active committees in referent managed zone
        When I am on "/api/statistics/committees/top-5-in-referent-area"
        Then the response status code should be 401

    Scenario: As an adherent I can not get the most active committees in referent managed zone
        When I am logged as "jacques.picard@en-marche.fr"
        And I am on "/api/statistics/committees/top-5-in-referent-area"
        Then the response status code should be 401

    Scenario: As a referent I can get the most active committees in referent managed zone
        Given I freeze the clock to "2018-05-18"
        Given I send a "POST" request to "/oauth/v2/token" with parameters:
            | key           | value                                        |
            | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
            | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
            | grant_type    | client_credentials                           |
            | scope         | read:stats                                   |
        And I add the access token to the Authorization header
        When I send a "GET" request to "/api/statistics/committees/top-5-in-referent-area?referent=referent@en-marche-dev.fr"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
          "most_active": [
            {"name":"En Marche Dammarie-les-Lys","events":1},
            {"name":"Antenne En Marche de Fontainebleau","events":1},
            {"name":"En Marche - Suisse","events":1}
          ],
          "least_active": [
            {"name":"En Marche - Suisse","events":1},
            {"name":"Antenne En Marche de Fontainebleau","events":1},
            {"name":"En Marche Dammarie-les-Lys","events":1}
          ]
        }
        """

    Scenario: As a non logged-in user I can not get the committee members count in referent managed zone
        When I am on "/api/statistics/committees/members/count-by-month"
        Then the response status code should be 401

    Scenario: As an adherent I can not get the committee members count in referent managed zone
        When I am logged as "jacques.picard@en-marche.fr"
        And I am on "/api/statistics/committees/members/count-by-month"
        Then the response status code should be 401

    Scenario: As a referent I can get the committee members count in referent managed zone
        Given I freeze the clock to "2018-04-15"
        Given I send a "POST" request to "/oauth/v2/token" with parameters:
            | key           | value                                        |
            | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
            | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
            | grant_type    | client_credentials                           |
            | scope         | read:stats                                   |
        And I add the access token to the Authorization header
        When I send a "GET" request to "/api/statistics/committees/members/count-by-month?referent=referent-75-77@en-marche-dev.fr"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
          "committee_members": [
            {"date": "2018-04", "count":3},
            {"date": "2018-03", "count":3},
            {"date": "2018-02", "count":2},
            {"date": "2018-01", "count":2},
            {"date": "2017-12", "count":2},
            {"date": "2017-11", "count":2}
          ]
        }
        """
        Given I send a "POST" request to "/oauth/v2/token" with parameters:
            | key           | value                                        |
            | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
            | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
            | grant_type    | client_credentials                           |
            | scope         | read:stats                                   |
        And I add the access token to the Authorization header
        When I send a "GET" request to "/api/statistics/committees/members/count-by-month?referent=referent-75-77@en-marche-dev.fr&country=fr"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
          "committee_members": [
            {"date": "2018-04", "count":3},
            {"date": "2018-03", "count":3},
            {"date": "2018-02", "count":2},
            {"date": "2018-01", "count":2},
            {"date": "2017-12", "count":2},
            {"date": "2017-11", "count":2}
          ]
        }
        """
        Given I send a "POST" request to "/oauth/v2/token" with parameters:
            | key           | value                                        |
            | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
            | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
            | grant_type    | client_credentials                           |
            | scope         | read:stats                                   |
        And I add the access token to the Authorization header
        When I send a "GET" request to "/api/statistics/committees/members/count-by-month?referent=referent-75-77@en-marche-dev.fr&country=ch"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
          "committee_members": [
            {"date": "2018-04", "count":0},
            {"date": "2018-03", "count":0},
            {"date": "2018-02", "count":0},
            {"date": "2018-01", "count":0},
            {"date": "2017-12", "count":0},
            {"date": "2017-11", "count":0}
          ]
        }
        """
        Given I send a "POST" request to "/oauth/v2/token" with parameters:
            | key           | value                                        |
            | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
            | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
            | grant_type    | client_credentials                           |
            | scope         | read:stats                                   |
        And I add the access token to the Authorization header
        When I send a "GET" request to "/api/statistics/committees/members/count-by-month?referent=referent-75-77@en-marche-dev.fr&city=Paris%208ème"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
          "committee_members": [
            {"date": "2018-04", "count":2},
            {"date": "2018-03", "count":2},
            {"date": "2018-02", "count":1},
            {"date": "2018-01", "count":1},
            {"date": "2017-12", "count":1},
            {"date": "2017-11", "count":1}
          ]
        }
        """
        Given I send a "POST" request to "/oauth/v2/token" with parameters:
            | key           | value                                        |
            | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
            | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
            | grant_type    | client_credentials                           |
            | scope         | read:stats                                   |
        And I add the access token to the Authorization header
        When I send a "GET" request to "/api/statistics/committees/members/count-by-month?referent=referent-75-77@en-marche-dev.fr&city=Dammarie-les-Lys"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
          "committee_members": [
            {"date": "2018-04", "count":1},
            {"date": "2018-03", "count":1},
            {"date": "2018-02", "count":1},
            {"date": "2018-01", "count":1},
            {"date": "2017-12", "count":1},
            {"date": "2017-11", "count":1}
          ]
        }
        """
        Given I send a "POST" request to "/oauth/v2/token" with parameters:
            | key           | value                                        |
            | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
            | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
            | grant_type    | client_credentials                           |
            | scope         | read:stats                                   |
        And I add the access token to the Authorization header
        When I send a "GET" request to "/api/statistics/committees/members/count-by-month?referent=referent-75-77@en-marche-dev.fr&committee=515a56c0-bde8-56ef-b90c-4745b1c93818"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
          "committee_members": [
            {"date": "2018-04", "count":2},
            {"date": "2018-03", "count":2},
            {"date": "2018-02", "count":1},
            {"date": "2018-01", "count":1},
            {"date": "2017-12", "count":1},
            {"date": "2017-11", "count":1}
          ]
        }
        """
        # Test get stats for committee with scheduled events but not managed by referent
        Given I send a "POST" request to "/oauth/v2/token" with parameters:
            | key           | value                                        |
            | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
            | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
            | grant_type    | client_credentials                           |
            | scope         | read:stats                                   |
        And I add the access token to the Authorization header
        When I send a "GET" request to "/api/statistics/committees/members/count-by-month?referent=referent-75-77@en-marche-dev.fr&committee=b0cd0e52-a5a4-410b-bba3-37afdd326a0a"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
          "committee_members": [
            {"date": "2018-04", "count":1},
            {"date": "2018-03", "count":1},
            {"date": "2018-02", "count":1},
            {"date": "2018-01", "count":1},
            {"date": "2017-12", "count":1},
            {"date": "2017-11", "count":1}
          ]
        }
        """

    Scenario: As a non logged-in user I can not access the adherents count information
        When I am on "/api/statistics/adherents/count"
        Then the response status code should be 401

    Scenario: As an adherent I can not access the adherents count information
        When I am logged as "jacques.picard@en-marche.fr"
        And I am on "/api/statistics/adherents/count"
        Then the response status code should be 401

    Scenario: As a referent I can access the adherents count information
        Given I send a "POST" request to "/oauth/v2/token" with parameters:
            | key           | value                                        |
            | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
            | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
            | grant_type    | client_credentials                           |
            | scope         | read:stats                                   |
        And I add the access token to the Authorization header
        When I send a "GET" request to "/api/statistics/adherents/count?referent=referent@en-marche-dev.fr"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
          "female":29,
          "male":46,
          "total":75
        }
        """

    Scenario: As a non logged-in user I can not access the managed by referent adherents count information
        When I am on "/api/statistics/adherents/count-by-referent-area"
        Then the response status code should be 401

    Scenario: As an adherent I can not access the managed by referent adherents count information
        When I am logged as "jacques.picard@en-marche.fr"
        And I am on "/api/statistics/adherents/count-by-referent-area"
        Then the response status code should be 401

    Scenario: As a referent I can access the managed by referent adherents count information
        Given I freeze the clock to "2018-04-17"
        Given I send a "POST" request to "/oauth/v2/token" with parameters:
            | key           | value                                        |
            | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
            | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
            | grant_type    | client_credentials                           |
            | scope         | read:stats                                   |
        And I add the access token to the Authorization header
        When I send a "GET" request to "/api/statistics/adherents/count-by-referent-area?referent=referent-75-77@en-marche-dev.fr"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
          "female":3,
          "male":7,
          "total":10,
          "adherents": [
              {"date": "2018-04", "total": 9},
              {"date": "2018-03", "total": 9},
              {"date": "2018-02", "total": 9},
              {"date": "2018-01", "total": 9},
              {"date": "2017-12", "total": 8},
              {"date": "2017-11", "total": 8}
          ],
          "committee_members": [
              {"date": "2018-04", "count": 3},
              {"date": "2018-03", "count": 3},
              {"date": "2018-02", "count": 2},
              {"date": "2018-01", "count": 2},
              {"date": "2017-12", "count": 2},
              {"date": "2017-11", "count": 2}
          ],
          "email_subscriptions": [
              {"date": "2018-04", "subscribed_emails_local_host": 0, "subscribed_emails_referents": 0},
              {"date": "2018-03", "subscribed_emails_local_host": 0, "subscribed_emails_referents": 0},
              {"date": "2018-02", "subscribed_emails_local_host": 4, "subscribed_emails_referents": 0},
              {"date": "2018-01", "subscribed_emails_local_host": 3, "subscribed_emails_referents": 0},
              {"date": "2017-12", "subscribed_emails_local_host": 2, "subscribed_emails_referents": 0},
              {"date": "2017-11", "subscribed_emails_local_host": 1, "subscribed_emails_referents": 0}
          ]
        }
        """
