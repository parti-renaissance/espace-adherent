Feature:
  As a user
  In order to confirm my membership is terminated
  An email should be sent by the application

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData |

  Scenario: A user terminates its membership
    When I am logged as "michel.vasseur@example.ch"
    Given I am on "/parametres/mon-compte/desadherer"
    When I check "Je reçois trop d'e-mails"
    And I press "Je confirme la suppression de mon adhésion"
    Then I should have 1 email "AdherentTerminateMembershipMessage" for "michel.vasseur@example.ch" with payload:
    """
    {
      "FromEmail": "contact@en-marche.fr",
      "FromName": "En Marche !",
      "Subject": "Votre départ d'En Marche !",
      "MJ-TemplateID": "187353",
      "MJ-TemplateLanguage": true,
      "Recipients": [
        {
          "Email": "michel.vasseur@example.ch",
          "Name": "Michel VASSEUR",
          "Vars": {
            "target_firstname": "Michel"
          }
        }
      ]
    }
    """
