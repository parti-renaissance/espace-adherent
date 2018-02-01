Feature:
  As an anonymous user
  In order to register
  I should receive emails from the application

  Scenario: An anonymous user registers
    Given I am on "/inscription"
    When I fill in the following:
      | Prénom             | Jacques     |
      | Nom                | PICARD      |
      | E-mail             | jp@test.com |
      | Re-saisir l'e-mail | jp@test.com |
      | Mot de passe       | testtest    |
      | Code postal        | 38000       |
      | Pays               | FR          |
    And I resolved the captcha
    And I press "Créer mon compte"
    Then I should have 1 email "AdherentAccountActivationMessage" for "jp@test.com" with payload:
    """
    {
      "FromEmail": "contact@en-marche.fr",
      "FromName": "En Marche !",
      "Subject": "Confirmez votre compte En-Marche.fr",
      "MJ-TemplateID": "292269",
      "MJ-TemplateLanguage": true,
      "Recipients": [
        {
          "Email": "jp@test.com",
          "Name": "Jacques PICARD",
          "Vars": {
            "first_name": "Jacques",
            "activation_link": "http:\/\/enmarche.dev\/inscription\/finaliser\/@string@\/@string@"
          }
        }
      ]
    }
    """
    When I click on the email link "activation_link"
    Then I should have 1 email "AdherentAccountConfirmationMessage" for "jp@test.com" with payload:
    """
    {
      "FromEmail": "contact@en-marche.fr",
      "FromName": "En Marche !",
      "Subject": "Et maintenant ?",
      "MJ-TemplateID": "54673",
      "MJ-TemplateLanguage": true,
      "Recipients": [
        {
          "Email": "jp@test.com",
          "Name": "Jacques PICARD",
          "Vars": {
            "adherents_count": 1,
            "committees_count": 0,
            "target_firstname": "Jacques",
            "target_lastname": "PICARD"
          }
        }
      ]
    }
    """
