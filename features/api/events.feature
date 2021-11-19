@api
Feature:
  In order to get events count in the
  As a client passing a referent email
  I should be able to access events API stats

  Background:
    Given I freeze the clock to "2018-05-18"

  Scenario: As a non logged-in user I can not get events count in the referent managed zone
    When I am on "/api/statistics/events/count"
    Then the response status code should be 401

  Scenario Outline: As an adherent I can not get events count in the referent managed zone
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "<url>"
    Then the response status code should be 401

    Examples:
      | url                                       |
      | /api/statistics/events/count              |
      | /api/statistics/events/count-by-month     |
      | /api/statistics/events/count-participants |

  Scenario: As a client passing a referent email I can get events count in the referent managed zone
    Given I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
      | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
      | grant_type    | client_credentials                           |
      | scope         | read:stats                                   |
    And I add the access token to the Authorization header
    When I send a "GET" request to "/api/statistics/events/count?referent=referent-75-77@en-marche-dev.fr"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "current_total":4,
      "events": [
        {"date": "2018-05", "count":3},
        {"date": "2018-04", "count":2},
        {"date": "2018-03", "count":0},
        {"date": "2018-02", "count":0},
        {"date": "2018-01", "count":0},
        {"date": "2017-12", "count":0}
      ],
      "referent_events": [
        {"date": "2018-05", "count":1},
        {"date": "2018-04", "count":0},
        {"date": "2018-03", "count":0},
        {"date": "2018-02", "count":0},
        {"date": "2018-01", "count":0},
        {"date": "2017-12", "count":0}
      ]
    }
    """

  Scenario: As a non logged-in user I can not get events count in the referent managed zone
    When I am on "/api/statistics/events/count-by-month?country=fr"
    Then the response status code should be 401

  Scenario:  As a client passing a referent email I can get events count in the referent managed zone
    Given I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
      | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
      | grant_type    | client_credentials                           |
      | scope         | read:stats                                   |
    And I add the access token to the Authorization header
    When I send a "GET" request to "/api/statistics/events/count-by-month?referent=referent-75-77@en-marche-dev.fr"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "events": [
        {"date": "2018-05", "count":3},
        {"date": "2018-04", "count":2},
        {"date": "2018-03", "count":0},
        {"date": "2018-02", "count":0},
        {"date": "2018-01", "count":0},
        {"date": "2017-12", "count":0}
      ],
      "event_participants": [
        {"date": "2018-05", "count":5},
        {"date": "2018-04", "count":4},
        {"date": "2018-03", "count":0},
        {"date": "2018-02", "count":0},
        {"date": "2018-01", "count":0},
        {"date": "2017-12", "count":0}
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
    When I send a "GET" request to "/api/statistics/events/count-by-month?referent=referent-75-77@en-marche-dev.fr&country=fr"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "events": [
        {"date": "2018-05", "count":3},
        {"date": "2018-04", "count":2},
        {"date": "2018-03", "count":0},
        {"date": "2018-02", "count":0},
        {"date": "2018-01", "count":0},
        {"date": "2017-12", "count":0}
      ],
      "event_participants": [
        {"date": "2018-05", "count":5},
        {"date": "2018-04", "count":4},
        {"date": "2018-03", "count":0},
        {"date": "2018-02", "count":0},
        {"date": "2018-01", "count":0},
        {"date": "2017-12", "count":0}
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
    When I send a "GET" request to "/api/statistics/events/count-by-month?referent=referent-75-77@en-marche-dev.fr&city=Paris%208e"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "events": [
        {"date": "2018-05", "count":3},
        {"date": "2018-04", "count":0},
        {"date": "2018-03", "count":0},
        {"date": "2018-02", "count":0},
        {"date": "2018-01", "count":0},
        {"date": "2017-12", "count":0}
      ],
      "event_participants": [
        {"date": "2018-05", "count":5},
        {"date": "2018-04", "count":0},
        {"date": "2018-03", "count":0},
        {"date": "2018-02", "count":0},
        {"date": "2018-01", "count":0},
        {"date": "2017-12", "count":0}
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
    When I send a "GET" request to "/api/statistics/events/count-by-month?referent=referent-75-77@en-marche-dev.fr&city=Fontainebleau"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "events": [
        {"date": "2018-05", "count":0},
        {"date": "2018-04", "count":2},
        {"date": "2018-03", "count":0},
        {"date": "2018-02", "count":0},
        {"date": "2018-01", "count":0},
        {"date": "2017-12", "count":0}
      ],
      "event_participants": [
        {"date": "2018-05", "count":0},
        {"date": "2018-04", "count":4},
        {"date": "2018-03", "count":0},
        {"date": "2018-02", "count":0},
        {"date": "2018-01", "count":0},
        {"date": "2017-12", "count":0}
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
    When I send a "GET" request to "/api/statistics/events/count-by-month?referent=referent-75-77@en-marche-dev.fr&committee=515a56c0-bde8-56ef-b90c-4745b1c93818"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "events": [
        {"date": "2018-05", "count":3},
        {"date": "2018-04", "count":0},
        {"date": "2018-03", "count":0},
        {"date": "2018-02", "count":0},
        {"date": "2018-01", "count":0},
        {"date": "2017-12", "count":0}
      ],
      "event_participants": [
        {"date": "2018-05", "count":5},
        {"date": "2018-04", "count":0},
        {"date": "2018-03", "count":0},
        {"date": "2018-02", "count":0},
        {"date": "2018-01", "count":0},
        {"date": "2017-12", "count":0}
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
    When I send a "GET" request to "/api/statistics/events/count-by-month?referent=referent-75-77@en-marche-dev.fr&committee=62ea97e7-6662-427b-b90a-23429136d0dd"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "events": [
        {"date": "2018-05", "count":0},
        {"date": "2018-04", "count":0},
        {"date": "2018-03", "count":0},
        {"date": "2018-02", "count":0},
        {"date": "2018-01", "count":0},
        {"date": "2017-12", "count":0}
      ],
      "event_participants": [
        {"date": "2018-05", "count":0},
        {"date": "2018-04", "count":0},
        {"date": "2018-03", "count":0},
        {"date": "2018-02", "count":0},
        {"date": "2018-01", "count":0},
        {"date": "2017-12", "count":0}
      ]
    }
    """

  Scenario: As a client passing a referent email I can get participants count
    Given I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
      | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
      | grant_type    | client_credentials                           |
      | scope         | read:stats                                   |
    And I add the access token to the Authorization header
    When I send a "GET" request to "/api/statistics/events/count-participants?referent=referent-75-77@en-marche-dev.fr"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "total": 7,
      "participants": [
        {"date": "2018-05", "count": 5},
        {"date": "2018-04", "count": 4},
        {"date": "2018-03", "count": 0},
        {"date": "2018-02", "count": 0},
        {"date": "2018-01", "count": 0},
        {"date": "2017-12", "count": 0}
      ],
      "participants_as_adherent": [
        {"date": "2018-05", "count": 3},
        {"date": "2018-04", "count": 4},
        {"date": "2018-03", "count": 0},
        {"date": "2018-02", "count": 0},
        {"date": "2018-01", "count": 0},
        {"date": "2017-12", "count": 0}
      ]
    }
    """

  Scenario Outline: As a logged-in user I can get an event
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App" with scope "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "GET" request to "/api/v3/events/<uuid>"
    Then the response status code should be 200
    Examples:
      | uuid                                  |
      # scheduled and published
      | 0e5f9f02-fa33-4c2c-a700-4235d752315b  |
      # scheduled and private
      | 47e5a8bf-8be1-4c38-aae8-b41e6908a1b3  |

  Scenario: As a logged-in user I cannot get not published event
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App" with scope "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "GET" request to "/api/v3/events/de7f027c-f6c3-439f-b1dd-bf2b110a0fb0"
    Then the response status code should be 404

  Scenario: As a logged-in user I can get events
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App" with scope "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "GET" request to "/api/v3/events"
    Then the response status code should be 200
    And the JSON nodes should match:
      | metadata.total_items  | 38 |

  Scenario: As a non logged-in user I can get scheduled and published event
    When I send a "GET" request to "/api/events/0e5f9f02-fa33-4c2c-a700-4235d752315b"
    Then the response status code should be 200

  Scenario: As a non logged-in user I cannot get not published event
    When I send a "GET" request to "/api/events/de7f027c-f6c3-439f-b1dd-bf2b110a0fb0"
    Then the response status code should be 404

  Scenario: As a non logged-in user I cannot get private event
    When I send a "GET" request to "/api/events/47e5a8bf-8be1-4c38-aae8-b41e6908a1b3"
    Then the response status code should be 404

  Scenario: As a non logged-in user I can get events
    When I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the JSON nodes should match:
      | metadata.total_items  | 36 |

  Scenario: As a logged-in user I can get events
    When I am logged as "jacques.picard@en-marche.fr"
    And I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the JSON nodes should match:
      | metadata.total_items  | 38 |

  Scenario: As a non logged-in user I can not check if I'm registered for events
    When I send a "POST" request to "/api/v3/events/registered" with body:
    """
    {
      "uuids": [
        "44249b1d-ea10-41e0-b288-5eb74fa886ba"
      ]
    }
    """
    Then the response status code should be 401

  Scenario: As a logged-in user I can not check if I'm registered for events if no uuids
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Coalition App"
    When I send a "POST" request to "/api/v3/events/registered" with body:
    """
    {
      "uuids": []
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON nodes should match:
      | detail  | Parameter "uuids" should be an array of uuids.  |

  Scenario: As a logged-in user I can check if I'm registered for events
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Coalition App"
    When I send a "POST" request to "/api/v3/events/registered" with body:
    """
    {
      "uuids": [
        "44249b1d-ea10-41e0-b288-5eb74fa886ba"
      ]
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    []
    """

  Scenario: As a non logged-in user I can get only EnMarche or Coalitions events
    When I send a "GET" request to "/api/events?group_source=en_marche"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | metadata.total_items  | 20 |

    When I send a "GET" request to "/api/events?group_source=coalitions"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | metadata.total_items          | 16                                   |
      | items[0].organizer.uuid       | a046adbe-9c7b-56a9-a676-6151a6785dda |
      | items[0].organizer.first_name | Jacques                              |
      | items[0].organizer.last_name  | Picard                               |

  Scenario: As a logged-in user I can not delete an event of another adherent
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App"
    When I send a "DELETE" request to "/api/v3/events/472d1f86-6522-4122-a0f4-abd69d17bb2d"
    Then the response status code should be 403

  Scenario: As a logged-in user I can delete one of my events
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Coalition App"
    When I send a "DELETE" request to "/api/v3/events/472d1f86-6522-4122-a0f4-abd69d17bb2d"
    Then the response status code should be 204

  Scenario: As a logged-in user I can not cancel an event of another adherent
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App"
    When I send a "PUT" request to "/api/v3/events/462d7faf-09d2-4679-989e-287929f50be8/cancel"
    Then the response status code should be 403

  Scenario: As a logged-in user I can cancel my event
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Coalition App"
    When I send a "PUT" request to "/api/v3/events/ef62870c-6d42-47b6-91ea-f454d473adf8/cancel"
    Then the response status code should be 200
    And I should have 1 email
    And I should have 1 email "CoalitionsEventCancellationMessage" for "jacques.picard@en-marche.fr" with payload:
    """
    {
      "template_name": "coalitions-event-cancellation",
      "template_content": [],
      "message": {
        "subject": "✊ Événement annulé",
        "from_email": "contact@pourunecause.fr",
        "global_merge_vars": [
          {
            "name": "event_name",
            "content": "Événement culturel 1 de la cause culturelle 1"
          }
        ],
        "merge_vars": [
          {
            "rcpt": "jacques.picard@en-marche.fr",
            "vars":[
              {
                "name": "first_name",
                "content": "Jacques"
              }
            ]
          },
          {
            "rcpt": "francis.brioul@yahoo.com",
            "vars": [
              {
                "name": "first_name",
                "content": "Francis"
              }
            ]
          }
        ],
        "from_name": "Pour une cause",
        "to": [
          {
            "email": "jacques.picard@en-marche.fr",
            "type": "to",
            "name": "Jacques"
          },
          {
            "email": "francis.brioul@yahoo.com",
            "type": "to",
            "name": "Francis"
          }
        ]
      }
    }
    """

  Scenario: As logged-in user I can not cancel an already cancelled event
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Coalition App"
    When I send a "PUT" request to "/api/v3/events/2f36a0b9-ac1d-4bee-b9ef-525bc89a7c8e/cancel"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON nodes should match:
      | title   | An error occurred               |
      | detail  | this event is already cancelled |
