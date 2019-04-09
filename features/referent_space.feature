Feature:
  As a referent
  In order to see adherents, committees, citizen projects of my managed area
  I should be able to access my referent space

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData        |
      | LoadCitizenProjectData  |

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
