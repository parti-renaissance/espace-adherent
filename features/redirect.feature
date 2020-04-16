Feature:
  In order to not confuse other app
  I should have some redirect

  Scenario: Login
    When I am on "/login"
    Then I should be on "/connexion"

    When I am on "/login?foo=bar"
    Then I should be on "/connexion?foo=bar"

  Scenario: Register
    When I am on "/register"
    Then I should be on "/adhesion"

    When I am on "/register?foo=bar"
    Then I should be on "/adhesion?foo=bar"

  Scenario: Inscription
    When I am on "/inscription"
    Then I should be on "/adhesion"

    When I am on "/inscription?foo=bar"
    Then I should be on "/adhesion?foo=bar"

  Scenario: I am redirected to the last visited page after login
    Given the following fixtures are loaded:
      | LoadAdherentData  |
    And I am on "/comites"
    When I am on "/connexion"
    And I fill in the following:
      | _login_email    | carl999@example.fr  |
      | _login_password | secret!12345        |
    And I press "Connexion"
    Then the response status code should be 200
    And I should be on "/comites"
    And I should see "Carl Mirabeau"

    Given I am on "/deconnexion"
    And I am on "/atelier-des-idees/contribuer"
    When I am on "/connexion"
    And I fill in the following:
      | _login_email    | carl999@example.fr  |
      | _login_password | secret!12345        |
    And I press "Connexion"
    Then the response status code should be 200
    And I should be on "/atelier-des-idees/contribuer"
    And I should see "Carl Mirabeau"
