@app
Feature:
  As a referent, animator or simple adherent
  In order to see all my informations
  I should be able to access my dashboard

  Scenario Outline: As an anonymous I can view non-secured pages
    Given I am on "<uri>"
    Then the response status code should be 200
    And the response should not be in JSON
    Examples:
      | uri         |
      | /           |
      | /connexion  |
      | /evenements |

  Scenario Outline: As a logged-in user I can view secured pages
    Given I am logged as "referent@en-marche-dev.fr"
    And I am on "<uri>"
    Then the response status code should be 200
    And the response should not be in JSON
    Examples:
      | uri                           |
      | /espace-adherent/accueil      |

  @renaissance
  Scenario Outline: As an anonymous user I can not view non-secured API pages
    Given I am on "<uri>"
    Then the response status code should be 401
    And the response should be in JSON
    Examples:
      | uri                             |
      | /api/users/me                   |
      | /api/statistics/adherents/count |

  Scenario: As an adherent I can login
    Given I am on "/connexion"
    And I fill in the following:
      | _login_email    | luciole1989@spambox.fr |
      | _login_password | secret!12345           |
    When I press "Connexion"
    Then I should be on "/evenements"
