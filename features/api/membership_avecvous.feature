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
      "source": "avecvous",
      "cgu_accepted": true,
      "recaptcha": "fake123"
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

  Scenario: As a non logged-in user I can update a contact interests
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

  Scenario: As a non logged-in user I can update a contact with postal code only and phone_contact checkbox
    Given I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/contacts/fdbc1c47-2c2e-4caf-b9d7-1212cabcd26f" with body:
    """
    {
      "interests": ["campagne_numerique"]
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
    Given I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/contacts/fdbc1c47-2c2e-4caf-b9d7-1212cabcd26f" with body:
    """
    {
      "last_name": "Doe",
      "birthdate": "1975-01-01",
      "phone": "+33 0611223344",
      "post_address": {
          "postal_code": "69001"
      },
      "phone_contact": true,
      "cgu_accepted": true
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
    And I should have 0 email

  Scenario: As a non logged-in user I can update a contact with full address and all contact checkboxes
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
    Then I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/contacts/fdbc1c47-2c2e-4caf-b9d7-1212cabcd26f" with body:
    """
    {
      "last_name": "Doe",
      "birthdate": "1975-01-01",
      "phone": "+33 0611223344",
      "post_address": {
          "address": "6 rue neyret",
          "postal_code": "69001",
          "city_name": "lyon 1er",
          "country": "FR"
      },
      "email_contact": false,
      "phone_contact": true,
      "cgu_accepted": true
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
    And I should have 1 email "AvecVousUserAccountConfirmationMessage" for "remi@avecvous.dev" with payload:
    """
    {
        "template_name": "avec-vous-user-account-confirmation",
        "template_content": [],
        "message": {
            "subject": "🚀 Activez votre compte Je m’engage",
            "from_email": "ne-pas-repondre@je-mengage.fr",
            "from_name": "Emmanuel Macron avec vous",
            "global_merge_vars": [
                {
                    "name": "create_password_link",
                    "content": "http://login.jemengage.code/changer-mot-de-passe/@string@/@string@"
                }
            ],
            "to": [
                {
                    "email": "remi@avecvous.dev",
                    "type": "to",
                    "name": "Rémi Doe"
                }
            ]
        }
    }
    """

  Scenario: As a non logged-in user I can create a contact with the same email address as an existing account
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/contacts" with body:
    """
    {
      "first_name": "Carl",
      "email_address": "carl999@example.fr",
      "source": "avecvous",
      "cgu_accepted": true,
      "recaptcha": "fake123"
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

    Given I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/contacts/fdbc1c47-2c2e-4caf-b9d7-1212cabcd26f" with body:
    """
    {
      "interests": ["campagne_numerique"]
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
    Given I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/contacts/fdbc1c47-2c2e-4caf-b9d7-1212cabcd26f" with body:
    """
    {
      "last_name": "Doe",
      "birthdate": "1975-01-01",
      "phone": "+33 0611223344",
      "post_address": {
          "postal_code": "69001"
      },
      "phone_contact": true,
      "cgu_accepted": true
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
    And I should have 0 email

  Scenario: As a non logged-in user I can not create a contact with invalid captcha
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/contacts" with body:
    """
    {
      "first_name": "Rémi",
      "email_address": "new@avecvous.dev",
      "source": "avecvous",
      "cgu_accepted": true,
      "recaptcha": "wrong_answer"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "recaptcha: Merci de confirmer le captcha avant de continuer.",
      "violations": [
        {
          "propertyPath": "recaptcha",
          "message": "Merci de confirmer le captcha avant de continuer."
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can not create a contact with no cgu accepted
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/contacts" with body:
    """
    {
      "first_name": "Rémi",
      "email_address": "new@avecvous.dev",
      "source": "avecvous",
      "cgu_accepted": false,
      "recaptcha": "fake123"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "cgu_accepted: Veuillez accepter les CGU.",
      "violations": [
        {
          "propertyPath": "cgu_accepted",
          "message": "Veuillez accepter les CGU."
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can not create a contact with an invalid email address
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/contacts" with body:
    """
    {
      "first_name": "Rémi",
      "email_address": "invalid_email",
      "source": "avecvous",
      "cgu_accepted": true,
      "recaptcha": "fake123"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "email_address: Ceci n'est pas une adresse e-mail valide.",
      "violations": [
        {
          "propertyPath": "email_address",
          "message": "Ceci n'est pas une adresse e-mail valide."
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can not create a contact with an already existing email address
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/contacts" with body:
    """
    {
      "first_name": "Rémi",
      "email_address": "remi@avecvous.dev",
      "source": "avecvous",
      "cgu_accepted": true,
      "recaptcha": "fake123"
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

  Scenario: As a non logged-in user I can not create a contact with no first name
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/contacts" with body:
    """
    {
      "first_name": null,
      "email_address": "new@avecvous.dev",
      "source": "avecvous",
      "cgu_accepted": true,
      "recaptcha": "fake123"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "first_name: Cette valeur ne doit pas être vide.",
      "violations": [
        {
          "propertyPath": "first_name",
          "message": "Cette valeur ne doit pas être vide."
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can not create a contact with a too short first name
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/contacts" with body:
    """
    {
      "first_name": "R",
      "email_address": "new@avecvous.dev",
      "source": "avecvous",
      "cgu_accepted": true,
      "recaptcha": "fake123"
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

  Scenario: As a non logged-in user I can not create a contact with a too long first name
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/contacts" with body:
    """
    {
      "first_name": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
      "email_address": "new@avecvous.dev",
      "source": "avecvous",
      "cgu_accepted": true,
      "recaptcha": "fake123"
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

  Scenario: As a non logged-in user I can not create a contact with no source
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/contacts" with body:
    """
    {
      "first_name": "Rémi",
      "email_address": "new@avecvous.dev",
      "source": null,
      "cgu_accepted": true,
      "recaptcha": "fake123"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "source: Cette valeur ne doit pas être vide.",
      "violations": [
        {
          "propertyPath": "source",
          "message": "Cette valeur ne doit pas être vide."
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can not create a contact with an invalid source
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/contacts" with body:
    """
    {
      "first_name": "Rémi",
      "email_address": "new@avecvous.dev",
      "source": "unknown_source",
      "cgu_accepted": true,
      "recaptcha": "fake123"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "source: Cette valeur n'est pas valide.",
      "violations": [
        {
          "propertyPath": "source",
          "message": "Cette valeur n'est pas valide."
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can not update a contact with invalid interests
    Given I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/contacts/fdbc1c47-2c2e-4caf-b9d7-1212cabcd26f" with body:
    """
    {
      "interests": ["invalid_interest_1", "invalid_interest_2"]
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "interests: Une ou plusieurs des valeurs soumises sont invalides.",
      "violations": [
        {
          "propertyPath": "interests",
          "message": "Une ou plusieurs des valeurs soumises sont invalides."
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can not update a contact with no cgu accepted
    Given I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/contacts/fdbc1c47-2c2e-4caf-b9d7-1212cabcd26f" with body:
    """
    {
      "cgu_accepted": false
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "cgu_accepted: Veuillez accepter les CGU.",
      "violations": [
        {
          "propertyPath": "cgu_accepted",
          "message": "Veuillez accepter les CGU."
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can not update a contact with an invalid phone number
    Given I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/contacts/fdbc1c47-2c2e-4caf-b9d7-1212cabcd26f" with body:
    """
    {
      "phone": "+00 21 35 68"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "phone: Cette valeur n'est pas un numéro de téléphone valide.",
      "violations": [
        {
          "propertyPath": "phone",
          "message": "Cette valeur n'est pas un numéro de téléphone valide."
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can not update a contact with an invalid birth date
    Given I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/contacts/fdbc1c47-2c2e-4caf-b9d7-1212cabcd26f" with body:
    """
    {
      "birthdate": "2099-01-02"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "birthdate: Cette valeur doit être comprise entre @string@ à @string@ et @string@ à @string@.",
      "violations": [
        {
          "propertyPath": "birthdate",
          "message": "Cette valeur doit être comprise entre @string@ à @string@ et @string@ à @string@."
        }
      ]
    }
    """
