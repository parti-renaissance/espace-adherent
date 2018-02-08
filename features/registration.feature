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
            "activation_link": "http:\/\/test.enmarche.code\/inscription\/finaliser\/@string@\/@string@"
          }
        }
      ]
    }
    """

    Given I am on "/connexion"
    When I fill in the following:
      | E-mail       | jp@test.com |
      | Mot de passe | testtest    |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I click on the email link "activation_link"
    Then I should be on "/adhesion"
    And the "become_adherent[phone][country]" field should contain "CH"
    And I should see "Bienvenue ! Votre e-mail est confirmé."

    When I am on "/adhesion"
    Then I should not see "Bienvenue ! Votre e-mail est confirmé."

    And I fill in the following:
      | become_adherent[address][address]    |  |
      | become_adherent[address][cityName]   |  |
      | become_adherent[address][postalCode] |  |
      | become_adherent[gender]              |  |
      | become_adherent[phone][number]       |  |
      | become_adherent[birthdate][day]      |  |
      | become_adherent[birthdate][month]    |  |
      | become_adherent[birthdate][year]     |  |
    When I press "J'adhère"
    Then I should see 6 ".form__error" elements
    And I should see "L'adresse est obligatoire."
    And I should see "Veuillez renseigner un code postal."
    And I should see "Veuillez renseigner une ville."
    And I should see "Veuillez renseigner un sexe."
    And I should see "Vous devez spécifier votre date de naissance."
    And I should see "Le numéro de téléphone est obligatoire."

    When I fill in hidden field "become_adherent_address_city" with "06000-6088"
    And I fill in the following:
      | become_adherent[address][address]    | 1 rue de l'egalite |
      | become_adherent[address][cityName]   | Nice               |
      | become_adherent[address][postalCode] | 06000              |
      | become_adherent[gender]              | male               |
      | become_adherent[phone][country]      | FR                 |
      | become_adherent[phone][number]       | 0600000000         |
      | become_adherent[birthdate][day]      | 1                  |
      | become_adherent[birthdate][month]    | 1                  |
      | become_adherent[birthdate][year]     | 1980               |
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
    Then the "adherent[address][address]" field should contain "1 rue de l'egalite"
    And the "adherent[address][country]" field should contain "CH"
    And the "adherent[phone][country]" field should contain "FR"
    And the "adherent[phone][number]" field should contain "06 00 00 00 00"
    And the "adherent[birthdate][day]" field should contain "1"
    And the "adherent[birthdate][month]" field should contain "1"
    And the "adherent[birthdate][year]" field should contain "1980"

    When I am on "/parametres/mon-compte/preferences-des-emails"
    Then the element "Emails En Marche !" should be disabled
    Then the element "Emails de vos référents" should be disabled
    Then the element "Emails de votre animateur local" should be disabled
    Then the element "Être notifié(e) de la création de nouveaux projets citoyens" should be disabled

  Scenario: I can become adherent with a foreign country
    Given the following fixtures are loaded:
      | LoadUserData |
    And I am logged as "simple-user@example.ch"
    And I am on "/adhesion"
    And I fill in the following:
      | become_adherent[address][address]    | 32 Zeppelinstrasse |
      | become_adherent[address][postalCode] | 8057               |
      | become_adherent[gender]              | male               |
      | become_adherent[phone][number]       | 06 12 34 56 78     |
      | become_adherent[birthdate][day]      | 1                  |
      | become_adherent[birthdate][month]    | 1                  |
      | become_adherent[birthdate][year]     | 1980               |
    When I press "J'adhère"
    Then I should see "Veuillez renseigner une ville."

    Given I fill in the following:
      | become_adherent[address][cityName] | Zürich |
    When I press "J'adhère"
    Then I should be on "/espace-adherent/accueil"
    And I should see "Votre compte adhérent est maintenant actif."

  Scenario: I can become adherent with a french address
    Given the following fixtures are loaded:
      | LoadUserData |
    And I am logged as "simple-user@example.ch"
    And I am on "/adhesion"
    And I fill in the following:
      | become_adherent[address][country]    | FR                  |
      | become_adherent[address][address]    | 1 rue des alouettes |
      | become_adherent[gender]              | male                |
      | become_adherent[phone][number]       | 06 12 34 56 78      |
      | become_adherent[birthdate][day]      | 1                   |
      | become_adherent[birthdate][month]    | 1                   |
      | become_adherent[birthdate][year]     | 1980                |
    When I press "J'adhère"
    Then I should see "Veuillez renseigner une ville."

    Given I fill in the following:
      | become_adherent[address][postalCode] | 69001    |
      | become_adherent[address][cityName]   | Lyon 1er |
    And I fill in hidden field "become_adherent_address_city" with "69001-6088"
    When I press "J'adhère"
    Then I should be on "/espace-adherent/accueil"
    And I should see "Votre compte adhérent est maintenant actif."

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
    And I should see "Le code postal doit contenir moins de 15 caractères"
    And I should see "Cette valeur ne doit pas être vide."
