@api
Feature:
  In order to get and manipulate events
  As a client of different apps
  I should be able to access events API

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
            "vars": [
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
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
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

  Scenario: As a DC referent I can get an ordered list of events corresponding to my zones
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/events?scope=referent&page_size=3&order[bagin_at]=asc"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 7,
            "items_per_page": 3,
            "count": 3,
            "current_page": 1,
            "last_page": 3
        },
        "items": [
            {
                "uuid": "113876dd-87d2-426a-a12a-60ffd5107b10",
                "name": "Grand Meeting de Marseille",
                "time_zone": "Europe/Paris",
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
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
                "local_finish_at": "@string@.isDateTime()",
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
            }
        ]
    }
    """
    When I send a "GET" request to "/api/v3/events?scope=referent&page_size=3&order[finish_at]=desc"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 7,
            "items_per_page": 3,
            "count": 3,
            "current_page": 1,
            "last_page": 3
        },
        "items": [
            {
                "uuid": "113876dd-87d2-426a-a12a-60ffd5107b10",
                "name": "Grand Meeting de Marseille",
                "time_zone": "Europe/Paris",
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
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
                "local_finish_at": "@string@.isDateTime()",
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
            }
        ]
    }
    """

  Scenario: As a DC referent I can get a list of events created by me
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/events?only_mine&page_size=10"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
        {
        "metadata": {
            "total_items": 4,
            "items_per_page": 10,
            "count": 4,
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
                "uuid": "4d962b05-68fe-4888-ab6b-53b96bdbe797",
                "name": "Un événement du référent annulé",
                "time_zone": "Europe/Paris",
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "organizer": {
                    "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                    "first_name": "Referent",
                    "last_name": "Referent"
                },
                "participants_count": 0,
                "status": "CANCELLED",
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
                "category": null,
                "mode": "online",
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

  Scenario: As a DC referent I can get one event
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/events/0e5f9f02-fa33-4c2c-a700-4235d752315b"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "committee": {
            "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818",
            "name": "En Marche Paris 8",
            "slug": "en-marche-paris-8",
            "link": "http://test.enmarche.code/comites/en-marche-paris-8"
        },
        "uuid": "0e5f9f02-fa33-4c2c-a700-4235d752315b",
        "name": "Événement de la catégorie masquée",
        "slug": "@string@-evenement-de-la-categorie-masquee",
        "description": "Allons à la rencontre des citoyens.",
        "time_zone": "Europe/Paris",
        "begin_at": "@string@.isDateTime()",
        "finish_at": "@string@.isDateTime()",
        "organizer": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
        },
        "participants_count": 0,
        "status": "SCHEDULED",
        "capacity": 10,
        "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
        },
        "category": {
            "event_group_category": {
                "name": "événement",
                "slug": "evenement"
            },
            "name": "Catégorie masquée",
            "slug": "categorie-masquee"
        },
        "visio_url": null,
        "mode": null,
        "image_url": null,
        "link": "http://test.enmarche.code/evenements/@string@-evenement-de-la-categorie-masquee",
        "user_registered_at": null
    }
    """

  Scenario: As a DC referent I can get one event with full info
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/events/0e5f9f02-fa33-4c2c-a700-4235d752315b?scope=referent"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "committee": {
            "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818",
            "name": "En Marche Paris 8",
            "slug": "en-marche-paris-8",
            "link": "http://test.enmarche.code/comites/en-marche-paris-8"
        },
        "uuid": "0e5f9f02-fa33-4c2c-a700-4235d752315b",
        "name": "Événement de la catégorie masquée",
        "slug": "@string@-evenement-de-la-categorie-masquee",
        "description": "Allons à la rencontre des citoyens.",
        "time_zone": "Europe/Paris",
        "begin_at": "@string@.isDateTime()",
        "finish_at": "@string@.isDateTime()",
        "organizer": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
        },
        "participants_count": 0,
        "status": "SCHEDULED",
        "capacity": 10,
        "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
        },
        "category": {
            "event_group_category": {
                "name": "événement",
                "slug": "evenement"
            },
            "name": "Catégorie masquée",
            "slug": "categorie-masquee"
        },
        "private": false,
        "electoral": false,
        "visio_url": null,
        "mode": null,
        "image_url": null,
        "link": "http://test.enmarche.code/evenements/@string@-evenement-de-la-categorie-masquee",
        "user_registered_at": null
    }
    """

  Scenario: As a DC deputy I cannot create an event with missing or invalid data
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/events" with body:
    """
    {
       "begin_at": "2018-01-01 10:10:10",
       "finish_at": "2018-01-06 16:30:30"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "detail": "finish_at: La date de fin de votre événement ne peut pas dépasser le 4 janv. 2018 à 09:10.\nname: Cette valeur ne doit pas être vide.\ncanonical_name: Cette valeur ne doit pas être vide.\ndescription: Cette valeur ne doit pas être vide.\nbegin_at: La date de début doit être dans le future.",
      "title": "An error occurred",
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "violations": [
          {
             "message": "La date de fin de votre événement ne peut pas dépasser le 4 janv. 2018 à 09:10.",
             "propertyPath": "finish_at"
          },
          {
              "message": "Cette valeur ne doit pas être vide.",
              "propertyPath": "name"
          },
          {
              "message": "Cette valeur ne doit pas être vide.",
              "propertyPath": "canonical_name"
          },
          {
              "message": "Cette valeur ne doit pas être vide.",
              "propertyPath": "description"
          },
          {
              "message": "La date de début doit être dans le future.",
              "propertyPath": "begin_at"
          }
      ]
    }
    """

  Scenario: As a DC deputy I can create an event with invalid data
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/events" with body:
    """
    {
        "name": "Nouveau événement",
        "category": "kiosque",
        "description": "Une description de l'événement",
        "begin_at": "2022-01-29 16:30:30",
        "finish_at": "2022-01-30 16:30:30",
        "capacity": 100,
        "mode": "online",
        "visio_url": "https://en-marche.fr/reunions/123",
        "post_address": {
          "address": "50 rue de la villette",
          "postal_code": "69003",
          "city_name": "Lyon 3e",
          "country": "FR"
        },
        "time_zone": "Europe/Paris",
        "electoral": false,
        "private": true
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "uuid": "@uuid@",
        "name": "Nouveau événement",
        "slug": "2022-01-29-nouveau-evenement",
        "description": "Une description de l'événement",
        "time_zone": "Europe/Paris",
        "begin_at": "2022-01-29T16:30:30+01:00",
        "finish_at": "2022-01-30T16:30:30+01:00",
        "organizer": {
            "uuid": "918f07e5-676b-49c0-b76d-72ce01cb2404",
            "first_name": "Député",
            "last_name": "PARIS I"
        },
        "participants_count": 1,
        "status": "SCHEDULED",
        "capacity": 100,
        "post_address": {
            "address": "50 rue de la villette",
            "postal_code": "69003",
            "city": "69003-69383",
            "city_name": "Lyon 3e",
            "country": "FR",
            "latitude": 45.759636,
            "longitude": 4.861436
        },
        "category": {
            "event_group_category": {
                "name": "événement",
                "slug": "evenement"
            },
            "name": "Kiosque",
            "slug": "kiosque"
        },
        "visio_url": "https://en-marche.fr/reunions/123",
        "mode": "online",
        "image_url": null
    }
    """

  Scenario: As a DC referent I can edit my default event
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/events/5cab27a7-dbb3-4347-9781-566dad1b9eb5" with body:
    """
    {
      "description": "Nouvelle description",
      "begin_at": "2022-05-12 10:30:00",
      "finish_at": "2022-05-12 16:30:00",
      "mode": "online",
      "visio_url": "http://visio.fr",
      "post_address": {
        "address": "dammarie-les-lys",
        "postal_code": "77190",
        "city": "77190-77152",
        "city_name": "dammarie-les-lys",
        "country": "FR"
      }
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "uuid": "5cab27a7-dbb3-4347-9781-566dad1b9eb5",
        "name": "Nouvel événement online",
        "slug": "2022-05-12-nouvel-evenement-online",
        "description": "Nouvelle description",
        "time_zone": "Europe/Paris",
        "begin_at": "2022-05-12T10:30:00+02:00",
        "finish_at": "2022-05-12T16:30:00+02:00",
        "organizer": {
            "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
            "first_name": "Referent",
            "last_name": "Referent"
        },
        "participants_count": 0,
        "status": "SCHEDULED",
        "capacity": 50,
        "post_address": {
            "address": "dammarie-les-lys",
            "postal_code": "77190",
            "city": "77190-77152",
            "city_name": "dammarie-les-lys",
            "country": "FR",
            "latitude": null,
            "longitude": null
        },
        "category": null,
        "visio_url": "http://visio.fr",
        "mode": "online",
        "image_url": null
    }
    """
    And I should have 1 email
    And I should have 1 email "EventUpdateMessage" for "francis.brioul@yahoo.com" with payload:
    """
    {
       "template_name": "event-update",
       "template_content": [

       ],
       "message": {
          "subject": "Un événement auquel vous participez a été mis à jour",
          "from_email": "contact@en-marche.fr",
          "global_merge_vars": [
             {
                "name": "event_name",
                "content": "Nouvel événement online"
             },
             {
                "name": "event_url",
                "content": "http://test.enmarche.code/evenements/2022-05-12-nouvel-evenement-online"
             },
             {
                "name": "event_date",
                "content": "jeudi 12 mai 2022"
             },
             {
                "name": "event_hour",
                "content": "10h30"
             },
             {
                "name": "event_address",
                "content": "dammarie-les-lys, 77190 dammarie-les-lys"
             },
             {
                "name": "calendar_url",
                "content": "http://test.enmarche.code/evenements/2022-05-12-nouvel-evenement-online/ical"
             }
          ],
          "merge_vars": [
             {
                "rcpt": "referent@en-marche-dev.fr",
                "vars": [
                   {
                      "name": "first_name",
                      "content": "Referent"
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
             },
             {
                "rcpt": "simple-user@example.ch",
                "vars": [
                   {
                      "name": "first_name",
                      "content": "Simple"
                   }
                ]
             },
             {
                "rcpt": "marie.claire@test.com",
                "vars": [
                   {
                      "name": "first_name",
                      "content": "Marie"
                   }
                ]
             }
          ],
          "headers": {
             "Reply-To": "referent@en-marche-dev.fr"
          },
          "from_name": "La République En Marche !",
          "to": [
             {
                "email": "referent@en-marche-dev.fr",
                "type": "to",
                "name": "Referent Referent"
             },
             {
                "email": "francis.brioul@yahoo.com",
                "type": "to",
                "name": "Francis Brioul"
             },
             {
                "email": "simple-user@example.ch",
                "type": "to",
                "name": "Simple User"
             },
             {
                "email": "marie.claire@test.com",
                "type": "to",
                "name": "Marie CLAIRE"
             }
          ]
       }
    }
    """

  Scenario: As a DC referent I can cancel my default event
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "PUT" request to "/api/v3/events/5cab27a7-dbb3-4347-9781-566dad1b9eb5/cancel"
    Then the response status code should be 200
    And I should have 1 email
    And I should have 1 email "EventCancellationMessage" for "francis.brioul@yahoo.com" with payload:
    """
    {
       "template_name": "event-cancellation",
       "template_content": [],
       "message": {
          "subject": "L'événement @string@Nouvel événement online@string@ a été annulé.",
          "from_email": "contact@en-marche.fr",
          "global_merge_vars": [
             {
                "name": "event_name",
                "content": "Nouvel événement online"
             },
             {
                "name": "event_slug",
                "content": "http://test.enmarche.code/evenements"
             }
          ],
          "merge_vars": [
             {
                "rcpt": "referent@en-marche-dev.fr",
                "vars": [
                   {
                      "name": "target_firstname",
                      "content": "Referent"
                   }
                ]
             },
             {
                "rcpt": "francis.brioul@yahoo.com",
                "vars": [
                   {
                      "name": "target_firstname",
                      "content": "Francis"
                   }
                ]
             },
             {
                "rcpt": "simple-user@example.ch",
                "vars": [
                   {
                      "name": "target_firstname",
                      "content": "Simple"
                   }
                ]
             },
             {
                "rcpt": "marie.claire@test.com",
                "vars": [
                   {
                      "name": "target_firstname",
                      "content": "Marie"
                   }
                ]
             }
          ],
          "headers": {
             "Reply-To": "referent@en-marche-dev.fr"
          },
          "from_name": "La République En Marche !",
          "to": [
             {
                "email": "referent@en-marche-dev.fr",
                "type": "to",
                "name": "Referent Referent"
             },
             {
                "email": "francis.brioul@yahoo.com",
                "type": "to",
                "name": "Francis Brioul"
             },
             {
                "email": "simple-user@example.ch",
                "type": "to",
                "name": "Simple User"
             },
             {
                "email": "marie.claire@test.com",
                "type": "to",
                "name": "Marie CLAIRE"
             }
          ]
       }
    }
    """

  Scenario: As a DC referent I cannot delete my event with participants
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "DELETE" request to "/api/v3/events/5cab27a7-dbb3-4347-9781-566dad1b9eb5"
    Then the response status code should be 403

  Scenario: As a logged-in user I can delete my event with no participants
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Coalition App"
    When I send a "DELETE" request to "/api/v3/events/462d7faf-09d2-4679-989e-287929f50be8"
    Then the response status code should be 204

  Scenario: As a DC referent I can get the list of event participants
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/events/5cab27a7-dbb3-4347-9781-566dad1b9eb5/participants?scope=referent&page_size=10"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 4,
            "items_per_page": 10,
            "count": 4,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "subscription_date": "@string@.isDateTime()",
                "type": "adherent",
                "first_name": "Referent",
                "last_name": "Referent",
                "postal_code": "77000"
            },
            {
                "subscription_date": "@string@.isDateTime()",
                "type": "adherent",
                "first_name": "Francis",
                "last_name": "Brioul",
                "postal_code": "77000"
            },
            {
                "subscription_date": "@string@.isDateTime()",
                "type": "user",
                "first_name": "Simple",
                "last_name": "User",
                "postal_code": "8057"
            },
            {
                "subscription_date": "@string@.isDateTime()",
                "type": "contact",
                "first_name": "Marie",
                "last_name": "CLAIRE",
                "postal_code": null
            }
        ]
    }
    """
