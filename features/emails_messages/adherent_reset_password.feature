Feature:
  As a user
  In order to reset my password
  I should receive emails from the application

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData |

  Scenario: Reset my password
    Given I am on "/mot-de-passe-oublie"
    When I fill in the following:
      | E-mail | jacques.picard@en-marche.fr |
    And I press "Envoyer un e-mail"
    Then I should have 1 email "AdherentResetPasswordMessage" for "jacques.picard@en-marche.fr" with payload:
    """
    {
      "FromEmail": "contact@en-marche.fr",
      "FromName": "En Marche !",
      "Subject": "R\u00e9initialisation de votre mot de passe",
      "MJ-TemplateID": "292292",
      "MJ-TemplateLanguage": true,
      "Recipients": [
        {
          "Email": "jacques.picard@en-marche.fr",
          "Name": "Jacques Picard",
          "Vars": {
            "first_name": "Jacques",
            "reset_link": "http:\/\/enmarche.dev\/changer-mot-de-passe\/@string@\/@string@"
          }
        }
      ]
    }
    """

    When I click on the email link "reset_link"
    And I fill in the following:
      | Nouveau mot de passe | test123test |
      | Confirmation         | test123test |
    And I press "Réinitialiser le mot de passe"
    Then I should have 1 email "AdherentResetPasswordConfirmationMessage" for "jacques.picard@en-marche.fr" with payload:
    """
    {
      "FromEmail": "contact@en-marche.fr",
      "FromName": "En Marche !",
      "Subject": "Confirmation réinitialisation du mot de passe",
      "MJ-TemplateID": "292297",
      "MJ-TemplateLanguage": true,
      "Recipients": [
        {
          "Email": "jacques.picard@en-marche.fr",
          "Name": "Jacques Picard",
          "Vars": {
            "first_name": "Jacques"
          }
        }
      ]
    }
    """
