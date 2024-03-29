@api
@renaissance
Feature:
  In order to manage adherent formations
  As a logged-in user
  I should be able to access adherent formations API

  Scenario Outline: As a user granted with local scope, I can create an adherent formation in a zone I am manager of
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    And I send a "POST" request to "/api/v3/formations?scope=<scope>" with body:
    """
    {
        "title": "New formation",
        "description": "New formation description",
        "content_type": "link",
        "link": "https://renaissance.code/",
        "published": true,
        "zone": "e3efe5c5-906e-11eb-a875-0242ac150002",
        "position": 4
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "uuid": "@uuid@",
        "title": "New formation",
        "description": "@string@",
        "content_type": "link",
        "file_path": null,
        "link": "https://renaissance.code/",
        "published": true,
        "print_count": 0,
        "visibility": "local",
        "zone": {
            "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
            "code": "77",
            "name": "Seine-et-Marne"
        },
        "position": 4
    }
    """
    Examples:
        | user                      | scope                                          |
        | referent@en-marche-dev.fr | referent                                       |
        | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can get adherent formations in a zone I am manager of
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    And I send a "GET" request to "/api/v3/formations?scope=<scope>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 2,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "uuid": "ebdbafa2-c0b0-40ff-adbd-745f48f48c42",
                "title": "Première formation du 77",
                "description": "@string@",
                "content_type": "file",
                "file_path": "@string@.isUrl()",
                "link": null,
                "published": true,
                "print_count": 0,
                "visibility": "local",
                "zone": {
                    "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                    "code": "77",
                    "name": "Seine-et-Marne"
                },
                "position": 0
            },
            {
                "uuid": "366c1da2-f833-4172-883a-c10a41588766",
                "title": "Deuxième formation du 77",
                "description": "@string@",
                "content_type": "link",
                "file_path": null,
                "link": "http://renaissance.code/",
                "published": true,
                "print_count": 0,
                "visibility": "local",
                "zone": {
                    "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                    "code": "77",
                    "name": "Seine-et-Marne"
                },
                "position": 0
            }
        ]
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a user granted with local scope, I can delete adherent formations in my managed zone
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "DELETE" request to "/api/v3/formations/ebdbafa2-c0b0-40ff-adbd-745f48f48c42?scope=referent"
        Then the response status code should be 204
