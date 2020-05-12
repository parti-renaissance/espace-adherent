Feature: Make sure we can see and interact with organizational chart
  In order to have an organizational chart
  As a root referent
  I need to be able to see and edit an organizational chart

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                |
      | LoadOrganizationalChartItemData |

  Scenario: As a root referent I can see and click to access to organizational chart page
    Given I am logged as "referent@en-marche-dev.fr"
    And I am on "/espace-referent/utilisateurs"
    When I follow "Mon équipe"
    Then I should see "Référent départemental"
    And I should see "Responsable logistique Nom du responsable"
    And I should see "Jean Dupont Co-Référent"
    And I should see "Accès adhérents"
    And I should see "Lucie Olivera Responsable contenus"
    And I should see "Accès adhérents (limité)"
    And I should see 2 "span.referent-person-link:contains('Accès adhérents')" elements
    And I should see 1 "span.referent-person-link:contains('Accès adhérents (limité)')" elements

  Scenario: As a child referent I can't see and click to access to organizational chart page
    Given I am logged as "referent-child@en-marche-dev.fr"
    And I am on "/espace-referent/utilisateurs"
    Then I should not see "Mon équipe"
    When I am on "/espace-referent/mon-equipe"
    Then the response status code should be 403

  Scenario: As a root referent I can edit a node in the organizational chart
    Given I am logged as "referent@en-marche-dev.fr"
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

    When I follow "Nom du responsable Responsable Financier"
    And I fill in the following:
      | Nom             | VASSEUR                   |
      | Prénom          | Michel                    |
      | E-mail          | michel.vasseur@example.ch |
      | Téléphone       | 0698765432                |
      | Adresse postale | 12 Pilgerweg              |
    And I press "Sauvegarder"
    Then I should be on "/espace-referent/mon-equipe"
    And I should see "Michel VASSEUR Responsable Financier"

    When I follow "Michel VASSEUR Responsable Financier"
    Then I should see 1 "#referent_person_link_coReferent" element
    And I should see "Donner un accès à l'onglet Adhérents"
    And the "referent_person_link[coReferent][]" checkbox should not be checked

  Scenario: As a root referent I can transform a member to co-referent
    Given I am logged as "referent@en-marche-dev.fr"
    And I am on "/espace-referent/mon-equipe"

    When I follow "Carl Mirabeau Responsable digital"
    Then the "referent_person_link[coReferent][]" checkbox should not be checked
    And I check "referent_person_link_coReferent_0"
    And I press "Sauvegarder"
    Then I should be on "/espace-referent/mon-equipe"

    When I follow "Carl Mirabeau Responsable digital"
    Then the "referent_person_link_coReferent_0" checkbox should be checked

    When I uncheck "referent_person_link_coReferent_0"
    And I press "Sauvegarder"
    Then I should be on "/espace-referent/mon-equipe"

    When I follow "Carl Mirabeau Responsable digital"
    Then the "referent_person_link[coReferent][]" checkbox should not be checked

  Scenario: As a root referent I can transform a member to co-referent with limited access
    Given I am logged as "referent@en-marche-dev.fr"
    And I am on "/espace-referent/mon-equipe"

    When I follow "Carl Mirabeau Responsable digital"
    And the "referent_person_link[coReferent][]" checkbox should not be checked
    And I check "referent_person_link_coReferent_1"
    And I press "Sauvegarder"
    Then I should be on "/espace-referent/mon-equipe"

    When I follow "Carl Mirabeau Responsable digital"
    Then the "referent_person_link_coReferent_1" checkbox should be checked

    When I uncheck "referent_person_link_coReferent_1"
    And I press "Sauvegarder"
    Then I should be on "/espace-referent/mon-equipe"

    When I follow "Carl Mirabeau Responsable digital"
    And the "referent_person_link[coReferent][]" checkbox should not be checked

  Scenario: As a root referent I can add a J'ecoute manager
    Given I am logged as "referent@en-marche-dev.fr"
    And I am on "/espace-referent/mon-equipe"
    When I follow "Nom du responsable Responsable J'ecoute"
    Then I should see "Edition de Responsable J'ecoute"
    When I fill in the following:
      | Nom             | Jecoutino              |
      | Prénom          | Lucie                  |
      | E-mail          | luciole1989@spambox.fr |
      | Téléphone       | 0612345678             |
      | Adresse postale | 1 avenue du svelto     |
    And I check "referent_person_link_isJecouteManager"
    And I press "Sauvegarder"
    Then I should be on "/espace-referent/mon-equipe"
    And I should see "Organigramme mis à jour."
    And I should see "Lucie Jecoutino Responsable J'ecoute"
