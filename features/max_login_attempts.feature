Feature:
  In order to protect the APP
  I should be blocked if I try to login too many time

  Scenario: I'm block after 5 attempts
    Given the following fixtures are loaded:
      | LoadAdherentData |
    Given I am on "/connexion"

    When I fill in the following:
      | _adherent_email    | luciole1989@spambox.fr |
      | _adherent_password | wrongPassword          |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _adherent_email    | luciole1989@spambox.fr |
      | _adherent_password | wrongPassword          |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _adherent_email    | luciole1989@spambox.fr |
      | _adherent_password | wrongPassword          |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _adherent_email    | luciole1989@spambox.fr |
      | _adherent_password | wrongPassword          |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    When I fill in the following:
      | _adherent_email    | luciole1989@spambox.fr |
      | _adherent_password | wrongPassword          |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."

    # Refuse login with good credential
    When I fill in the following:
      | _adherent_email    | luciole1989@spambox.fr |
      | _adherent_password | EnMarche2017           |
    And I press "Connexion"
    Then I should see "L'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas."
