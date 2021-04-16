@api
Feature:
  In order to see coalition events
  As a non logged-in user
  I should be able to access API coalition events

  Background:
    Given the following fixtures are loaded:
      | LoadCoalitionData       |
      | LoadCoalitionEventData  |

  Scenario: As a non logged-in user I see coalition events
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 6,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 3
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
                "mode": null,
                "capacity": null,
                "uuid": "472d1f86-6522-4122-a0f4-abd69d17bb2d",
                "post_address": {
                    "address": "60 avenue des Champs-Élysées",
                    "postal_code": "75008",
                    "city": "75008-75108",
                    "city_name": "Paris 8e",
                    "country": "FR",
                    "latitude": 48.870506,
                    "longitude": 2.313243
                }
            },
            {
                "category": {
                    "event_group_category": {
                        "name": "\u00e9v\u00e9nement",
                        "slug": "evenement"
                    },
                    "name": "Atelier du programme",
                    "slug": "atelier-du-programme"
                },
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
                "mode": null,
                "capacity": null,
                "uuid": "462d7faf-09d2-4679-989e-287929f50be8",
                "post_address": {
                    "address": "60 avenue des Champs-Élysées",
                    "postal_code": "75008",
                    "city": "75008-75108",
                    "city_name": "Paris 8e",
                    "country": "FR",
                    "latitude": 48.870506,
                    "longitude": 2.313243
                }
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
            "total_items": 6,
            "items_per_page": 2,
            "count": 2,
            "current_page": 2,
            "last_page": 3
        },
        "items": [
            {
                "category": {
                    "name": "Marche",
                    "slug": "marche",
                    "event_group_category": {
                        "name": "événement",
                        "slug": "evenement"
                    }
                },
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
                "mode": null,
                "capacity": null,
                "uuid": "d16f0ab4-292b-4698-847c-005f58ec3119",
                "post_address": {
                    "address": "12 Pilgerweg",
                    "postal_code": "8802",
                    "city": null,
                    "city_name": "Kilchberg",
                    "country": "CH",
                    "latitude": 47.321568,
                    "longitude": 8.549969
                }
            },
            {
                "category": {
                    "name": "Atelier du programme",
                    "slug": "atelier-du-programme",
                    "event_group_category": {
                        "name": "événement",
                        "slug": "evenement"
                    }
                },
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
                "mode": null,
                "capacity": null,
                "uuid": "d7e72e52-b81a-4adf-b022-d547672ce095",
                "post_address": {
                    "address": "226 W 52nd St",
                    "postal_code": "10019",
                    "city": null,
                    "city_name": "New York",
                    "country": "US",
                    "latitude": 40.762527,
                    "longitude": -73.985992
                }
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
            "total_items": 6,
            "items_per_page": 5,
            "count": 1,
            "current_page": 2,
            "last_page": 2
        },
        "items": [
            {
                "category": {
                    "name": "Événement innovant",
                    "slug": "evenement-innovant",
                    "event_group_category": {
                        "name": "événement",
                        "slug": "evenement"
                    }
                },
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
                "mode": null,
                "capacity": null,
                "uuid": "a9d45d86-0333-4767-9853-6e9e7268d778",
                "post_address": {
                    "address": "60 avenue des Champs-Élysées",
                    "postal_code": "75008",
                    "city": "75008-75108",
                    "city_name": "Paris 8e",
                    "country": "FR",
                    "latitude": 48.870506,
                    "longitude": 2.313243
                }
            }
        ]
    }
    """
