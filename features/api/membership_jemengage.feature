@api
Feature:
  In order to create a JeMengage user
  As a non logged-in user
  I should be able to access API membership

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
      "cgu_accepted": true
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
            "content": "http:\/\/login.jemengage.code\/changer-mot-de-passe\/@string@\/@string@"
          }
        ],
        "from_name": "Je-mengage.fr",
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
    And I press "Réinitialiser le mot de passe"
    Then I should be on "http://login.jemengage.code/bienvenue"

    Scenario: As a non logged-in user I can request a reset password
      Given I send a "POST" request to "/api/membership/forgot-password?source=jemengage" with body:
      """
      {"email_address": "new-user@en-marche-dev.fr"}
      """
      Then the response status code should be 200
      And I should have 1 email "JeMengageResetPasswordMessage" for "new-user@en-marche-dev.fr" with payload:
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
              "content": "http:\/\/login.jemengage.code\/changer-mot-de-passe\/@string@\/@string@"
            }
          ],
          "from_name": "Je-mengage.fr",
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
