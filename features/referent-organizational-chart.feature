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
    And I should see 3 "div.referent-person-link" elements
    And the "label[for='potential_co_referents_referentPersonLinks_1_isCoReferent']" element should contain "Donner un accès à l'onglet <b>Adhérents</b> en le nommant co-référent"
    And the "label[for='potential_co_referents_referentPersonLinks_2_isCoReferent']" element should contain "Donner un accès à l'onglet <b>Adhérents</b> en le nommant co-référent"
    And the "label[for='potential_co_referents_referentPersonLinks_3_isCoReferent']" element should contain "Donner un accès à l'onglet <b>Adhérents</b> en le nommant co-référent"
    And I should see 1 "span.already-co-referent" element

    When I follow "Nom du responsable Responsable logistique"
    Then I should see "Edition de Responsable logistique"
    And I should see 0 "#referent_person_link_isCoReferent" elements
    When I fill in the following:
      | Nom             | Jean                     |
      | Prénom          | Dupoint                  |
      | E-mail          | test@test.fr             |
      | Téléphone       | 0612345678               |
      | Adresse postale | 1 avenue des chez élisée |
    And I press "Sauvegarder"
    Then I should be on "/espace-referent/mon-equipe"
    And I should see "Dupoint Jean Responsable logistique"
    And I should see 3 "div.referent-person-link" elements
    And the "label[for='potential_co_referents_referentPersonLinks_1_isCoReferent']" element should contain "Donner un accès à l'onglet <b>Adhérents</b> en le nommant co-référent"
    And the "label[for='potential_co_referents_referentPersonLinks_2_isCoReferent']" element should contain "Donner un accès à l'onglet <b>Adhérents</b> en le nommant co-référent"
    And the "label[for='potential_co_referents_referentPersonLinks_3_isCoReferent']" element should contain "Donner un accès à l'onglet <b>Adhérents</b> en le nommant co-référent"
    And I should see 1 "span.already-co-referent" element

    When I follow "Nom du responsable Responsable Financier"
    And I fill in the following:
      | Nom             | Michel                    |
      | Prénom          | VASSEUR                   |
      | E-mail          | michel.vasseur@example.ch |
      | Téléphone       | 0698765432                |
      | Adresse postale | Station Javel             |
    And I press "Sauvegarder"
    Then I should be on "/espace-referent/mon-equipe"
    And I should see "VASSEUR Michel Responsable Financier"
    And I should see 4 "div.referent-person-link" elements
    And the "label[for='potential_co_referents_referentPersonLinks_1_isCoReferent']" element should contain "Donner un accès à l'onglet <b>Adhérents</b> en le nommant co-référent"
    And the "label[for='potential_co_referents_referentPersonLinks_2_isCoReferent']" element should contain "Donner un accès à l'onglet <b>Adhérents</b> en le nommant co-référent"
    And the "label[for='potential_co_referents_referentPersonLinks_3_isCoReferent']" element should contain "Donner un accès à l'onglet <b>Adhérents</b> en le nommant co-référent"
    And the "label[for='potential_co_referents_referentPersonLinks_5_isCoReferent']" element should contain "Donner un accès à l'onglet <b>Adhérents</b> en le nommant co-référent"
    And I should see 1 "span.already-co-referent" element
    And the "potential_co_referents_referentPersonLinks_1_isCoReferent" checkbox should be unchecked
    And the "potential_co_referents_referentPersonLinks_2_isCoReferent" checkbox should be unchecked
    And the "potential_co_referents_referentPersonLinks_3_isCoReferent" checkbox should be unchecked
    And the "potential_co_referents_referentPersonLinks_5_isCoReferent" checkbox should be unchecked

  Scenario: As a root referent I can transform a member to co-referent
    Given the following fixtures are loaded:
      | LoadAdherentData                |
      | LoadOrganizationalChartItemData |
    And I am logged as "referent@en-marche-dev.fr"
    And I am on "/espace-referent/mon-equipe"
    And the "potential_co_referents_referentPersonLinks_1_isCoReferent" checkbox should be unchecked
    And the "potential_co_referents_referentPersonLinks_2_isCoReferent" checkbox should be unchecked
    And the "potential_co_referents_referentPersonLinks_3_isCoReferent" checkbox should be unchecked
    When I check "potential_co_referents_referentPersonLinks_3_isCoReferent"
    And I press "Enregistrer"
    Then the "potential_co_referents_referentPersonLinks_1_isCoReferent" checkbox should be unchecked
    Then the "potential_co_referents_referentPersonLinks_2_isCoReferent" checkbox should be unchecked
    And the "potential_co_referents_referentPersonLinks_3_isCoReferent" checkbox should be checked

    When I follow "Carl Mirabeau Responsable digital"
    Then I should see 1 "#referent_person_link_isCoReferent" element
    And I should see "Pour pouvoir modifier le mail, veuillez décocher la case donnant les droits de co-référent et sauvegarder la modification."
    When I uncheck "referent_person_link_isCoReferent"
    And I press "Sauvegarder"
    Then I should be on "/espace-referent/mon-equipe"
    And I should see "Carl Mirabeau Responsable digital"
    And the "potential_co_referents_referentPersonLinks_1_isCoReferent" checkbox should be unchecked
    And the "potential_co_referents_referentPersonLinks_2_isCoReferent" checkbox should be unchecked
    And the "potential_co_referents_referentPersonLinks_3_isCoReferent" checkbox should be unchecked

    When I follow "Carl Mirabeau Responsable digital"
    And I check "referent_person_link_isCoReferent"
    And I press "Sauvegarder"
    Then I should be on "/espace-referent/mon-equipe"
    And I should see "Carl Mirabeau Responsable digital"
    And the "potential_co_referents_referentPersonLinks_1_isCoReferent" checkbox should be unchecked
    And the "potential_co_referents_referentPersonLinks_2_isCoReferent" checkbox should be unchecked
    And the "potential_co_referents_referentPersonLinks_3_isCoReferent" checkbox should be checked
