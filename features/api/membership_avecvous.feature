@api
Feature:
  In order to create a contact
  As a non logged-in user
  I should be able to access the contacts API

  Scenario: As a non logged-in user I can create a contact
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/contacts" with body:
    """
    {
      "first_name": "Rémi",
      "email_address": "new-user@avecvous.dev",
      "source": "avecvous"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "uuid": "@uuid@"
    }
    """

  Scenario: As a non logged-in user I can get a contact information with its UUID
    Given I add "Accept" header equal to "application/json"
    And I send a "GET" request to "/api/contacts/fdbc1c47-2c2e-4caf-b9d7-1212cabcd26f"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "uuid": "@uuid@",
      "email_address": "remi@avecvous.dev",
      "first_name": "Rémi"
    }
    """

  Scenario: As a non logged-in user I can update a contact information with its UUID
    # interests only (step 2)
    Given I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/contacts/fdbc1c47-2c2e-4caf-b9d7-1212cabcd26f" with body:
    """
    {
      "interests": ["action_terrain", "campagne_numerique"]
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "uuid": "@uuid@"
    }
    """

    # general informations (step 3)
    Given I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/contacts/fdbc1c47-2c2e-4caf-b9d7-1212cabcd26f" with body:
    """
    {
      "last_name": "Doe",
      "birthdate": "1975-01-01",
      "phone": "0611223344"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "uuid": "@uuid@"
    }
    """

  Scenario: As a non logged-in user I can not create an invalid contact
    # Already existing email address
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/contacts" with body:
    """
    {
      "first_name": "Rémi",
      "email_address": "remi@avecvous.dev",
      "source": "avecvous"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "email_address: Cette valeur est déjà utilisée.",
      "violations": [
        {
          "propertyPath": "email_address",
          "message": "Cette valeur est déjà utilisée."
        }
      ]
    }
    """

    # Invalid first name (1)
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/contacts" with body:
    """
    {
      "first_name": "R",
      "email_address": "new@avecvous.dev",
      "source": "avecvous"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "first_name: Votre prénom doit comporter au moins 2 caractères.",
      "violations": [
        {
          "propertyPath": "first_name",
          "message": "Votre prénom doit comporter au moins 2 caractères."
        }
      ]
    }
    """

    # Invalid first name (2)
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/contacts" with body:
    """
    {
      "first_name": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
      "email_address": "new@avecvous.dev",
      "source": "avecvous"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "first_name: Votre prénom ne peut pas dépasser 50 caractères.",
      "violations": [
        {
          "propertyPath": "first_name",
          "message": "Votre prénom ne peut pas dépasser 50 caractères."
        }
      ]
    }
    """