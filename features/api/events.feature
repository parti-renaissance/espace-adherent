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
      | metadata.total_items  | 22 |

  Scenario: As a logged-in user I can get coalitions events
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App" with scope "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "GET" request to "/api/v3/events?group_source=coalitions"
    Then the response status code should be 200
    And the JSON nodes should match:
      | metadata.total_items  | 16 |

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
      | metadata.total_items  | 20 |

  Scenario: As a logged-in user I can get events
    When I am logged as "jacques.picard@en-marche.fr"
    And I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the JSON nodes should match:
      | metadata.total_items  | 22 |

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

  Scenario: As a DC referent I can get the list of events corresponding to my zones
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/events?scope=referent&page_size=10"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 7,
            "items_per_page": 10,
            "count": 7,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "uuid": "113876dd-87d2-426a-a12a-60ffd5107b10",
                "name": "Grand Meeting de Marseille",
                "time_zone": "Europe/Paris",
                "begin_at": "2017-02-20T09:30:00+01:00",
                "finish_at": "2017-02-20T19:00:00+01:00",
                "organizer": {
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard"
                },
                "participants_count": 1,
                "status": "SCHEDULED",
                "capacity": 2000,
                "post_address": {
                    "address": "2 Place de la Major",
                    "postal_code": "13002",
                    "city": "13002-13202",
                    "city_name": "Marseille 2e",
                    "country": "FR",
                    "latitude": 43.298492,
                    "longitude": 5.362377
                },
                "created_at": "@string@.isDateTime()",
                "category": {
                    "event_group_category": {
                        "name": "événement",
                        "slug": "evenement"
                    },
                    "name": "Atelier du programme",
                    "slug": "atelier-du-programme"
                },
                "private": false,
                "electoral": false,
                "visio_url": null,
                "mode": null,
                "local_finish_at": "2017-02-20T19:00:00+01:00",
                "image_url": null,
                "user_registered_at": null
            },
            {
                "uuid": "67e75e81-ad27-4414-bb0b-9e0c6e12b275",
                "name": "Événements à Fontainebleau 1",
                "time_zone": "Europe/Paris",
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "organizer": {
                    "uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                    "first_name": "Francis",
                    "last_name": "Brioul"
                },
                "participants_count": 0,
                "status": "SCHEDULED",
                "capacity": 50,
                "post_address": {
                    "address": "40 Rue Grande",
                    "postal_code": "77300",
                    "city": "77300-77186",
                    "city_name": "Fontainebleau",
                    "country": "FR",
                    "latitude": 48.404766,
                    "longitude": 2.698759
                },
                "created_at": "@string@.isDateTime()",
                "category": {
                    "event_group_category": {
                        "name": "événement",
                        "slug": "evenement"
                    },
                    "name": "Atelier du programme",
                    "slug": "atelier-du-programme"
                },
                "private": false,
                "electoral": false,
                "visio_url": null,
                "mode": null,
                "local_finish_at": "@string@.isDateTime()",
                "image_url": null,
                "user_registered_at": null
            },
            {
                "uuid": "65610a6c-5f18-4e9d-b4ab-0e96c0a52d9e",
                "name": "Événements à Fontainebleau 2",
                "time_zone": "Europe/Paris",
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "organizer": {
                    "uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                    "first_name": "Francis",
                    "last_name": "Brioul"
                },
                "participants_count": 0,
                "status": "SCHEDULED",
                "capacity": 50,
                "post_address": {
                    "address": "40 Rue Grande",
                    "postal_code": "77300",
                    "city": "77300-77186",
                    "city_name": "Fontainebleau",
                    "country": "FR",
                    "latitude": 48.404766,
                    "longitude": 2.698759
                },
                "created_at": "@string@.isDateTime()",
                "category": {
                    "event_group_category": {
                        "name": "événement",
                        "slug": "evenement"
                    },
                    "name": "Conférence-débat",
                    "slug": "conference-debat"
                },
                "private": false,
                "electoral": false,
                "visio_url": null,
                "mode": null,
                "local_finish_at": "@string@.isDateTime()",
                "image_url": null,
                "user_registered_at": null
            },
            {
                "uuid": "a6709808-b3fa-40fc-95a4-da49ddc314ff",
                "name": "Event of non AL",
                "time_zone": "Europe/Zurich",
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "organizer": {
                    "uuid": "46ab0600-b5a0-59fc-83a7-cc23ca459ca0",
                    "first_name": "Michel",
                    "last_name": "VASSEUR"
                },
                "participants_count": 0,
                "status": "SCHEDULED",
                "capacity": 5,
                "post_address": {
                    "address": "12 Pilgerweg",
                    "postal_code": "8802",
                    "city": null,
                    "city_name": "Kilchberg",
                    "country": "CH",
                    "latitude": 47.321568,
                    "longitude": 8.549969
                },
                "created_at": "@string@.isDateTime()",
                "category": {
                    "event_group_category": {
                        "name": "événement",
                        "slug": "evenement"
                    },
                    "name": "Marche",
                    "slug": "marche"
                },
                "private": false,
                "electoral": false,
                "visio_url": null,
                "mode": null,
                "local_finish_at": "@string@.isDateTime()",
                "image_url": null,
                "user_registered_at": null
            },
            {
                "uuid": "47e5a8bf-8be1-4c38-aae8-b41e6908a1b3",
                "name": "Réunion de réflexion bellifontaine",
                "time_zone": "Europe/Paris",
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "organizer": {
                    "uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                    "first_name": "Francis",
                    "last_name": "Brioul"
                },
                "participants_count": 1,
                "status": "SCHEDULED",
                "capacity": 50,
                "post_address": {
                    "address": "40 Rue Grande",
                    "postal_code": "77300",
                    "city": "77300-77186",
                    "city_name": "Fontainebleau",
                    "country": "FR",
                    "latitude": 48.404766,
                    "longitude": 2.698759
                },
                "created_at": "@string@.isDateTime()",
                "category": {
                    "event_group_category": {
                        "name": "événement",
                        "slug": "evenement"
                    },
                    "name": "Réunion d'équipe",
                    "slug": "reunion-dequipe"
                },
                "private": true,
                "electoral": true,
                "visio_url": null,
                "mode": "meeting",
                "local_finish_at": "@string@.isDateTime()",
                "image_url": null,
                "user_registered_at": null
            },
            {
                "uuid": "defd812f-265c-4196-bd33-72fe39e5a2a1",
                "name": "Réunion de réflexion dammarienne",
                "time_zone": "Europe/Paris",
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "organizer": {
                    "uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                    "first_name": "Francis",
                    "last_name": "Brioul"
                },
                "participants_count": 2,
                "status": "SCHEDULED",
                "capacity": 50,
                "post_address": {
                    "address": "824 Avenue du Lys",
                    "postal_code": "77190",
                    "city": "77190-77152",
                    "city_name": "Dammarie-les-Lys",
                    "country": "FR",
                    "latitude": 48.518219,
                    "longitude": 2.624205
                },
                "created_at": "@string@.isDateTime()",
                "category": {
                    "event_group_category": {
                        "name": "événement",
                        "slug": "evenement"
                    },
                    "name": "Kiosque",
                    "slug": "kiosque"
                },
                "private": false,
                "electoral": false,
                "visio_url": null,
                "mode": null,
                "local_finish_at": "@string@.isDateTime()",
                "image_url": null,
                "user_registered_at": null
            },
            {
                "uuid": "24a01f4f-94ea-43eb-8601-579385c59a82",
                "name": "Réunion de réflexion marseillaise",
                "time_zone": "Europe/Paris",
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "organizer": {
                    "uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                    "first_name": "Francis",
                    "last_name": "Brioul"
                },
                "participants_count": 1,
                "status": "SCHEDULED",
                "capacity": 1,
                "post_address": {
                    "address": "2 Place de la Major",
                    "postal_code": "13002",
                    "city": "13002-13202",
                    "city_name": "Marseille 2e",
                    "country": "FR",
                    "latitude": 43.298492,
                    "longitude": 5.362377
                },
                "created_at": "@string@.isDateTime()",
                "category": {
                    "event_group_category": {
                        "name": "événement",
                        "slug": "evenement"
                    },
                    "name": "Tractage",
                    "slug": "tractage"
                },
                "private": false,
                "electoral": false,
                "visio_url": null,
                "mode": null,
                "local_finish_at": "@string@.isDateTime()",
                "image_url": null,
                "user_registered_at": null
            }
        ]
    }
    """

  Scenario: As a DC referent I can get a list of events created by me
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/events?only_mine&page_size=10"
    Then the response status code should be 200
    And print last JSON response
    And the JSON should be equal to:
    """
        {
        "metadata": {
            "total_items": 3,
            "items_per_page": 10,
            "count": 3,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "category": {
                    "name": "Comité politique",
                    "slug": "comite-politique"
                },
                "uuid": "3f46976e-e76a-476e-86d7-575c6d3bc15e",
                "name": "Evénement institutionnel numéro 1",
                "time_zone": "Europe/Paris",
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "organizer": {
                    "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                    "first_name": "Referent",
                    "last_name": "Referent"
                },
                "participants_count": 0,
                "status": "SCHEDULED",
                "capacity": null,
                "post_address": {
                    "address": "16 rue de la Paix",
                    "postal_code": "75008",
                    "city": "75008-75108",
                    "city_name": "Paris 8e",
                    "country": "FR",
                    "latitude": 48.869331,
                    "longitude": 2.331595
                },
                "mode": null,
                "local_finish_at": "@string@.isDateTime()",
                "image_url": null,
                "user_registered_at": null
            },
            {
                "uuid": "5cab27a7-dbb3-4347-9781-566dad1b9eb5",
                "name": "Nouvel événement online",
                "time_zone": "Europe/Paris",
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "organizer": {
                    "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                    "first_name": "Referent",
                    "last_name": "Referent"
                },
                "participants_count": 0,
                "status": "SCHEDULED",
                "capacity": 50,
                "post_address": {
                    "address": null,
                    "postal_code": null,
                    "city": null,
                    "city_name": null,
                    "country": null,
                    "latitude": null,
                    "longitude": null
                },
                "category": null,
                "mode": "online",
                "local_finish_at": "@string@.isDateTime()",
                "image_url": null,
                "user_registered_at": "@string@.isDateTime()"
            },
            {
                "uuid": "2b7238f9-10ca-4a39-b8a4-ad7f438aa95f",
                "name": "Nouvel événement online privé et électoral",
                "time_zone": "Europe/Paris",
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "organizer": {
                    "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                    "first_name": "Referent",
                    "last_name": "Referent"
                },
                "participants_count": 0,
                "status": "SCHEDULED",
                "capacity": 50,
                "post_address": {
                    "address": null,
                    "postal_code": null,
                    "city": null,
                    "city_name": null,
                    "country": null,
                    "latitude": null,
                    "longitude": null
                },
                "category": null,
                "mode": "online",
                "local_finish_at": "@string@.isDateTime()",
                "image_url": null,
                "user_registered_at": "@string@.isDateTime()"
            }
        ]
    }
    """
