Feature: Make sure we can see and interact with organizational chart
  In order to have an organizational chart
  As a root referent
  I need to be able to see and edit an organizational chart

  Scenario: As a root referent I can see and click to access to organizational chart page
    Given the republican silence is disabled
    And I am logged as "referent@en-marche-dev.fr"
    And I am on "/espace-referent/utilisateurs"
    When I follow "Votre équipe départementale"
    Then I should see "Référent départemental"
    And I should see "Responsable logistique Entrez le nom du responsable"
    And I should see "Co-Référent Jean Dupont"

  Scenario: As a child referent I can't see and click to access to organizational chart page
    Given I am logged as "referent-child@en-marche-dev.fr"
    And I am on "/espace-referent/utilisateurs"
    Then I should not see "Votre équipe départementale"
    When I am on "/espace-referent/organigramme"
    Then the response status code should be 403

  Scenario: As a root referent I can edit a node in the organizational chart
    Given I am logged as "referent@en-marche-dev.fr"
    And I am on "/espace-referent/organigramme"
    When I follow "Responsable logistique Entrez le nom du responsable"
    Then I should see "Edition de Responsable logistique"
    When I fill in the following:
      | Nom             | Jean                     |
      | Prénom          | Dupoint                  |
      | E-mail          | test@test.fr             |
      | Téléphone       | 0612345678               |
      | Adresse postale | 1 avenue des chez élisée |
    And I press "Sauvegarder"
    Then I should be on "/espace-referent/organigramme"
    And I should see "Responsable logistique Dupoint Jean"
