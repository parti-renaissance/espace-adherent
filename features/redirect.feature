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
