@api
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
        "title": "New formation",
        "description": "New formation description",
        "content_type": "link",
        "file": null,
        "link": "https://renaissance.code/",
        "published": true,
        "valid": false,
        "print_count": 0,
        "created_at": "@string@.isDateTime()",
        "updated_at": "@string@.isDateTime()",
        "visibility": "local",
        "zone": {
            "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
            "code": "77",
            "name": "Seine-et-Marne",
            "created_at": "@string@.isDateTime()",
            "updated_at": "@string@.isDateTime()"
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
                "title": "Première formation du 77",
                "description": "@string@",
                "content_type": "file",
                "file": {
                    "title": "Formation PDF",
                    "slug": "formation-pdf-2",
                    "path": "files/adherent_formations/536d2de6-8275-42ff-b077-961cd16798d5.pdf",
                    "extension": "pdf"
                },
                "link": null,
                "published": true,
                "valid": false,
                "print_count": 0,
                "created_at": "@string@.isDateTime()",
                "visibility": "local",
                "zone": {
                    "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                    "code": "77",
                    "name": "Seine-et-Marne",
                    "created_at": "@string@.isDateTime()"
                },
                "position": 0
            },
            {
                "title": "Deuxième formation du 77",
                "description": "@string@",
                "content_type": "link",
                "file": null,
                "link": "http://renaissance.code/",
                "published": true,
                "valid": false,
                "print_count": 0,
                "created_at": "@string@.isDateTime()",
                "visibility": "local",
                "zone": {
                    "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                    "code": "77",
                    "name": "Seine-et-Marne",
                    "created_at": "@string@.isDateTime()"
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
