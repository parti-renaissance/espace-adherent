Feature:
  As a user
  In order to contact another user
  An email should be sent by the application

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData |

  Scenario: Send a message to an user
    When I am logged as "jacques.picard@en-marche.fr"
    Given I am on "/espace-adherent/contacter/313bd28f-efc8-57c9-8ab7-2106c8be9697"
    When I fill in the following:
      | Contenu du message | Bonjour Michelle ! |
    And I resolved the captcha
    And I press "Envoyer"
    Then I should have 1 email "AdherentContactMessage" for "michelle.dufour@example.ch" with payload:
    """
    {
      "FromEmail": "jemarche@en-marche.fr",
      "FromName": "En Marche !",
      "Subject": "Jacques vous a envoyé un message",
      "MJ-TemplateID": "114629",
      "MJ-TemplateLanguage": true,
      "Recipients": [
        {
          "Email": "michelle.dufour@example.ch",
          "Name": "Michelle Dufour",
          "Vars": {
            "member_firstname": "Jacques",
            "target_message": "Bonjour Michelle !"
          }
        }
      ],
      "Headers": {
        "Reply-To": "jacques.picard@en-marche.fr"
      }
    }
    """
