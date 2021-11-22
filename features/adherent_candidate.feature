@app
Feature:
  As a candidate
  I should be able to access my candidate space and see concerned information

  Scenario: As a headed regional candidate I can access user list in candidate space
    Given I am logged as "jacques.picard@en-marche.fr"
    When I am on "/espace-candidat/utilisateurs"
    And the response status code should be 200
