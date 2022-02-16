@api
Feature:
  In order to create a JeMengage user
  As a non logged-in user
  I should be able to access API membership

  Scenario: As a non logged-in user I cannot create a JeMengage user with empty and wrong data
    Given I send a "POST" request to "/api/membership?source=jemengage" with body:
    """
    {
      "phone": "0"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "type": "https://symfony.com/errors/validation",
        "title": "Validation Failed",
        "detail": "last_name: Cette valeur ne doit pas être vide.\ngender: Veuillez renseigner un genre.\nbirthdate: Vous devez spécifier votre date de naissance.\nphone: Cette valeur n'est pas un numéro de téléphone valide.\nemail_address: Cette valeur ne doit pas être vide.\ncgu_accepted: Vous devez accepter la charte.",
        "violations": [
            {
                "propertyPath": "last_name",
                "title": "Cette valeur ne doit pas être vide.",
                "parameters": {
                    "{{ value }}": "null"
                },
                "type": "@string@"
            },
            {
                "propertyPath": "gender",
                "title": "Veuillez renseigner un genre.",
                "parameters": {
                    "{{ value }}": "null"
                },
                "type": "@string@"
            },
            {
                "propertyPath": "birthdate",
                "title": "Vous devez spécifier votre date de naissance.",
                "parameters": {
                    "{{ value }}": "null"
                },
                "type": "@string@"
            },
            {
                "propertyPath": "phone",
                "title": "Cette valeur n'est pas un numéro de téléphone valide.",
                "parameters": {
                    "{{ types }}": "phone number",
                    "{{ value }}": "@string@"
                },
                "type": "@string@"
            },
            {
                "propertyPath": "email_address",
                "title": "Cette valeur ne doit pas être vide.",
                "parameters": {
                    "{{ value }}": "\"\""
                },
                "type": "@string@"
            },
            {
                "propertyPath": "cgu_accepted",
                "title": "Vous devez accepter la charte.",
                "parameters": {
                    "{{ value }}": "false"
                },
                "type": "@string@"
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can create a JeMengage user
    Given I send a "POST" request to "/api/membership?source=jemengage" with body:
    """
    {
      "email_address": "new-user@en-marche-dev.fr",
      "first_name": "Jules",
      "last_name": "Fullstack",
      "gender": "male",
      "birthdate": "1975-01-01",
      "nationality": "FR",
      "phone": "0611223344",
      "address": {
          "address": "6 rue neyret",
          "postal_code": "69001",
          "city_name": "lyon 1er",
          "country": "FR"
      },
      "cgu_accepted": true,
      "allow_mobile_notifications": true,
      "allow_email_notifications": true
    }
    """
    Then the response status code should be 201
    And I should have 1 email "JeMengageUserAccountConfirmationMessage" for "new-user@en-marche-dev.fr" with payload:
    """
    {
      "template_name": "je-mengage-user-account-confirmation",
      "template_content": [],
      "message": {
        "subject": "Confirmez votre adresse email",
        "from_email": "ne-pas-repondre@je-mengage.fr",
        "global_merge_vars": [
          {
            "name": "first_name",
            "content": "Jules"
          },
          {
            "name": "create_password_link",
            "content": "http://login.jemengage.code/changer-mot-de-passe/@string@/@string@"
          }
        ],
        "from_name": "Je m'engage",
        "to": [
          {
            "email": "new-user@en-marche-dev.fr",
            "type": "to",
            "name": "Jules Fullstack"
          }
        ]
      }
    }
    """
    When I click on the email link "create_password_link"
    And I fill in the following:
      | adherent_reset_password[password][first]  | test1234 |
      | adherent_reset_password[password][second] | test1234 |
    And I press "Créer mon mot de passe"
    Then I should be on "http://login.jemengage.code/bienvenue"

    Scenario: As a non logged-in user I can request a reset password
      Given I send a "POST" request to "/api/membership/forgot-password?source=jemengage" with body:
      """
      {"email_address": "je-mengage-user-1@en-marche-dev.fr"}
      """
      Then the response status code should be 200
      And I should have 1 email "JeMengageResetPasswordMessage" for "je-mengage-user-1@en-marche-dev.fr" with payload:
      """
      {
        "template_name": "je-mengage-reset-password",
        "template_content": [],
        "message": {
          "subject": "Réinitialisation de votre mot de passe",
          "from_email": "ne-pas-repondre@je-mengage.fr",
          "global_merge_vars": [
            {
              "name": "first_name",
              "content": "Jules"
            },
            {
              "name": "create_password_link",
              "content": "http://login.jemengage.code/changer-mot-de-passe/@string@/@string@"
            }
          ],
          "from_name": "Je m'engage",
          "to": [
            {
              "email": "je-mengage-user-1@en-marche-dev.fr",
              "type": "to",
              "name": "Jules Fullstack"
            }
          ]
        }
      }
      """
