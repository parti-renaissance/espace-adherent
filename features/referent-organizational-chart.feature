Feature: Make sure we can see and interact with organizational chart
  In order to have an organizational chart
  As a root referent
  I need to be able to see and edit an organizational chart

  Scenario: As a root referent I can see and click to access to organizational chart page
    Given the following fixtures are loaded:
      | LoadAdherentData                |
      | LoadOrganizationalChartItemData |
    And I am logged as "referent@en-marche-dev.fr"
    And I am on "/espace-referent/utilisateurs"
    When I follow "Mon équipe"
    Then I should see "Référent départemental"
    And I should see "Responsable logistique Nom du responsable"
    And I should see "Jean Dupont Co-Référent"

  Scenario: As a child referent I can't see and click to access to organizational chart page
    Given the following fixtures are loaded:
      | LoadAdherentData                |
      | LoadOrganizationalChartItemData |
    And I am logged as "referent-child@en-marche-dev.fr"
    And I am on "/espace-referent/utilisateurs"
    Then I should not see "Mon équipe"
    When I am on "/espace-referent/mon-equipe"
    Then the response status code should be 403

  Scenario: As a root referent I can edit a node in the organizational chart
    Given the following fixtures are loaded:
      | LoadAdherentData                |
      | LoadOrganizationalChartItemData |
    And I am logged as "referent@en-marche-dev.fr"
    And I am on "/espace-referent/mon-equipe"
    When I follow "Nom du responsable Responsable logistique"
    Then I should see "Edition de Responsable logistique"
    When I fill in the following:
      | Nom             | Jean                     |
      | Prénom          | Dupoint                  |
      | E-mail          | test@test.fr             |
      | Téléphone       | 0612345678               |
      | Adresse postale | 1 avenue des chez élisée |
    And I press "Sauvegarder"
    Then I should be on "/espace-referent/mon-equipe"
    And I should see "Dupoint Jean Responsable logistique"
