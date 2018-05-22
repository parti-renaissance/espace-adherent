Feature:
  In order to get committees' information
  As a referent
  I should be able to acces committees API data

  Background:
    Given the following fixtures are loaded:
      | LoadUserData      |
      | LoadAdherentData  |

  Scenario: As a non logged-in user I can not access the committee supervisors count managed by referent information
    When I am on "/api/committees/count-for-referent-area"
    Then the response status code should be 200
    And I should be on "/connexion"

  Scenario: As an adherent I can not access the committee supervisors count managed by referent information
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/committees/count-for-referent-area"
    Then the response status code should be 403

  Scenario: As a referent I can access the committee supervisors count managed by referent information
    When I am logged as "referent@en-marche-dev.fr"
    And I am on "/api/committees/count-for-referent-area"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "committees":4,
      "supervisors": {
        "female":1,
        "male":2,
        "total":3
      }
    }
    """
