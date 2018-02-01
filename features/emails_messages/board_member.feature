Feature:
  As a board member
  In order to contact other board members
  I should be able to send emails from the application

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData |

  Scenario: Send a message to all board members
    Given I am logged as "referent@en-marche-dev.fr"
    And I am on "/espace-membres-conseil/recherche"
    Then I follow "Envoyer un message à ces 5 personnes"
    And I fill in the following:
      | Objet   | Message d'un membre du conseil      |
      | Message | Message test d'un membre du conseil |
    When I press "Envoyer le message"
    Then I should have 1 email "BoardMemberMessage" for "carl999@example.fr" with payload:
    """
    {
      "FromEmail": "jemarche@en-marche.fr",
      "FromName": "Referent Referent membre du Conseil de LaREM",
      "Subject": "Message d'un membre du conseil",
      "MJ-TemplateID": "233701",
      "MJ-TemplateLanguage": true,
      "Recipients": [
        {
          "Email": "carl999@example.fr",
          "Name": "Carl Mirabeau",
          "Vars": {
            "member_firstname": "Referent",
            "member_lastname": "Referent",
            "target_message": "Message test d'un membre du conseil"
          }
        },
        {
          "Email": "jemarche@en-marche.fr",
          "Name": "Je Marche",
          "Vars": {
            "member_firstname": "Referent",
            "member_lastname": "Referent",
            "target_message": "Message test d'un membre du conseil"
          }
        },
        {
          "Email": "laura@deloche.com",
          "Name": "Laura Deloche",
          "Vars": {
            "member_firstname": "Referent",
            "member_lastname": "Referent",
            "target_message": "Message test d'un membre du conseil"
          }
        },
        {
          "Email": "martine.lindt@gmail.com",
          "Name": "Martine Lindt",
          "Vars": {
            "member_firstname": "Referent",
            "member_lastname": "Referent",
            "target_message": "Message test d'un membre du conseil"
          }
        },
        {
          "Email": "lolodie.dutemps@hotnix.tld",
          "Name": "\u00c9lodie Dutemps",
          "Vars": {
            "member_firstname": "Referent",
            "member_lastname": "Referent",
            "target_message": "Message test d'un membre du conseil"
          }
        },
        {
          "Email": "kiroule.p@blabla.tld",
          "Name": "Pierre Kiroule",
          "Vars": {
            "member_firstname": "Referent",
            "member_lastname": "Referent",
            "target_message": "Message test d'un membre du conseil"
          }
        }
      ],
      "Headers": {
        "Reply-To": "referent@en-marche-dev.fr"
      }
    }
    """
