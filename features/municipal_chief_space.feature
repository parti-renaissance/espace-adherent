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
  Scenario: I can see running mate request only for the zones I manage
    Given I am logged as "municipal-chief@en-marche-dev.fr"
    When I am on "/espace-chef-municipal/municipale/candidature-colistiers"
    Then I should see "Vous gérez : Lille, Oignies, Seclin"
    And I should see 3 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "Camphin-en-Carembault, Lille"
    And I should see "Mons-en-Pévèle, Seclin"
    And I should see "Seclin"

    When I click the "application-detail-0" element
    Then I should see "Retour à la liste des candidats colistiers"
    And I should see "Profession : Scientist"
    And I should see "Membre de l'association locale ? Non"
    And I should see "Domaine de l'association locale : Fighting super villains"
    And I should see "Activiste politique ? Non"
    And I should see "Activiste politique détails : Putsch Thanos from his galactic throne"
    And I should see "Est l'élu précédent ? Non"
    And I should see "Est l'élu précédent détails :"
    And I should see "Détails du projet :"
    And I should see "Actifs professionnels :"

  @javascript
  Scenario Outline: I can see running mate request only for the zones I manage
    Given I am logged as "<user>"
    When I am on "/espace-chef-municipal/municipale/candidature-colistiers"
    Then I should see "<managed-cities>"
    And I should see 2 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "<cities-tr-1>"
    And I should see "<cities-tr-2>"

    When I click the "application-detail-0" element
    Then I should see "Retour à la liste des candidats colistiers"
    And I should see "Profession : Scientist"
    And I should see "Membre de l'association locale ? Non"
    And I should see "Domaine de l'association locale : Fighting super villains"
    And I should see "Activiste politique ? Non"
    And I should see "Activiste politique détails : Putsch Thanos from his galactic throne"
    And I should see "Est l'élu précédent ? Non"
    And I should see "Est l'élu précédent détails :"
    And I should see "Détails du projet :"
    And I should see "Actifs professionnels :"

    Examples:
      | user                               | managed-cities                                        | cities-tr-1                        | cities-tr-2                        |
      | municipal-chief-2@en-marche-dev.fr | Vous gérez : Camphin-en-Carembault, Camphin-en-Pévèle | Camphin-en-Carembault, Lille       | Camphin-en-Pévèle, Mons-en-Baroeul |
      | municipal-chief-3@en-marche-dev.fr | Vous gérez : Mons-en-Baroeul, Mons-en-Pévèle          | Camphin-en-Pévèle, Mons-en-Baroeul | Mons-en-Pévèle, Seclin             |

  @javascript
  Scenario: I can see running mate request only for the zones I manage
    Given I am logged as "municipal-chief@en-marche-dev.fr"
    When I am on "/espace-chef-municipal/municipale/candidature-benevole"
    Then I should see "Vous gérez : Lille, Oignies, Seclin"
    And I should see 3 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "Camphin-en-Carembault, Lille"
    And I should see "Mons-en-Pévèle, Seclin"
    And I should see "Seclin"

    When I click the "application-detail-0" element
    Then I should see "Retour à la liste des candidats bénévoles"
    And I should see "Thèmes favoris : Sécurité Environnement"
    And I should see "Thèmes favoris personnalisés : Thanos destruction"
    And I should see "Compétences techniques : Communication Management Animation Autre"
    And I should see "Fait partie d'une précédente campagne ? Non"
    And I should see "Domaine de l'association locale :"
    And I should see "Partage l'engagement associatif ? Non"
    And I should see "Détail de l'engagement associatif :"

  @javascript
  Scenario Outline: I can see running mate request only for the zones I manage
    Given I am logged as "<user>"
    When I am on "/espace-chef-municipal/municipale/candidature-benevole"
    Then I should see "<managed-cities>"
    And I should see 2 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "<cities-tr-1>"
    And I should see "<cities-tr-2>"

    When I click the "application-detail-0" element
    Then I should see "Retour à la liste des candidats bénévoles"
    And I should see "Thèmes favoris : Sécurité Environnement"
    And I should see "Thèmes favoris personnalisés : Thanos destruction"
    And I should see "Compétences techniques : Communication Management Animation Autre"
    And I should see "Fait partie d'une précédente campagne ? Non"
    And I should see "Domaine de l'association locale :"
    And I should see "Partage l'engagement associatif ? Non"
    And I should see "Détail de l'engagement associatif :"

    Examples:
      | user                               | managed-cities                                        | cities-tr-1                        | cities-tr-2                        |
      | municipal-chief-2@en-marche-dev.fr | Vous gérez : Camphin-en-Carembault, Camphin-en-Pévèle | Camphin-en-Carembault, Lille       | Camphin-en-Pévèle, Mons-en-Baroeul |
      | municipal-chief-3@en-marche-dev.fr | Vous gérez : Mons-en-Baroeul, Mons-en-Pévèle          | Camphin-en-Pévèle, Mons-en-Baroeul | Mons-en-Pévèle, Seclin             |
