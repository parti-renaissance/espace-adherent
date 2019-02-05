Feature:
  In order to protect the APP
  I should be blocked if I try to login too many time

  Scenario: Known user is blocked after 5 attempts
    Given I am on "/connexion"

    When I fill in the following:
      | _login_email    | luciole1989@spambox.fr |
      | _login_password | wrongPassword          |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _login_email    | lUciole1989@spambox.fr |
      | _login_password | wrongPassword          |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _login_email    | Luciole1989@spambox.fr |
      | _login_password | wrongPassword          |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _login_email    | luciole1989@spambox.fr |
      | _login_password | wrongPassword          |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _login_email    | luciole1989@spambox.fr |
      | _login_password | wrongPassword          |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    # Refuse login with good credential
    When I fill in the following:
      | _login_email    | luciole1989@spambox.fr |
      | _login_password | EnMarche2017           |
    And I press "Connexion"
    Then I should see "Vous avez effectué 5 tentatives de connexion erronées. Veuillez attendre 1 minute avant de réessayer."

  Scenario: Unknown user is blocked after 5 attempts
    Given I am on "/connexion"

    When I fill in the following:
      | _login_email    | unkown_not_in_em_db@spambox.fr |
      | _login_password | wrongPassword                  |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _login_email    | unkown_not_in_em_db@spambox.fr |
      | _login_password | wrongPassword                  |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _login_email    | unkown_not_in_em_db@spambox.fr |
      | _login_password | wrongPassword                  |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _login_email    | unkown_not_in_em_db@spambox.fr |
      | _login_password | wrongPassword                  |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _login_email    | unkown_not_in_em_db@spambox.fr |
      | _login_password | wrongPassword                  |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    # Refuse login
    When I fill in the following:
      | _login_email    | unkown_not_in_em_db@spambox.fr |
      | _login_password | wrongPassword                  |
    And I press "Connexion"
    Then I should see "Vous avez effectué 5 tentatives de connexion erronées. Veuillez attendre 1 minute avant de réessayer."

  Scenario: Known admin is blocked after 5 attempts
    Given I am on "/admin/login"

    When I fill in the following:
      | _login_email    | superadmin@en-marche-dev.fr |
      | _login_password | wrongPassword               |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _login_email    | superadmin@en-marche-dev.fr |
      | _login_password | wrongPassword               |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _login_email    | suPeradmin@en-marche-dev.fr |
      | _login_password | wrongPassword               |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _login_email    | superadmin@en-marche-dev.fr |
      | _login_password | wrongPassword               |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _login_email    | superAdmin@en-marche-dev.fr |
      | _login_password | wrongPassword               |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    # Refuse login with good credential
    When I fill in the following:
      | _login_email    | superadmin@en-marche-dev.fr |
      | _login_password | superadmin                  |
    And I press "Connexion"
    Then I should see "Vous avez effectué 5 tentatives de connexion erronées. Veuillez attendre 1 minute avant de réessayer."
