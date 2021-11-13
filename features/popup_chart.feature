@popupchart
@javascript
@group02
Feature:
  As deputy or a referent
  I must have a popup when I am on my space only if I didn't accept it before

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                  |
      | LoadDistrictData                  |
      | LoadReferentTagsZonesLinksData    |
      | LoadCommitteeData                 |
      | LoadCommitteeAdherentMandateData  |

  Scenario: As deputy, I should see a popup when I'm in the deputy space
    Given I am logged as "deputy@en-marche-dev.fr"
    And I am on "parametres/mon-compte/modifier"
    When I follow "Espace député"
    Then I should be on "espace-depute/utilisateurs"
    And I wait 3 second until I see "Charte de bonne utilisation des outils numériques"

  Scenario: As deputy, I should be redirected if I close the popup when I'm in the deputy space
    Given I am logged as "deputy@en-marche-dev.fr"
    And I am on "espace-depute/messagerie"
    And I wait 3 second until I see "Charte de bonne utilisation des outils numériques"
    When I press "close"
    Then I should be on "/"

  Scenario: As deputy, I should stay on the same page if I accept the condition
    Given I am logged as "deputy@en-marche-dev.fr"
    And I am on "espace-depute/messagerie"
    And I wait 3 second until I see "Charte de bonne utilisation des outils numériques"
    When I press "J'ai lu et j'accepte"
    Then I should be on "espace-depute/messagerie"

  Scenario: As referent, I should see a popup when I'm in the referent space
    Given I am logged as "referent-75-77@en-marche-dev.fr"
    And I am on "parametres/mon-compte/modifier"
    When I follow "Espace référent"
    Then I should be on "espace-referent/utilisateurs"
    And I wait 3 second until I see "Charte de bonne utilisation des outils numériques"

  Scenario: As referent, I should be redirected if I close the popup when I'm in the referent space
    Given I am logged as "referent-75-77@en-marche-dev.fr"
    And I am on "espace-referent/evenements"
    And I wait 3 second until I see "Charte de bonne utilisation des outils numériques"
    When I press "close"
    Then I should be on "/"

  Scenario: As referent, I should stay on the same page if I accept the condition
    Given I am logged as "referent-75-77@en-marche-dev.fr"
    And I am on "espace-referent/evenements"
    And I wait 3 second until I see "Charte de bonne utilisation des outils numériques"
    When I press "J'ai lu et j'accepte"
    Then I should be on "espace-referent/evenements"

  Scenario: As supervisor, I should be redirected if I close the popup when I'm in the deputy space
    Given I am logged as "martine.lindt@gmail.com"
    And I am on "/espace-animateur/en-marche-comite-de-berlin/designations"
    And I wait 3 second until I see "Charte de bonne utilisation des outils numériques"
    When I press "close"
    Then I should be on "/"

  Scenario: As supervisor, I should stay on the same page if I accept the condition
    Given I am logged as "martine.lindt@gmail.com"
    And I am on "/espace-animateur/en-marche-comite-de-berlin/designations"
    And I wait 3 second until I see "Charte de bonne utilisation des outils numériques"
    When I press "J'ai lu et j'accepte"
    Then I should be on "/espace-animateur/en-marche-comite-de-berlin/designations"

  Scenario: As provisional supervisor, I should be redirected if I close the popup when I'm in the deputy space
    Given I am logged as "senatorial-candidate@en-marche-dev.fr"
    And I am on "/espace-animateur/en-marche-comite-de-berlin/messagerie"
    And I wait 3 second until I see "Charte de bonne utilisation des outils numériques"
    When I press "close"
    Then I should be on "/"

  Scenario: As provisional supervisor, I should stay on the same page if I accept the condition
    Given I am logged as "senatorial-candidate@en-marche-dev.fr"
    And I am on "/espace-animateur/en-marche-comite-de-berlin/messagerie"
    And I wait 3 second until I see "Charte de bonne utilisation des outils numériques"
    When I press "J'ai lu et j'accepte"
    Then I should be on "/espace-animateur/en-marche-comite-de-berlin/messagerie"

  Scenario: As host, I should be redirected if I close the popup when I'm in the deputy space
    Given I am logged as "martine.lindt@gmail.com"
    And I am on "/comites/en-marche-allemagne/membres"
    And I wait 3 second until I see "Charte de bonne utilisation des outils numériques"
    When I press "close"
    Then I should be on "/"

  Scenario: As host, I should stay on the same page if I accept the condition
    Given I am logged as "martine.lindt@gmail.com"
    And I am on "/comites/en-marche-allemagne/membres"
    And I wait 3 second until I see "Charte de bonne utilisation des outils numériques"
    When I press "J'ai lu et j'accepte"
    Then I should be on "/comites/en-marche-allemagne/membres"
    When I am on "/comites/en-marche-allemagne/messagerie"
    And I should not see "Charte de bonne utilisation des outils numériques - à l'usage des animateurs, animateurs provisoires et co-animateurs"
