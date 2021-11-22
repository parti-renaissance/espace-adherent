@api
Feature:
  In order to see, create, edit and delete adherent messages
  As a logged-in user
  I should be able to access API adherent messages

  Scenario: As a logged-in user I can not update adherent message filter with not my segment
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f/filter" with body:
    """
    {
      "segment": "f6c36dd7-0517-4caf-ba6f-ec6822f2ec12"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "type":"https://symfony.com/errors/validation",
       "title":"Validation Failed",
       "detail":"segment: Le segment n'est pas autoris\u00e9",
       "violations":[
          {
             "propertyPath":"segment",
             "title":"Le segment n'est pas autorisé",
             "parameters":[
                
             ]
          }
       ]
    }
    """

  Scenario: As a logged-in user I can update adherent message filter with segment
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f/filter" with body:
    """
    {
      "segment": "830d230f-67fb-4217-9986-1a3ed7d3d5e7"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      "OK"
    """

  Scenario Outline: As a logged-in (delegated) referent I can retrive my messages
    Given I am logged with "<user>" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/adherent_messages?scope=<scope>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
        "metadata": {
            "total_items": 101,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 51
        },
        "items": [
            {
                "uuid": "@uuid@",
                "label": "@string@",
                "subject": "@string@",
                "status": "draft",
                "recipient_count": 0,
                "synchronized": false,
                "from_name": "Referent Referent | La République En Marche !",
                "statistics": {
                    "click_rate": 0,
                    "clicks": 0,
                    "open_rate": 0,
                    "opens": 0,
                    "sent": 0,
                    "unsubscribe": 0,
                    "unsubscribe_rate": 0
                },
                "zones": [
                    {
                      "code": "13",
                      "name": "Bouches-du-Rhône",
                      "postal_code": [],
                      "type": "department",
                      "uuid": "e3f01553-906e-11eb-a875-0242ac150002"
                    }
                ]
            },
            {
                "uuid": "@uuid@",
                "label": "@string@",
                "subject": "@string@",
                "status": "draft",
                "recipient_count": 0,
                "synchronized": true,
                "from_name": "Referent Referent | La République En Marche !",
                "statistics": {
                    "click_rate": 0,
                    "clicks": 0,
                    "open_rate": 0,
                    "opens": 0,
                    "sent": 0,
                    "unsubscribe": 0,
                    "unsubscribe_rate": 0
                },
                "zones": [
                    {
                      "code": "13",
                      "name": "Bouches-du-Rhône",
                      "postal_code": [],
                      "type": "department",
                      "uuid": "e3f01553-906e-11eb-a875-0242ac150002"
                    }
                ]
            }
        ]
    }
    """

    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      # senateur@en-marche-dev.fr has a delegated access from referent@en-marche-dev.fr and should see the same messages
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
