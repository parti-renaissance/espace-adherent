@app
@renaissance_admin
Feature:
  In order to protect the APP
  I should be blocked if I try to login too many time

    Scenario: Known admin is blocked after 5 attempts
        And I am on "/login"

        When I fill in the following:
            | _login_email    | superadmin@en-marche-dev.fr |
            | _login_password | wrongPassword               |
        And I press "Connexion"
        Then I should see "L'adresse email et le mot de passe que vous avez saisis ne correspondent pas."

        When I fill in the following:
            | _login_email    | superadmin@en-marche-dev.fr |
            | _login_password | wrongPassword               |
        And I press "Connexion"
        Then I should see "L'adresse email et le mot de passe que vous avez saisis ne correspondent pas."

        When I fill in the following:
            | _login_email    | suPeradmin@en-marche-dev.fr |
            | _login_password | wrongPassword               |
        And I press "Connexion"
        Then I should see "L'adresse email et le mot de passe que vous avez saisis ne correspondent pas."

        When I fill in the following:
            | _login_email    | superadmin@en-marche-dev.fr |
            | _login_password | wrongPassword               |
        And I press "Connexion"
        Then I should see "L'adresse email et le mot de passe que vous avez saisis ne correspondent pas."

        When I fill in the following:
            | _login_email    | superAdmin@en-marche-dev.fr |
            | _login_password | wrongPassword               |
        And I press "Connexion"
        Then I should see "L'adresse email et le mot de passe que vous avez saisis ne correspondent pas."

    # Refuse login with good credential
        When I fill in the following:
            | _login_email    | superadmin@en-marche-dev.fr |
            | _login_password | superadmin                  |
        And I press "Connexion"
        Then I should see "Vous avez effectué 5 tentatives de connexion erronées. Veuillez attendre 1 minute avant de réessayer."
