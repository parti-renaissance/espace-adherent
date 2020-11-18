Feature:
  As a candidate
  I should be able to acces my candidate space

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData |

  Scenario: I can access user list in candidate space
    Given I am logged as "jacques.picard@en-marche.fr"
    When I am on "/espace-candidat/utilisateurs"
    And the response status code should be 200
