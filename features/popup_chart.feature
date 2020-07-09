@javascript
@popupchart
Feature:
  As deputy or a referent
  I must have a popup when I am on my space only if I didn't accept it before

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData      |
      | LoadDistrictData      |

  Scenario: As deputy, I should see a popup when I'm in the deputy space
    Given I am logged as "deputy@en-marche-dev.fr"
    And I am on "parametres/mon-compte"
    When I follow "Espace député"
    Then I should be on "espace-depute/utilisateurs"
    And I should see "Charte de bonne utilisation des outils numériques - à l’usage des députés"

  Scenario: As deputy, I should be redirected if I close the popup when I'm in the deputy space
    Given I am logged as "deputy@en-marche-dev.fr"
    And I am on "espace-depute/messagerie"
    And I should see "Charte de bonne utilisation des outils numériques - à l’usage des députés"
    When I press "close"
    Then I should be on "/"

  Scenario: As deputy, I should stay on the same page if I accept the condition
    Given I am logged as "deputy@en-marche-dev.fr"
    And I am on "espace-depute/messagerie"
    And I should see "Charte de bonne utilisation des outils numériques - à l’usage des députés"
    When I press "J'ai lu et j'accepte"
    Then I should be on "espace-depute/messagerie"

  Scenario: As referent, I should see a popup when I'm in the referent space
    Given I am logged as "referent-75-77@en-marche-dev.fr"
    And I am on "parametres/mon-compte"
    When I follow "Espace référent"
    Then I should be on "espace-referent/utilisateurs"
    And I should see "Charte de bonne utilisation des outils numériques - à l’usage des référents"

  Scenario: As referent, I should be redirected if I close the popup when I'm in the referent space
    Given I am logged as "referent-75-77@en-marche-dev.fr"
    And I am on "espace-referent/evenements"
    And I should see "Charte de bonne utilisation des outils numériques - à l’usage des référents"
    When I press "close"
    Then I should be on "/"

  Scenario: As referent, I should stay on the same page if I accept the condition
    Given I am logged as "referent-75-77@en-marche-dev.fr"
    And I am on "espace-referent/evenements"
    And I should see "Charte de bonne utilisation des outils numériques - à l’usage des référents"
    When I press "J'ai lu et j'accepte"
    Then I should be on "espace-referent/evenements"
