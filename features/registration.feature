@app
Feature:
  As a visitor
  In order to access the web site
  I can register

  Scenario: I can register as an adherent
    When I am on "/adhesion"
    And I fill in the following:
      | adherent_registration[firstName]            | Jean-Pierre         |
      | adherent_registration[lastName]             | DURAND              |
      | adherent_registration[emailAddress][first]  | jp@test.com         |
      | adherent_registration[emailAddress][second] | jp@test.com         |
      | adherent_registration[password]             | testtest            |
      | adherent_registration[nationality]          | FR                  |
      | adherent_registration[address][address]     | 1 rue des alouettes |
      | adherent_registration[address][postalCode]  | 94320               |
      | adherent_registration[address][cityName]    | Thiais              |
      | adherent_registration[birthdate][day]       | 29                  |
      | adherent_registration[birthdate][month]     | 1                   |
      | adherent_registration[birthdate][year]      | 1989                |
      | adherent_registration[gender]               | male                |
    And I fill in hidden field "adherent_registration_address_city" with "94320-94073"
    And I fill in hidden field "adherent_registration_address_country" with "FR"
    And I check "Oui, j'adhère à la charte des valeurs, aux statuts et aux règles de fonctionnement de La République En Marche, ainsi qu'aux conditions générales d'utilisation du site"
    And I resolved the captcha
    And I clean the "api_sync" queue
    And I press "Je rejoins La République En Marche"
    And the response status code should be 200
    Then I should be on "/inscription/centre-interets"

    Given I check "Sport"
    When I press "Continuer"
    Then I should be on "/inscription/choisir-des-comites"
    And the response status code should be 200
    And I should see "Je veux suivre ce comité" 3 times

    Given I press "Continuer"
    Then I should be on "/inscription/don"
    And the response status code should be 200
    And I should see "Vous êtes une majorité à donner 50 €"

    When I click the "skip-donation" element
    Then the response status code should be 200
    And I should be on "/presque-fini"
    Then the adherent "jp@test.com" should have the "94" referent tag
    And I should have 1 email
    And I should have 1 email "AdherentAccountActivationMessage" for "jp@test.com" with payload:
    """
    {
      "template_name": "adherent-account-activation",
      "template_content": [],
      "message": {
        "subject": "Confirmez votre compte En-Marche.fr",
        "from_email": "contact@en-marche.fr",
        "merge_vars": [
          {
            "rcpt": "jp@test.com",
            "vars": [
              {
                "name": "first_name",
                "content": "Jean-Pierre"
              },
              {
                "name": "activation_link",
                "content": "http:\/\/test.enmarche.code\/inscription\/finaliser\/@string@\/@string@"
              }
            ]
          }
        ],
        "from_name": "La R\u00e9publique En Marche !",
        "to": [
          {
            "email": "jp@test.com",
            "type": "to",
            "name": "Jean-Pierre Durand"
          }
        ]
      }
    }
    """

    Given I am on "/deconnexion"
    And I am on "/connexion"
    When I fill in the following:
      | E-mail       | jp-fail@test.com |
      | Mot de passe | testtesti        |
    And I press "Connexion"
    Then I should be on "/connexion"
    And I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    Given I am on "/connexion"
    When I fill in the following:
      | E-mail       | jp@test.com |
      | Mot de passe | testtest    |
    And I press "Connexion"
    Then I should see "Pour vous connecter vous devez confirmer votre adhésion. Si vous n'avez pas reçu le mail de validation, vous pouvez cliquer ici pour le recevoir à nouveau."

    When I click on the email link "activation_link"
    Then I should be on "/espace-adherent/accueil"
    And the response status code should be 200

  Scenario: I can register as a user
    When I am on "/inscription-utilisateur"
    And I fill in the following:
      | Prénom             | Jean-Pierre |
      | Nom                | DURAND      |
      | E-mail             | jp@test.com |
      | Re-saisir l'e-mail | jp@test.com |
      | Mot de passe       | testtest    |
      | Code postal        | 38000       |
      | Pays               | CH          |
      | Nationalité        | CH          |
    And I resolved the captcha
    And I press "Créer mon compte"
    Then I should be on "/presque-fini"
    And the response status code should be 200
    And I should have 1 email
    And I should have 1 email "AdherentAccountActivationMessage" for "jp@test.com" with payload:
    """
    {
      "template_name": "adherent-account-activation",
      "template_content": [],
      "message": {
        "subject": "Confirmez votre compte En-Marche.fr",
        "from_email": "contact@en-marche.fr",
        "merge_vars": [
          {
            "rcpt": "jp@test.com",
            "vars": [
              {
                "name": "first_name",
                "content": "Jean-Pierre"
              },
              {
                "name": "activation_link",
                "content": "http:\/\/test.enmarche.code\/inscription\/finaliser\/@string@\/@string@"
              }
            ]
          }
        ],
        "from_name": "La R\u00e9publique En Marche !",
        "to": [
          {
            "email": "jp@test.com",
            "type": "to",
            "name": "Jean-Pierre Durand"
          }
        ]
      }
    }
    """

    Given I am on "/connexion"
    When I fill in the following:
      | E-mail       | jp@test.com |
      | Mot de passe | testtest    |
    And I press "Connexion"
    Then I should see "Pour vous connecter vous devez confirmer votre adhésion. Si vous n'avez pas reçu le mail de validation, vous pouvez cliquer ici pour le recevoir à nouveau."

    When I click on the email link "activation_link"
    Then I should be on "/adhesion"
    And the "become_adherent[phone][country]" field should contain "CH"

    When I am on "/adhesion"
    Then I should not see "Bienvenue ! Votre e-mail est confirmé."
    And I should not see "L'un des champs du formulaire est mal renseigné."

    And I fill in the following:
      | become_adherent[address][address]    |    |
      | become_adherent[address][cityName]   |    |
      | become_adherent[address][postalCode] |    |
      | become_adherent[address][country]    | FR |
      | become_adherent[gender]              |    |
      | become_adherent[phone][number]       |    |
      | become_adherent[birthdate][day]      |    |
      | become_adherent[birthdate][month]    |    |
      | become_adherent[birthdate][year]     |    |
    When I press "Je rejoins La République En Marche"
    Then I should see 6 ".form__error" elements
    And I should see "L'adresse est obligatoire."
    And I should see "Veuillez renseigner un code postal."
    And I should see "Veuillez renseigner une ville."
    And I should see "Veuillez renseigner un genre."
    And I should see "Vous devez spécifier votre date de naissance."
    And I should see "Vous devez accepter les conditions."
    And I should see "L'un des champs du formulaire est mal renseigné."

    And I fill in the following:
      | become_adherent[address][address]    |    |
      | become_adherent[address][cityName]   |    |
      | become_adherent[address][postalCode] |    |
      | become_adherent[address][country]    | RU |
      | become_adherent[gender]              |    |
      | become_adherent[phone][number]       |    |
      | become_adherent[birthdate][day]      |    |
      | become_adherent[birthdate][month]    |    |
      | become_adherent[birthdate][year]     |    |
    When I press "Je rejoins La République En Marche"
    Then I should see 5 ".form__error" elements
    And I should see "L'adresse est obligatoire."
    And I should see "Veuillez renseigner une ville."
    And I should see "Veuillez renseigner un genre."
    And I should see "Vous devez spécifier votre date de naissance."
    And I should see "Vous devez accepter les conditions."
    And I should see "L'un des champs du formulaire est mal renseigné."

    Given I fill in hidden field "become_adherent_address_city" with "06000-6088"
    And I fill in the following:
      | become_adherent[address][address]    | 1 rue de l'egalite |
      | become_adherent[address][cityName]   | Nice               |
      | become_adherent[address][postalCode] | 06000              |
      | become_adherent[address][country]    | FR                 |
      | become_adherent[gender]              | male               |
      | become_adherent[phone][country]      | FR                 |
      | become_adherent[phone][number]       | 0600000000         |
      | become_adherent[birthdate][day]      | 1                  |
      | become_adherent[birthdate][month]    | 1                  |
      | become_adherent[birthdate][year]     | 1980               |
    And I check "Oui, j'adhère à la charte des valeurs, aux statuts et aux règles de fonctionnement de La République En Marche, ainsi qu'aux conditions générales d'utilisation du site"
    When I press "Je rejoins La République En Marche"
    Then I should be on "/espace-adherent/accueil"
    And I should see "Votre compte adhérent est maintenant actif."
    And I should have 2 emails
    And the adherent "jp@test.com" should have the "06" referent tag
    And I should have 1 email "AdherentAccountConfirmationMessage" for "jp@test.com" with payload:
    """
    {
      "template_name": "adherent-account-confirmation",
      "template_content": [],
      "message": {
        "subject": "Et maintenant ?",
        "from_email": "contact@en-marche.fr",
        "merge_vars": [
          {
            "rcpt": "jp@test.com",
            "vars": [
              {
                "name": "target_firstname",
                "content": "Jean-Pierre"
              }
            ]
          }
        ],
        "from_name": "La R\u00e9publique En Marche !",
        "to": [
          {
            "email": "jp@test.com",
            "type": "to",
            "name": "Jean-Pierre Durand"
          }
        ]
      }
    }
    """
    And I clean the "api_sync" queue

    Given I am on "/parametres/mon-compte/modifier"
    Then the response status code should be 200

    And the "adherent_profile[address][address]" field should contain "1 rue de l'egalite"
    And the "adherent_profile[address][country]" field should contain "FR"
    And the "adherent_profile[phone][country]" field should contain "FR"
    And the "adherent_profile[phone][number]" field should contain "06 00 00 00 00"
    And the "adherent_profile[birthdate]" field should contain "1980-01-01"

    Given I follow "Communication"
    Then I should be on "/parametres/mon-compte/preferences-des-emails"
    And the "Recevoir les informations sur les actions militantes du mouvement par téléphone" checkbox should be unchecked
    And the "Recevoir les e-mails nationaux" checkbox should be unchecked
    And the "Recevoir la newsletter hebdomadaire nationale" checkbox should be unchecked
    And I should not see an "Recevoir les e-mails de mon animateur\(trice\) local\(e\) de comité" element
    And I should not see an "Recevoir les e-mails de mon\/ma référent\(e\) territorial\(e\)" element
    And I should not see an "Recevoir les e-mails de mon porteur de projet" element
    And I should not see an "Être notifié\(e\) de la création de nouveaux projets citoyens dans un rayon de autour de chez moi" element

    When I check "Recevoir les e-mails nationaux"
    When I check "Recevoir la newsletter hebdomadaire nationale"
    And the "Recevoir les e-mails nationaux" checkbox should be checked
    And the "Recevoir la newsletter hebdomadaire nationale" checkbox should be checked
    And I press "Enregistrer les modifications"
    Then the response status code should be 200
    And I should see "Vos préférences d'e-mails ont bien été mises à jour."

  Scenario: I have great error message when register is misfiled
    Given I am on "/inscription-utilisateur"
    When I fill in the following:
      | Prénom             |                  |
      | Nom                |                  |
      | E-mail             | jp@test.com      |
      | Re-saisir l'e-mail | jp2@test.com     |
      | Mot de passe       | testte           |
      | Code postal        | 0000000000000000 |
      | Pays               | FR               |
      | Nationalité        | FR               |
    And I press "Créer mon compte"
    Then the response status code should be 200
    And I should see 6 ".form__error" elements
    And I should see "Les adresses email ne correspondent pas."
    And I should see "Votre mot de passe doit comporter au moins 8 caractères."
    And I should see "Le code postal doit contenir moins de 15 caractères"
    And I should see "Cette valeur n'est pas un code postal français valide."
    And I should see "Votre prénom doit comporter au moins 2 caractères."
    And I should see "Votre nom doit comporter au moins 1 caractères."

  Scenario: A new user should see personal message to help him to validate his account
    And I am on "/connexion"
    When I fill in the following:
      | E-mail       | simple-user-not-activated@example.ch |
      | Mot de passe | secret!12345                         |
    And I press "Connexion"
    Then I should see "Pour vous connecter vous devez confirmer votre adhésion. Si vous n'avez pas reçu le mail de validation, vous pouvez cliquer ici pour le recevoir à nouveau."

    When I follow "cliquer ici"
    Then I should be on "/connexion"
    And I should have 1 email
    And I should have 1 email "AdherentAccountActivationMessage" for "simple-user-not-activated@example.ch" with payload:
    """
    {
      "template_name": "adherent-account-activation",
      "template_content": [],
      "message": {
        "subject": "Confirmez votre compte En-Marche.fr",
        "from_email": "contact@en-marche.fr",
        "merge_vars": [
          {
            "rcpt": "simple-user-not-activated@example.ch",
            "vars": [
              {
                "name": "first_name",
                "content": "Simple"
              },
              {
                "name": "activation_link",
                "content": "http:\/\/test.enmarche.code\/inscription\/finaliser\/@string@\/@string@"
              }
            ]
          }
        ],
        "from_name": "La R\u00e9publique En Marche !",
        "to": [
          {
            "email": "simple-user-not-activated@example.ch",
            "type": "to",
            "name": "Simple User"
          }
        ]
      }
    }
    """

    When I click on the email link "activation_link"
    Then I should be on "/adhesion"
