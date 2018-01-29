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
    Then I should be on "/inscription"

    When I am on "/register?foo=bar"
    Then I should be on "/inscription?foo=bar"
