@app
Feature: Manage adherent from admin panel

  Scenario: Display list of adherents
    Given I am logged as "superadmin@en-marche-dev.fr" admin
    When I am on "/admin/app/adherent/list"
    Then the response status code should be 200
    And I should see 32 "tbody tr" elements
    And I should see 14 "thead tr th" elements

  Scenario: A user update must trigger an event in RabbitMQ
    Given I am logged as "superadmin@en-marche-dev.fr" admin
    When I am on "/admin/app/adherent/list"
    And I follow "SCHMIDT"
    And I clean the "api_sync" queue
    When I press "Mettre à jour"
    Then the response status code should be 200

  @debug
  Scenario:
      As an administrator, I can manually register a user from another app.
      The adherent can then change his password on the platform
    Given I am logged as "superadmin@en-marche-dev.fr" admin
    Then I should have 0 email
    When I am on "/admin/app/adherent/list"
    And I fill in the following:
      | filter[emailAddress][value] | je-mengage-user-1@en-marche-dev.fr |
    When I press "Filtrer"
    Then I should be on "/admin/app/adherent/list"
    And I should see "Jules Fullstack"
    And I follow "EM"
    Then I should be on "/"

    And I follow "Adhérer"
    Then I should be on "/adhesion"
    And I check "become_adherent[conditions]"
    When I press "Je rejoins La République En Marche"
    Then I should be on "/espace-adherent/accueil"
    And I should see "Votre compte adhérent est maintenant actif."
    Then I go to "/deconnexion"
    Then I should be on "/"

    Then I follow "Connexion"
    And I fill in the following:
      | _login_email | je-mengage-user-1@en-marche-dev.fr |
      | _login_password | secret!12345                    |
    And I press "Connexion"
    And I should be on "/evenements"
    Then I go to "/deconnexion"
    Then I should be on "/"

    Then I follow "Connexion"
    Then I should be on "/connexion"
    And I should have 0 email
    Then I follow "Mot de passe oublié ? Cliquez ici"
    Then I should be on "/mot-de-passe-oublie"
    And I fill in the following:
      | form[email] | je-mengage-user-1@en-marche-dev.fr |
    And I clean the "api_sync" queue
    And I press "Envoyer un e-mail"
    Then I should be on "/connexion"
    And I should see "Si l'adresse que vous avez saisie est valide, un e-mail vous a été envoyé contenant un lien pour réinitialiser votre mot de passe."
    And I should have 1 email
    And I should have 1 email "AdherentResetPasswordMessage" for "je-mengage-user-1@en-marche-dev.fr" with payload:
    """
    {
      "template_name": "adherent-reset-password,
      "template_content": [],
      "message": {
        "subject": "Réinitialisation de votre mot de passe",
        "from_email": "contact@en-marche.fr",
        "from_name": "La République En Marche !",
        "global_merge_vars": [
          {
            "name": "first_name",
            "content": "Jules"
          },
          {
            "name": "reset_link",
            "content": "http://enmarche.code/changer-mot-de-passe/@string@/@string@"
          }
        ],
        "to": [
          {
            "email": "je-mengage-user-1@en-marche-dev.fr",
            "type": "to",
            "name": "Jules Fullstack"
          }
        ]
      }
    }
    """
