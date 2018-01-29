Feature:
  As a visitor
  In order to access the web site
  I can register

  Scenario: I can register as a user
    Given the following fixtures are loaded:
      | LoadHomeBlockData |
    Given I am on "/inscription"
    When I fill in the following:
      | Prénom             | Jean-Pierre |
      | Nom                | DURAND      |
      | E-mail             | jp@test.com |
      | Re-saisir l'e-mail | jp@test.com |
      | Mot de passe       | testtest    |
      | Code postal        | 38000       |
      | Pays               | FR          |
    And I resolved the captcha
    And I press "Créer mon compte"
    Then I should be on "/presque-fini"
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
          "Name": "Jean-Pierre DURAND",
          "Vars": {
            "first_name": "Jean-Pierre",
            "activation_link": "http:\/\/enmarche.dev\/inscription\/finaliser\/@string@\/@string@"
          }
        }
      ]
    }
    """
    When I click on the email link "activation_link"
    Then I should be on "/adhesion"
    When I fill in the following:
      | Adresse postale                      | 1 rue de l'egalite |
      | membership_request[phone][country]   | FR                 |
      | membership_request[phone][number]    | 0600000000         |
      | membership_request[birthdate][day]   | 1                  |
      | membership_request[birthdate][month] | 1                  |
      | membership_request[birthdate][year]  | 1980               |
    And I press "J'adhère"
    Then I should be on "/"
    And I should see "Votre compte adhérent est maintenant actif."
    When I am on "/parametres/mon-compte/modifier"
    Then the "update_membership_request[address][address]" field should contain "1 rue de l'egalite"
    And the "update_membership_request[address][country]" field should contain "FR"
    And the "update_membership_request[phone][country]" field should contain "FR"
    And the "update_membership_request[phone][number]" field should contain "06 00 00 00 00"
    And the "update_membership_request[birthdate][day]" field should contain "1"
    And the "update_membership_request[birthdate][month]" field should contain "1"
    And the "update_membership_request[birthdate][year]" field should contain "1980"
