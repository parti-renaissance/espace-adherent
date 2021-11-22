@api
Feature:
  In order to see coalitions
  As a non logged-in user
  I should be able to access API coalitions

  Scenario: As a non logged-in user I can see first page of active coalitions
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
      {
        "name": "Culture",
        "description": "Description de la coalition 'Culture'",
        "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
        "followers_count": 4,
        "cause_followers_count": 5,
        "youtube_id": "yOuTUbe_",
        "image_url": "http://test.enmarche.code/assets/images/coalitions/5cd98b474069115622f2f5816014d204.png"
      },
      {
        "name": "Démocratie",
        "description": "Description de la coalition 'Démocratie'",
        "uuid": "09d700f8-8813-4c3c-9bee-ff18d2051bba",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Économie",
        "description": "Description de la coalition 'Économie'",
        "uuid": "fc7fd104-71e5-4399-a874-f8fe752f846b",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Éducation",
        "description": "Description de la coalition 'Éducation'",
        "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Égalité H/F",
        "description": "Description de la coalition 'Égalité H/F'",
        "uuid": "eaa129cf-fcbd-4d7d-8cfa-2268d08527ec",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Europe",
        "description": "Description de la coalition 'Europe'",
        "uuid": "0654ae09-ea1a-4142-bea4-2e82dc5da998",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Inclusion",
        "description": "Description de la coalition 'Inclusion'",
        "uuid": "81e4a680-7ce0-4038-b8fe-6bf755db4c5b",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "International",
        "description": "Description de la coalition 'International'",
        "uuid": "429fa3a9-8288-4de5-8ba5-366e6afa366b",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Justice",
        "description": "Description de la coalition 'Justice'",
        "uuid": "5b8db218-4da6-4f7f-a53e-29a7a349d45c",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Numérique",
        "description": "Description de la coalition 'Numérique'",
        "uuid": "5e500dbe-5227-4b83-8a9c-8c36f3f25265",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Puissance publique",
        "description": "Description de la coalition 'Puissance publique'",
        "uuid": "bd64b020-cb5b-4dd9-a478-1a1fac619ee1",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "République",
        "description": "Description de la coalition 'République'",
        "uuid": "1cbcf3cd-d0e4-4bd7-8d33-a2fa3320791d",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Ruralité",
        "description": "Description de la coalition 'Ruralité'",
        "uuid": "fd0990f9-0148-4fed-84e5-4deee0af2d45",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Santé",
        "description": "Description de la coalition 'Santé'",
        "uuid": "49202478-544e-4b00-9b90-f2945804c920",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Sécurité",
        "description": "Description de la coalition 'Sécurité'",
        "uuid": "4b2a1335-362c-4611-bf82-d6c1216db389",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Solidarités",
        "description": "Description de la coalition 'Solidarités'",
        "uuid": "9a552cda-2d7a-41b4-aaf0-1bcab14b76f8",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Transition écologique",
        "description": "Description de la coalition 'Transition écologique'",
        "uuid": "5ce3b33c-75d6-4923-bffb-7385e7d8e15a",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Travail",
        "description": "Description de la coalition 'Travail'",
        "uuid": "8b4a9add-c7cd-43a0-b4da-8eab51d8f02b",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      },
      {
        "name": "Villes et quartiers",
        "description": "Description de la coalition 'Villes et quartiers'",
        "uuid": "0abb99ea-c6fe-473b-bf88-f31f887a3233",
        "followers_count": 0,
        "cause_followers_count": 0,
        "youtube_id": null,
        "image_url": null
      }
    ]
    """

  Scenario: As a non logged-in user I can get one coalition by uuid
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "name": "Culture",
      "description": "Description de la coalition 'Culture'",
      "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
      "followers_count": 4,
      "cause_followers_count": 5,
      "youtube_id": "yOuTUbe_",
      "image_url": "http://test.enmarche.code/assets/images/coalitions/5cd98b474069115622f2f5816014d204.png"
    }
    """

  Scenario: As a non logged-in user I can not get an inactive coalition
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions/a82ee43a-c68d-4ed2-9cd5-56eb1f72d9c8"
    Then the response status code should be 404

  Scenario Outline: As a non logged-in user I can not follow/unfollow a coalition
    Given I add "Accept" header equal to "application/json"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 401
    Examples:
      | method  | url                                                               |
      | PUT     | /api/v3/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/follower  |
      | DELETE  | /api/v3/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/follower  |

  Scenario: As a logged-in user I can follow a coalition
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App"
    When I send a "PUT" request to "/api/v3/coalitions/fff11d8d-5cb5-4075-b594-fea265438d65/follower"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "uuid": "@string@"
    }
    """

  Scenario: As a logged-in user I can unfollow a coalition
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App"
    When I send a "DELETE" request to "/api/v3/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/follower"
    Then the response status code should be 204

  Scenario: As a logged-in user I can get followed coalitions
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App"
    When I send a "GET" request to "/api/v3/coalitions/followed"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
      "d5289058-2a35-4cf0-8f2f-a683d97d8315"
    ]
    """
