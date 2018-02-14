Feature:
  In order to get adherents' information
  As a referent
  I should be able to acces adherents API data

  Background:
    Given the following fixtures are loaded:
      | LoadUserData      |
      | LoadAdherentData  |

  Scenario: As a non logged-in user I can not access the adherents count information
    When I am on "/api/adherents/count"
    Then the response status code should be 200
    And I should be on "/connexion"

  Scenario: As an adherent I can not access the adherents count information
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/adherents/count"
    Then the response status code should be 403

  Scenario: As a referent I can access the adherents count information
    When I am logged as "referent@en-marche-dev.fr"
    And I am on "/api/adherents/count"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "female":6,"male":11,"total":17
    }
    """

  Scenario: As a non logged-in user I can not access the managed by referent adherents count information
    When I am on "/api/adherents/count-by-referent-area"
    Then the response status code should be 200
    And I should be on "/connexion"

  Scenario: As an adherent I can not access the managed by referent adherents count information
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/adherents/count-by-referent-area"
    Then the response status code should be 403

  Scenario: As a referent I can access the managed by referent adherents count information
    When I am logged as "referent@en-marche-dev.fr"
    And I am on "/api/adherents/count-by-referent-area"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "female":1,"male":7,"total":8
    }
    """
