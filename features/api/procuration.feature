@api
@renaissance
Feature:
  In order to get and manipulate proxies
  As a client of different apps
  I should be able to access proxies API

  Scenario Outline: As a referent I can get a list of proxies corresponding to my zones
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/procuration/proxies?scope=<scope>&page_size=3"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 1,
            "items_per_page": 3,
            "count": 1,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "slots": 1,
                "status": "pending",
                "requests": [],
                "email": "jane.martin@test.dev",
                "first_names": "Jane, Janine",
                "last_name": "Durand",
                "birthdate": "1991-03-14",
                "phone": null,
                "vote_zone": {
                    "uuid": "@uuid@",
                    "type": "country",
                    "code": "CH",
                    "name": "Suisse",
                    "created_at": "2020-12-04"
                },
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "post_address": {
                    "address": "12 Pilgerweg",
                    "postal_code": "8057",
                    "city": null,
                    "city_name": "Kilchberg",
                    "country": "CH",
                    "additional_address": null
                },
                "age": 33,
                "id": "@string@",
                "vote_place_name": "BDV CH 1"
            }
        ]
    }

    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
