@api
Feature:
  In order to see ideas
  As a non logged-in user
  I should be able to access API Ideas Workshop

  Background:
    Given the following fixtures are loaded:
      | LoadIdeaData              |
      | LoadIdeaThreadCommentData |
      | LoadIdeaVoteData          |

  Scenario: As a non logged-in user I can see published ideas
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas?status=FINALIZED"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
        {
            "theme": {
                "name": "Armées et défense",
                "slug": "armees-et-defense"
            },
            "category": {
                "name": "Echelle Européenne",
                "enabled": true
            },
            "needs": [],
            "author": {
                "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                "firstName": "Jacques",
                "lastName": "Picard"
            },
            "published_at": "2018-12-04T10:00:00+01:00",
            "committee": null,
            "status": "FINALIZED",
            "with_committee": false,
            "votes_count": 0,
            "author_category": "ADHERENT",
            "description": "In nec risus vitae lectus luctus fringilla. Suspendisse vitae enim interdum, maximus justo a, elementum lectus. Mauris et augue et magna imperdiet eleifend a nec tortor.",
            "uuid": "c14937d6-fd42-465c-8419-ced37f3e6194",
            "created_at": "@string@.isDateTime()",
            "name": "Réduire le gaspillage",
            "slug": "reduire-le-gaspillage",
            "days_before_deadline": "@integer@"
        }
    ]
    """

  Scenario: As a non logged-in user I can see pending ideas
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas?status=PENDING"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
        {
            "theme": {
                "name": "Armées et défense",
                "slug": "armees-et-defense"
            },
            "category": {
                "name": "Echelle Européenne",
                "enabled": true
            },
            "needs": [
                {
                    "name": "Juridique",
                    "enabled": true
                }
            ],
            "author": {
                "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                "firstName": "Jacques",
                "lastName": "Picard"
            },
            "published_at": "2018-12-01T10:00:00+01:00",
            "committee": {
                "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818",
                "createdAt": "2017-01-12T13:25:54+01:00",
                "name": "En Marche Paris 8",
                "slug": "en-marche-paris-8"
            },
            "status": "PENDING",
            "with_committee": true,
            "votes_count": 21,
            "author_category": "COMMITTEE",
            "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus convallis dolor, id ultricies lorem lobortis et. Vivamus bibendum leo et ullamcorper dapibus.",
            "uuid": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
            "created_at": "@string@.isDateTime()",
            "name": "Faire la paix",
            "slug": "faire-la-paix",
            "days_before_deadline": "@integer@"
        }
    ]
    """

  Scenario: As a non logged-in user I can filter ideas by name
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas?name=favoriser"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
        {
            "theme": {
                "name": "Armées et défense",
                "slug": "armees-et-defense"
            },
            "category": {
                "name": "Echelle Européenne",
                "enabled": true
            },
            "needs": [],
            "author": {
                "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                "firstName": "Jacques",
                "lastName": "Picard"
            },
            "published_at": "2018-12-02T10:00:00+01:00",
            "committee": {
                "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818",
                "createdAt": "2017-01-12T13:25:54+01:00",
                "name": "En Marche Paris 8",
                "slug": "en-marche-paris-8"
            },
            "status": "DRAFT",
            "with_committee": true,
            "votes_count": 21,
            "author_category": "COMMITTEE",
            "description": "Mauris posuere eros eget nunc dapibus ornare. Vestibulum dolor eros, facilisis in venenatis eu, tristique a sapien.",
            "uuid": "3b1ea810-115f-4b2c-944d-34a55d7b7e4d",
            "created_at": "@string@.isDateTime()",
            "name": "Favoriser l'écologie",
            "slug": "favoriser-lecologie",
            "days_before_deadline": "@integer@"
        }
    ]
    """

  Scenario: As a non logged-in user I can filter ideas by theme
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas?theme.name=defense"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
        {
            "theme": {
                "name": "Armées et défense",
                "slug": "armees-et-defense"
            },
            "category": {
                "name": "Echelle Européenne",
                "enabled": true
            },
            "needs": [
                {
                    "name": "Juridique",
                    "enabled": true
                }
            ],
            "author": {
                "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                "firstName": "Jacques",
                "lastName": "Picard"
            },
            "published_at": "2018-12-01T10:00:00+01:00",
            "committee": {
                "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818",
                "createdAt": "2017-01-12T13:25:54+01:00",
                "name": "En Marche Paris 8",
                "slug": "en-marche-paris-8"
            },
            "status": "PENDING",
            "with_committee": true,
            "votes_count": 21,
            "author_category": "COMMITTEE",
            "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus convallis dolor, id ultricies lorem lobortis et. Vivamus bibendum leo et ullamcorper dapibus.",
            "uuid": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
            "created_at": "@string@.isDateTime()",
            "name": "Faire la paix",
            "slug": "faire-la-paix",
            "days_before_deadline": "@integer@"
        },
        {
            "theme": {
                "name": "Armées et défense",
                "slug": "armees-et-defense"
            },
            "category": {
                "name": "Echelle Européenne",
                "enabled": true
            },
            "needs": [],
            "author": {
                "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                "firstName": "Jacques",
                "lastName": "Picard"
            },
            "published_at": "2018-12-02T10:00:00+01:00",
            "committee": {
                "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818",
                "createdAt": "2017-01-12T13:25:54+01:00",
                "name": "En Marche Paris 8",
                "slug": "en-marche-paris-8"
            },
            "status": "DRAFT",
            "with_committee": true,
            "votes_count": 21,
            "author_category": "COMMITTEE",
            "description": "Mauris posuere eros eget nunc dapibus ornare. Vestibulum dolor eros, facilisis in venenatis eu, tristique a sapien.",
            "uuid": "3b1ea810-115f-4b2c-944d-34a55d7b7e4d",
            "created_at": "@string@.isDateTime()",
            "name": "Favoriser l'écologie",
            "slug": "favoriser-lecologie",
            "days_before_deadline": "@integer@"
        },
        {
            "theme": {
                "name": "Armées et défense",
                "slug": "armees-et-defense"
            },
            "category": {
                "name": "Echelle Européenne",
                "enabled": true
            },
            "needs": [],
            "author": {
                "uuid": "acc73b03-9743-47d8-99db-5a6c6f55ad67",
                "firstName": "Benjamin",
                "lastName": "Duroc"
            },
            "published_at": "2018-12-03T10:00:00+01:00",
            "committee": null,
            "status": "DRAFT",
            "with_committee": false,
            "votes_count": 21,
            "author_category": "QG",
            "description": "Nam laoreet eros diam, vitae hendrerit libero interdum nec. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.",
            "uuid": "aa093ce6-8b20-4d86-bfbc-91a73fe47285",
            "created_at": "@string@.isDateTime()",
            "name": "Aider les gens",
            "slug": "aider-les-gens",
            "days_before_deadline": "@integer@"
        },
        {
            "theme": {
                "name": "Armées et défense",
                "slug": "armees-et-defense"
            },
            "category": {
                "name": "Echelle Européenne",
                "enabled": true
            },
            "needs": [],
            "author": {
                "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                "firstName": "Jacques",
                "lastName": "Picard"
            },
            "published_at": "2018-12-04T10:00:00+01:00",
            "committee": null,
            "status": "FINALIZED",
            "with_committee": false,
            "votes_count": 0,
            "author_category": "ADHERENT",
            "description": "In nec risus vitae lectus luctus fringilla. Suspendisse vitae enim interdum, maximus justo a, elementum lectus. Mauris et augue et magna imperdiet eleifend a nec tortor.",
            "uuid": "c14937d6-fd42-465c-8419-ced37f3e6194",
            "created_at": "@string@.isDateTime()",
            "name": "Réduire le gaspillage",
            "slug": "reduire-le-gaspillage",
            "days_before_deadline": "@integer@"
        }
    ]
    """

  Scenario: As a non logged-in user I can get information about one idea
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas/e4ac3efc-b539-40ac-9417-b60df432bdc5"
    Then the response status code should be 200
    And the response should be in JSON
    """
    [
        {
        "answers": [
            {
                "content": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce aliquet, mi condimentum venenatis vestibulum, arcu neque feugiat massa, at pharetra velit sapien et elit. Sed vitae hendrerit nulla. Vivamus consectetur magna at tincidunt maximus. Aenean dictum metus vel tellus posuere venenatis.",
                "question": {
                    "id": 1
                }
            },
            {
                "content": "Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex. Curabitur at eros et ex faucibus fringilla sed vel velit.",
                "question": {
                    "id": 1
                }
            },
            {
                "content": "Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex. Curabitur at eros et ex faucibus fringilla sed vel velit.",
                "question": {
                    "id": 3
                }
            },
            {
                "content": "Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex. Curabitur at eros et ex faucibus fringilla sed vel velit.",
                "question": {
                    "id": 4
                }
            },
            {
                "content": "Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex. Curabitur at eros et ex faucibus fringilla sed vel velit.",
                "question": {
                    "id": 5
                }
            },
            {
                "content": "Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex. Curabitur at eros et ex faucibus fringilla sed vel velit.",
                "question": {
                    "id": 6
                }
            },
            {
                "content": "Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex. Curabitur at eros et ex faucibus fringilla sed vel velit.",
                "question": {
                    "id": 7
                }
            },
            {
                "content": "Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex. Curabitur at eros et ex faucibus fringilla sed vel velit.",
                "question": {
                    "id": 8
                }
            }
        ]
    }
    """
