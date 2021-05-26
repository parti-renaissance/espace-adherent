@api
Feature:
  In order to create a light profile
  As a non logged-in user
  I should be able to access API membership

  Scenario: As a non logged-in user I can create a light profile
    Given I send a "POST" request to "/api/membership" with body:
    """
    {
      "first_name": "Rémi",
      "email_address": "new-light-user@en-marche-dev.fr",
      "zone": "e3f19d3c-906e-11eb-a875-0242ac150002",
      "source": "coalitions",
      "coalition_subscription": false,
      "cause_subscription": true
    }
    """
    Then the response status code should be 201
    And I should have 1 email "CoalitionUserAccountConfirmationMessage" for "new-light-user@en-marche-dev.fr" with payload:
    """
    {
        "template_name": "coalition-user-account-confirmation",
        "template_content": [],
        "message": {
            "subject": "Confirmez votre adresse email",
            "from_email": "contact@pourunecause.fr",
            "global_merge_vars": [
                {
                    "name": "first_name",
                    "content": "R\u00e9mi"
                },
                {
                    "name": "create_password_link",
                    "content": "http:\/\/coalitions.code\/confirmation\/@uuid@\/@string@"
                }
            ],
            "from_name": "Pour une cause",
            "to": [
                {
                    "email": "new-light-user@en-marche-dev.fr",
                    "type": "to",
                    "name": "R\u00e9mi "
                }
            ]
        }
    }
    """

  Scenario: As a non logged-in user I can not create a light profile with existing email address
    Given I send a "POST" request to "/api/membership" with body:
    """
    {
      "first_name": "Rémi",
      "email_address": "carl999@example.fr",
      "zone": "e3f19d3c-906e-11eb-a875-0242ac150002",
      "source": "coalitions"
    }
    """
    Then the response status code should be 400
    And the JSON should be a superset of:
    """
    {
      "violations": [
        {
          "propertyPath": "email_address",
          "title": "Cette adresse e-mail existe déjà.",
          "parameters": {
            "{{ email }}": "carl999@example.fr"
          }
        }
      ]
    }
    """
