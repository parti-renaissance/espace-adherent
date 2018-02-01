Feature:
  As a visitor
  In order to access the web site
  I can register

  Scenario: I can register as a user
    Given I am on "/inscription"
    When I fill in the following:
      | Prénom             | Jean-Pierre |
      | Nom                | DURAND      |
      | E-mail             | jp@test.com |
      | Re-saisir l'e-mail | jp@test.com |
      | Mot de passe       | testtest    |
      | Code postal        | 38000       |
      | Pays               | CH          |
    And I resolved the captcha
    And I press "Créer mon compte"
    Then I should be on "/presque-fini"
    And the response status code should be 200
    And I should have 1 email "AdherentAccountActivationMessage" for "jp@test.com" with payload:
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
          "Name": "Jean-Pierre Durand",
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
    And the "update_membership_request[phone][country]" field should contain "CH"

    When I fill in hidden field "update_membership_request_address_city" with "06000-6088"
    And I fill in the following:
      | update_membership_request[address][address]  | 1 rue de l'egalite |
      | update_membership_request[address][cityName] | Nice, France       |
      | update_membership_request[phone][country]    | FR                 |
      | update_membership_request[phone][number]     | 0600000000         |
      | update_membership_request[birthdate][day]    | 1                  |
      | update_membership_request[birthdate][month]  | 1                  |
      | update_membership_request[birthdate][year]   | 1980               |
    And I press "J'adhère"
    Then I should be on "/espace-adherent/accueil"
    And I should see "Votre compte adhérent est maintenant actif."
    And I should have 1 email "AdherentAccountConfirmationMessage" for "jp@test.com" with payload:
    """
    {
        "FromEmail":"contact@en-marche.fr",
        "FromName":"En Marche !",
        "Subject":"Et maintenant ?",
        "MJ-TemplateID":"54673",
        "MJ-TemplateLanguage":true,
        "Recipients":[
            {
                "Email":"jp@test.com",
                "Name":"Jean-Pierre Durand",
                "Vars":{
                    "adherents_count":1,
                    "committees_count":0,
                    "target_firstname":"Jean-Pierre",
                    "target_lastname":"Durand"
                }
            }
        ]
    }
    """

    When I am on "/parametres/mon-compte/modifier"
    Then the "update_membership_request[address][address]" field should contain "1 rue de l'egalite"
    And the "update_membership_request[address][country]" field should contain "FR"
    And the "update_membership_request[phone][country]" field should contain "FR"
    And the "update_membership_request[phone][number]" field should contain "06 00 00 00 00"
    And the "update_membership_request[birthdate][day]" field should contain "1"
    And the "update_membership_request[birthdate][month]" field should contain "1"
    And the "update_membership_request[birthdate][year]" field should contain "1980"

  Scenario: I have great error message when register is misfiled
    Given I am on "/inscription"
    When I fill in the following:
      | Prénom             |                  |
      | Nom                |                  |
      | E-mail             | jp@test.com      |
      | Re-saisir l'e-mail | jp2@test.com     |
      | Mot de passe       | testte           |
      | Code postal        | 0000000000000000 |
      | Pays               | FR               |
    And I press "Créer mon compte"
    Then the response status code should be 200
    And I should see 6 ".form__error" elements
    And I should see "Les adresses email ne correspondent pas."
    And I should see "Votre mot de passe doit comporter au moins 8 caractères."
    And I should see "Vous avez été détecté en tant que robot, pourriez-vous réessayer ?"
    And I should see "Vous devez saisir au maximum 15 caractères."
    And I should see "Cette valeur ne doit pas être vide."
