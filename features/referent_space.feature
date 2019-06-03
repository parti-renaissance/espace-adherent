Feature:
  As a referent
  In order to see adherents, committees, citizen projects of my managed area
  I should be able to access my referent space

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                             |
      | LoadCitizenProjectData                       |
      | LoadApplicationRequestRunningMateRequestData |
      | LoadApplicationRequestVolunteerRequestData   |

  @javascript
  Scenario: I can see citizen projects of my managed area
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/projets-citoyens"
    Then I should see 5 "table.managed__list__table tbody tr" elements
    And I should see "Un stage pour tous"
    And I should see "Le projet citoyen à Dammarie-les-Lys"
    And I should see "En Marche - Projet citoyen"
    And I should see "Massive Open Online Course"
    And I should see "Formation en ligne ouverte à tous"

  # Municipales space
  @javascript
  Scenario: I cannot see running mate request for the zones i don't manage
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/municipale/candidature-colistiers"
    Then I should see "Aucun résultat" in the "#datagrid div table.managed__list__table tbody tr td" element

  @javascript
  Scenario: I cannot see running mate request for the zones i don't manage
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/municipale/candidature-benevole"
    Then I should see "Aucun résultat" in the "#datagrid div table.managed__list__table tbody tr td" element

  @javascript
  Scenario: I can see running mate request for the zones i manage
    Given I am logged as "referent-75-77@en-marche-dev.fr"
    When I am on "/espace-referent/municipale/candidature-colistiers"
    And I wait 2 seconds
    And I accept terms of use
    Then I wait 2 seconds
    And I should see "Banner"
    And I should see "Bruce"
    And I should see "+33 6 06 06 06 06"
    And I should see "New York"
    And I should see "Télécharger le CV"
    And I should see "Oui"

  @javascript @wip
  Scenario: I can see running mate request for the zones i manage
    Given I am logged as "referent-75-77@en-marche-dev.fr"
    When I am on "/espace-referent/municipale/candidature-benevole"
    And I wait 2 seconds
    And I accept terms of use
    Then I wait 2 seconds
    And I should see "Stark"
    And I should see "Tony"
    And I should see ""
    And I should see "Malibu,New-York"
    And I should see "Oui"
