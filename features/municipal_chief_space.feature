Feature:
  As a municipal chief
  In order to see application request of my managed area
  I should be able to access my municipal chief space

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                             |
      | LoadApplicationRequestRunningMateRequestData |
      | LoadApplicationRequestVolunteerRequestData   |

  @javascript
  Scenario: I can see running mate request for the zones I manage, I can see the detail and I can add tags
    Given I am logged as "municipal-chief@en-marche-dev.fr"
    When I am on "/espace-chef-municipal/municipale/candidature-colistiers"
    Then I should see "Vous gérez : Lille, Oignies, Seclin"
    And I should see 3 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "Camphin-en-Carembault, Lille"
    And I should see "Mons-en-Pévèle, Seclin"
    And I should see "Seclin"

    When I click the "application-detail-0" element
    Then I should see "⟵ Retour"
    And I should see "Profession Scientist"
    And I should see "Membre de l'association locale ? Non"
    And I should see "Domaine de l'association locale : Fighting super villains"
    And I should see "Activiste politique ? Non"
    And I should see "Activiste politique détails : Putsch Thanos from his galactic throne"
    And I should see "Est l'élu précédent ? Non"
    And I should see "Est l'élu précédent détails :"
    And I should see "Détails du projet :"
    And I should see "Actifs professionnels :"

    When I click the ".back-to-list" selector
    And I click the "application-edit-0" element
    Then I wait 10 seconds until I see "Tags de candidature"

    When I select "4" from "application_request_tags_tags"
    And I press "Enregistrer"
    And I wait 10 seconds until I see NOM
    Then the 5 column of the 1 row in the table.managed__list__table table should contain "Tag 4"

  @javascript
  Scenario Outline: I can see running mate request for the zones I manage, I can see the detail and I can add tags
    Given I am logged as "<user>"
    When I am on "/espace-chef-municipal/municipale/candidature-colistiers"
    Then I should see "<managed-cities>"
    And I should see 2 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "<cities-tr-1>"
    And I should see "<cities-tr-2>"

    When I click the "application-detail-0" element
    Then I should see "⟵ Retour"
    And I should see "Profession : Scientist"
    And I should see "Membre de l'association locale ? Non"
    And I should see "Domaine de l'association locale : Fighting super villains"
    And I should see "Activiste politique ? Non"
    And I should see "Activiste politique détails : Putsch Thanos from his galactic throne"
    And I should see "Est l'élu précédent ? Non"
    And I should see "Est l'élu précédent détails :"
    And I should see "Détails du projet :"
    And I should see "Actifs professionnels :"

    When I click the ".back-to-list" selector
    And I click the "application-edit-0" element
    Then I wait 10 seconds until I see "Tags de candidature"

    When I select "4" from "application_request_tags_tags"
    And I press "Enregistrer"
    And I wait 10 seconds until I see NOM
    Then the 5 column of the 1 row in the table.managed__list__table table should contain "Tag 4"

    Examples:
      | user                               | managed-cities                                        | cities-tr-1                        | cities-tr-2                        |
      | municipal-chief-2@en-marche-dev.fr | Vous gérez : Camphin-en-Carembault, Camphin-en-Pévèle | Camphin-en-Carembault, Lille       | Camphin-en-Pévèle, Mons-en-Baroeul |
      | municipal-chief-3@en-marche-dev.fr | Vous gérez : Mons-en-Baroeul, Mons-en-Pévèle          | Camphin-en-Pévèle, Mons-en-Baroeul | Mons-en-Pévèle, Seclin             |

  @javascript
  Scenario: I can see volunteer request for the zones I manage, I can see the detail and I can add tags
    Given I am logged as "municipal-chief@en-marche-dev.fr"
    When I am on "/espace-chef-municipal/municipale/candidature-benevole"
    Then I should see "Vous gérez : Lille, Oignies, Seclin"
    And I should see 3 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "Camphin-en-Carembault, Lille"
    And I should see "Mons-en-Pévèle, Seclin"
    And I should see "Seclin"

    When I click the "application-detail-0" element
    Then I should see "⟵ Retour"
    And I should see "Thèmes favoris Sécurité Environnement"
    And I should see "Thèmes favoris personnalisés Thanos destruction"
    And I should see "Compétences techniques : Communication Management Animation Autre"
    And I should see "Fait partie d'une précédente campagne ? Non"
    And I should see "Domaine de l'association locale :"
    And I should see "Partage l'engagement associatif ? Non"
    And I should see "Détail de l'engagement associatif :"

    When I click the ".back-to-list" selector
    And I click the "application-edit-0" element
    Then I wait 10 seconds until I see "Tags de candidature"

    When I select "4" from "application_request_tags_tags"
    And I press "Enregistrer"
    And I wait 10 seconds until I see NOM
    Then the 5 column of the 1 row in the table.managed__list__table table should contain "Tag 4"

  @javascript
  Scenario Outline: I can see volunteer request for the zones I manage, I can see the detail and I can add tags
    Given I am logged as "<user>"
    When I am on "/espace-chef-municipal/municipale/candidature-benevole"
    Then I should see "<managed-cities>"
    And I should see 2 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "<cities-tr-1>"
    And I should see "<cities-tr-2>"

    When I click the "application-detail-0" element
    Then I should see "⟵ Retour"
    And I should see "Thèmes favoris Sécurité Environnement"
    And I should see "Thèmes favoris personnalisés Thanos destruction"
    And I should see "Compétences techniques : Communication Management Animation Autre"
    And I should see "Fait partie d'une précédente campagne ? Non"
    And I should see "Domaine de l'association locale :"
    And I should see "Partage l'engagement associatif ? Non"
    And I should see "Détail de l'engagement associatif :"

    When I click the ".back-to-list" selector
    And I click the "application-edit-0" element
    Then I wait 10 seconds until I see "Tags de candidature"

    When I select "4" from "application_request_tags_tags"
    And I press "Enregistrer"
    And I wait 10 seconds until I see NOM
    Then the 5 column of the 1 row in the table.managed__list__table table should contain "Tag 4"

    Examples:
      | user                               | managed-cities                                        | cities-tr-1                        | cities-tr-2                        |
      | municipal-chief-2@en-marche-dev.fr | Vous gérez : Camphin-en-Carembault, Camphin-en-Pévèle | Camphin-en-Carembault, Lille       | Camphin-en-Pévèle, Mons-en-Baroeul |
      | municipal-chief-3@en-marche-dev.fr | Vous gérez : Mons-en-Baroeul, Mons-en-Pévèle          | Camphin-en-Pévèle, Mons-en-Baroeul | Mons-en-Pévèle, Seclin             |
