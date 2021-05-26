@api
Feature:
  In order to see coalition events
  As a non logged-in user
  I should be able to access API coalition events

  Scenario: As a non logged-in user I see coalition events
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 7,
        "items_per_page": 2,
        "count": 2,
        "current_page": 1,
        "last_page": 4
      },
      "items": [
        {
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "uuid": "472d1f86-6522-4122-a0f4-abd69d17bb2d",
          "name": "Événement culturel 1",
          "slug": "@string@-evenement-culturel-1",
          "description": "Nous allons échanger autour de différents sujets",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "visio_url": null,
          "image_url": null,
          "mode": null
        },
        {
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "uuid": "462d7faf-09d2-4679-989e-287929f50be8",
          "name": "Événement culturel 2",
          "slug": "@string@-evenement-culturel-2",
          "description": "Nous allons échanger encore autour de différents sujets culturels",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "visio_url": null,
          "image_url": null,
          "mode": null
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can paginate coalition events
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/events?page=2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 7,
        "items_per_page": 2,
        "count": 2,
        "current_page": 2,
        "last_page": 4
      },
      "items": [
        {
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Marche",
            "slug": "marche"
          },
          "uuid": "d16f0ab4-292b-4698-847c-005f58ec3119",
          "name": "Événement culturel 5",
          "slug": "@string@-evenement-culturel-5",
          "description": "HAPPINESS FOR EVERYBODY, FREE, AND NO ONE WILL GO AWAY UNSATISFIED!",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "first_name": "Michel",
            "last_name": "VASSEUR"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "12 Pilgerweg",
            "postal_code": "8802",
            "city": null,
            "city_name": "Kilchberg",
            "country": "CH",
            "latitude": 47.321568,
            "longitude": 8.549969
          },
          "visio_url": null,
          "image_url": null,
          "mode": null
        },
        {
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "uuid": "d7e72e52-b81a-4adf-b022-d547672ce095",
          "name": "Événement culturel 3",
          "slug": "@string@-evenement-culturel-3",
          "description": "Nous allons échanger encore autour de différents sujets culturels",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "first_name": "Pierre",
            "last_name": "Kiroule"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "226 W 52nd St",
            "postal_code": "10019",
            "city": null,
            "city_name": "New York",
            "country": "US",
            "latitude": 40.762527,
            "longitude": -73.985992
          },
          "visio_url": null,
          "image_url": null,
          "mode": null
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can paginate coalition events and change number events by page
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/events?page=2&page_size=5"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 7,
        "items_per_page": 5,
        "count": 2,
        "current_page": 2,
        "last_page": 2
      },
      "items": [
        {
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Événement innovant",
            "slug": "evenement-innovant"
          },
          "uuid": "a9d45d86-0333-4767-9853-6e9e7268d778",
          "name": "Événement culturel 6",
          "slug": "@string@-evenement-culturel-6",
          "description": "Du bonheur pour tout le monde, gratuitement, et que personne ne reparte lésé ! ",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "first_name": "Referent75and77",
            "last_name": "Referent75and77"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "visio_url": null,
          "image_url": null,
          "mode": null
        },
        {
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "uuid": "4e805f54-4af6-40f8-91f9-d133407289b3",
          "name": "Événement culturel passé",
          "slug": "@string@-evenement-culturel-passe",
          "description": "Cet événement est passé",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "2 Place de la Major",
            "postal_code": "13002",
            "city": "13002-13202",
            "city_name": "Marseille 2e",
            "country": "FR",
            "latitude": 43.298492,
            "longitude": 5.362377
          },
          "visio_url": null,
          "image_url": null,
          "mode": null
        }
      ]
    }
    """

  Scenario: As a logged-in user I can not create a coalition event with invalid data
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App" with scope "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/events" with body:
    """
    {
       "type":"coalition",
       "coalitions": [
           "fc7fd104-71e5-4399-a874-f8fe752f846b",
           "d5289058-2a35-4cf0-8f2f-a683d97d8315"
       ]
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https:\/\/tools.ietf.org\/html\/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "coalitions: L'événement peut être lié qu'à une seule coalition.\nname: Cette valeur ne doit pas être vide.\ncanonical_name: Cette valeur ne doit pas être vide.\ndescription: Cette valeur ne doit pas être vide.\nbegin_at: Cette valeur ne doit pas être vide.\nfinish_at: Cette valeur ne doit pas être vide.",
      "violations": [
        {
          "propertyPath": "coalitions",
          "message": "L'événement peut être lié qu'à une seule coalition."
        },
        {
          "propertyPath": "name",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "propertyPath": "canonical_name",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "propertyPath": "description",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "propertyPath": "begin_at",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "propertyPath": "finish_at",
          "message": "Cette valeur ne doit pas être vide."
        }
      ]
    }
    """

  Scenario: As a logged-in user I can create a coalition event
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App" with scopes "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/events" with body:
    """
    {
       "type": "coalition",
       "name": "My coalition event",
       "description": "Description",
       "time_zone": "Europe/Paris",
       "begin_at": "2022-04-29 16:30:30",
       "finish_at": "2022-04-30 16:30:30",
       "category": "kiosque",
       "mode": "online",
       "visio_url": "http://visio.fr",
       "image_url": null,
       "post_address": {
          "address": "50 rue de la villette",
          "postal_code": "69003",
          "city_name": "Lyon 3e",
          "country": "FR"
      },
       "coalitions": [
          "fc7fd104-71e5-4399-a874-f8fe752f846b"
       ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "category": {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Kiosque",
        "slug": "kiosque"
      },
      "uuid": "@string@",
      "name": "My coalition event",
      "slug": "@string@-my-coalition-event",
      "description": "Description",
      "time_zone": "Europe/Paris",
      "begin_at": "2022-04-29T16:30:30+02:00",
      "finish_at": "2022-04-30T16:30:30+02:00",
      "organizer": {
        "first_name": "Gisele",
        "last_name": "Berthoux"
      },
      "participants_count": 0,
      "status": "SCHEDULED",
      "capacity": null,
      "post_address": {
        "address": "50 rue de la villette",
        "postal_code": "69003",
        "city": "69003-69383",
        "city_name": "Lyon 3e",
        "country": "FR",
        "latitude": 45.7596356,
        "longitude": 4.8614359
      },
      "visio_url": "http://visio.fr",
      "mode": "online",
      "image_url": null
    }
    """

  Scenario: As a logged-in user I can edit a coalition event
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Coalition App" with scopes "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/events/472d1f86-6522-4122-a0f4-abd69d17bb2d" with body:
    """
    {
      "name": "Nouvel objectif",
      "description": "Nouvelle description",
      "begin_at": "2022-05-15 16:30:30",
      "finish_at": "2022-05-16 16:30:30",
      "category": "reunion-dequipe",
      "mode": "online",
      "visio_url": "http://visio.fr",
      "image_url": null,
      "post_address": {
        "address": "50 rue de la villette",
        "postal_code": "69003",
        "city_name": "Lyon 3e",
        "country": "FR"
      }
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "category": {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Réunion d'équipe",
        "slug": "reunion-dequipe"
      },
      "uuid": "472d1f86-6522-4122-a0f4-abd69d17bb2d",
      "name": "Nouvel objectif",
      "slug": "2022-05-15-nouvel-objectif",
      "description": "Nouvelle description",
      "time_zone": "Europe\/Paris",
      "begin_at": "2022-05-15T16:30:30+02:00",
      "finish_at": "2022-05-16T16:30:30+02:00",
      "organizer": {
        "first_name": "Jacques",
        "last_name": "Picard"
      },
      "participants_count": 0,
      "status": "SCHEDULED",
      "capacity": null,
      "post_address": {
        "address": "50 rue de la villette",
        "postal_code": "69003",
        "city": "69003-69383",
        "city_name": "Lyon 3e",
        "country": "FR",
        "latitude": 45.7596356,
        "longitude": 4.8614359
      },
      "visio_url": "http://visio.fr",
      "mode": "online",
      "image_url": null
    }
    """

  Scenario: As a logged-in user I can not create a cause event with invalid data
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App" with scope "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/events" with body:
    """
    {
       "type":"cause",
       "causes": [
           "55056e7c-2b5f-4ef6-880e-cde0511f79b2",
           "017491f9-1953-482e-b491-20418235af1f"
       ]
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "causes: L'événement peut être lié qu'à une seule cause.\nname: Cette valeur ne doit pas être vide.\ncanonical_name: Cette valeur ne doit pas être vide.\ndescription: Cette valeur ne doit pas être vide.\nbegin_at: Cette valeur ne doit pas être vide.\nfinish_at: Cette valeur ne doit pas être vide.",
      "violations": [
        {
          "propertyPath": "causes",
          "message": "L'événement peut être lié qu'à une seule cause."
        },
        {
          "propertyPath": "name",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "propertyPath": "canonical_name",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "propertyPath": "description",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "propertyPath": "begin_at",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "propertyPath": "finish_at",
          "message": "Cette valeur ne doit pas être vide."
        }
      ]
    }
    """

  Scenario: As a logged-in user I can create a cause event
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App" with scope "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/events" with body:
    """
    {
       "type": "cause",
       "name": "My cause event",
       "description": "Description",
       "time_zone": "Europe/Paris",
       "begin_at": "2022-05-29 10:30:30",
       "finish_at": "2022-05-29 16:30:30",
       "category": "kiosque",
       "mode": "online",
       "visio_url": "http://visio.fr",
       "post_address": {
          "address": "50 rue de la villette",
          "postal_code": "69003",
          "city_name": "Lyon 3e",
          "country": "FR"
      },
       "causes": [
          "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
       ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "category": {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Kiosque",
        "slug": "kiosque"
      },
      "uuid": "@string@",
      "name": "My cause event",
      "slug": "@string@-my-cause-event",
      "description": "Description",
      "time_zone": "Europe/Paris",
      "begin_at": "2022-05-29T10:30:30+02:00",
      "finish_at": "2022-05-29T16:30:30+02:00",
      "organizer": {
        "first_name": "Gisele",
        "last_name": "Berthoux"
      },
      "participants_count": 0,
      "status": "SCHEDULED",
      "capacity": null,
      "post_address": {
        "address": "50 rue de la villette",
        "postal_code": "69003",
        "city": "69003-69383",
        "city_name": "Lyon 3e",
        "country": "FR",
        "latitude": 45.7596356,
        "longitude": 4.8614359
      },
      "visio_url": "http://visio.fr",
      "mode": "online",
      "image_url": null
    }
    """

  Scenario: As a logged-in user I can edit a cause event
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Coalition App" with scope "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/events/ef62870c-6d42-47b6-91ea-f454d473adf8" with body:
    """
    {
      "name": "Nouvel objectif",
      "description": "Nouvelle description",
      "time_zone": "Europe/Paris",
      "begin_at": "2022-05-12 10:30:30",
      "finish_at": "2022-05-12 16:30:30",
      "category": "kiosque",
      "mode": "online",
      "visio_url": "http://visio.fr",
      "post_address": {
        "address": "50 rue de la villette",
        "postal_code": "69003",
        "city_name": "Lyon 3e",
        "country": "FR"
      }
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "category": {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Kiosque",
        "slug": "kiosque"
      },
      "uuid": "ef62870c-6d42-47b6-91ea-f454d473adf8",
      "name": "Nouvel objectif",
      "slug": "2022-05-12-nouvel-objectif",
      "description": "Nouvelle description",
      "time_zone": "Europe\/Paris",
      "begin_at": "2022-05-12T10:30:30+02:00",
      "finish_at": "2022-05-12T16:30:30+02:00",
      "organizer": {
        "first_name": "Jacques",
        "last_name": "Picard"
      },
      "participants_count": 0,
      "status": "SCHEDULED",
      "capacity": null,
      "post_address": {
        "address": "50 rue de la villette",
        "postal_code": "69003",
        "city": "69003-69383",
        "city_name": "Lyon 3e",
        "country": "FR",
        "latitude": 45.7596356,
        "longitude": 4.8614359
      },
      "visio_url": "http://visio.fr",
      "mode": "online",
      "image_url": null
    }
    """

  Scenario: As a non logged-in user I see cause events
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 4,
        "items_per_page": 2,
        "count": 2,
        "current_page": 1,
        "last_page": 2
      },
      "items": [
        {
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "uuid": "ef62870c-6d42-47b6-91ea-f454d473adf8",
          "name": "Événement culturel 1 de la cause culturelle 1",
          "slug": "@string@-evenement-culturel-1-de-la-cause-culturelle-1",
          "description": "C'est un événement culturel de la cause",
          "time_zone": "Europe\/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "visio_url": null,
          "image_url": null,
          "mode": null
        },
        {
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "uuid": "19242011-7fbe-47b7-b459-0ca724d4fca2",
          "name": "Événement culturel 2 de la cause culturelle 1",
          "slug": "@string@-evenement-culturel-2-de-la-cause-culturelle-1",
          "description": "Un autre événement culturel",
          "time_zone": "Europe\/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "visio_url": null,
          "image_url": null,
          "mode": null
        }
      ]
    }
    """
