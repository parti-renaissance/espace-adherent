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
    When I send a "GET" request to "/api/ideas.json?status=FINALIZED"
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
                "firstName": "Jacques",
                "lastName": "Picard"
            },
            "published_at": "2018-12-04T10:00:00+01:00",
            "committee": null,
            "status": "FINALIZED",
            "with_committee": false,
            "votes_count": 0,
            "author_category": "ADHERENT",
            "created_at": "@string@.isDateTime()",
            "name": "Réduire le gaspillage",
            "slug": "reduire-le-gaspillage",
            "days_before_deadline": "@integer@"
        }
    ]
    """

  Scenario: As a non logged-in user I can see pending ideas
    When I send a "GET" request to "/api/ideas.json?status=PENDING"
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
                "firstName": "Jacques",
                "lastName": "Picard"
            },
            "published_at": "2018-12-01T10:00:00+01:00",
            "committee": {
                "createdAt": "2017-01-12T13:25:54+01:00",
                "name": "En Marche Paris 8",
                "slug": "en-marche-paris-8"
            },
            "status": "PENDING",
            "with_committee": true,
            "votes_count": 0,
            "author_category": "COMMITTEE",
            "created_at": "@string@.isDateTime()",
            "name": "Faire la paix",
            "slug": "faire-la-paix",
            "days_before_deadline": "@integer@"
        }
    ]
    """

  Scenario: As a non logged-in user I can filter ideas by name
    When I send a "GET" request to "/api/ideas.json?name=favoriser"
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
                "firstName": "Jacques",
                "lastName": "Picard"
            },
            "published_at": "2018-12-02T10:00:00+01:00",
            "committee": {
                "createdAt": "2017-01-12T13:25:54+01:00",
                "name": "En Marche Paris 8",
                "slug": "en-marche-paris-8"
            },
            "status": "DRAFT",
            "with_committee": true,
            "votes_count": 0,
            "author_category": "COMMITTEE",
            "created_at": "@string@.isDateTime()",
            "name": "Favoriser l'écologie",
            "slug": "favoriser-lecologie",
            "days_before_deadline": "@integer@"
        }
    ]
    """

  Scenario: As a non logged-in user I can filter ideas by theme
    When I send a "GET" request to "/api/ideas.json?theme.name=defense"
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
                "firstName": "Jacques",
                "lastName": "Picard"
            },
            "published_at": "2018-12-01T10:00:00+01:00",
            "committee": {
                "createdAt": "2017-01-12T13:25:54+01:00",
                "name": "En Marche Paris 8",
                "slug": "en-marche-paris-8"
            },
            "status": "PENDING",
            "with_committee": true,
            "votes_count": 0,
            "author_category": "COMMITTEE",
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
                "firstName": "Jacques",
                "lastName": "Picard"
            },
            "published_at": "2018-12-02T10:00:00+01:00",
            "committee": {
                "createdAt": "2017-01-12T13:25:54+01:00",
                "name": "En Marche Paris 8",
                "slug": "en-marche-paris-8"
            },
            "status": "DRAFT",
            "with_committee": true,
            "votes_count": 0,
            "author_category": "COMMITTEE",
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
                "firstName": "Benjamin",
                "lastName": "Duroc"
            },
            "published_at": "2018-12-03T10:00:00+01:00",
            "committee": null,
            "status": "DRAFT",
            "with_committee": false,
            "votes_count": 0,
            "author_category": "QG",
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
                "firstName": "Jacques",
                "lastName": "Picard"
            },
            "published_at": "2018-12-04T10:00:00+01:00",
            "committee": null,
            "status": "FINALIZED",
            "with_committee": false,
            "votes_count": 0,
            "author_category": "ADHERENT",
            "created_at": "@string@.isDateTime()",
            "name": "Réduire le gaspillage",
            "slug": "reduire-le-gaspillage",
            "days_before_deadline": "@integer@"
        }
    ]
    """

