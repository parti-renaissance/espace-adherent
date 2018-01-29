Feature:
  As a user
  In order to confirm i attend to a citizen action
  I should receive an email from the application

  Background:
    Given the following fixtures are loaded:
      | LoadCitizenActionData |

  Scenario: Attend to a citizen action and receive a confirmation email
    Given I am logged as "michel.vasseur@example.ch"
    And I am viewing the citizen action "Projet citoyen #3"
    And I follow "S'inscrire"
    And I fill in the following:
      | Prénom         | Michel                    |
      | Nom            | VASSEUR                   |
      | Adresse e-mail | michel.vasseur@example.ch |
    And I check "En participant à cet événement, vous acceptez de partager vos nom et prénom à l'organisateur."
    When I press "Je m'inscris"
    Then I should have 1 email "CitizenActionRegistrationConfirmationMessage" for "michel.vasseur@example.ch" with payload:
    """
    {
      "FromEmail": "contact@en-marche.fr",
      "FromName": "En Marche !",
      "Subject": "Votre inscription a bien \u00e9t\u00e9 prise en compte",
      "MJ-TemplateID": "270978",
      "MJ-TemplateLanguage": true,
      "Recipients": [
        {
          "Email": "michel.vasseur@example.ch",
          "Name": "Michel",
          "Vars": {
            "citizen_action_name": "Projet citoyen #3",
            "citizen_action_organiser": "Jacques",
            "citizen_action_calendar_url": "http:\/\/enmarche.dev\/action-citoyenne\/@string@-projet-citoyen-3\/ical",
            "prenom": "Michel"
          }
        }
      ]
    }
    """
