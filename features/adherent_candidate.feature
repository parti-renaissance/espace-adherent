@group03
Feature:
  As a candidate
  I should be able to access my candidate space and see concerned information

  Background:
    Given the following fixtures are loaded:
      | LoadReferentTagsZonesLinksData  |
      | LoadReferentTagData             |
      | LoadGeoZoneData                 |
      | LoadAdherentData                |
      | LoadJecouteSurveyData           |
      | LoadJemarcheDataSurveyData      |
      | LoadJecouteDataAnswerData       |

  Scenario: As a headed regional candidate I can access user list in candidate space
    Given I am logged as "jacques.picard@en-marche.fr"
    When I am on "/espace-candidat/utilisateurs"
    And the response status code should be 200

  @javascript
  Scenario: As a headed regional candidate I can see the local surveys list and their statistics
    Given I am logged as "jacques.picard@en-marche.fr"
    When I am on "/espace-candidat/questionnaires"
    And I should see "Vous gérez : Île-de-France (11)"
    And I should see "Questionnaires locaux"
    And I wait until I see "Un questionnaire de la Région"
    And I should see 3 "table.datagrid__table-manager tbody tr" elements
    And I should see "Questionnaire numéro 1"
    And I should see "Un questionnaire de la Région"
    And I should see "Un questionnaire avec modification bloquée"
    And I should see 1 "table.datagrid__table-manager tr:contains('Questionnaire numéro 1') .action-menu-oval:contains('Éditer')" elements
    And I should see 1 "table.datagrid__table-manager tr:contains('Un questionnaire de la Région') .action-menu-oval:contains('Éditer')" elements
    And I should see 1 "table.datagrid__table-manager tr:contains('Un questionnaire avec modification bloquée') .action-menu-oval:contains('Voir')" elements

    When I hover "table.datagrid__table-manager tbody tr td div.action-menu-oval"
    And I follow "Statistiques"
    Then I should see "Statistiques : Questionnaire numéro 1"
    And I should see "Aucune donnée n'est disponible pour le moment."
    And I should see "⟵ Retour"

    When I follow "⟵ Retour"
    Then I should be on "/espace-candidat/questionnaires"

  @javascript
  Scenario: As a departmental candidate I can see the local surveys list
    Given I am logged as "francis.brioul@yahoo.com"
    When I am on "/espace-candidat/questionnaires"
    Then I should see "Vous gérez : Melun (7711)"
    And I should see "Questionnaires locaux"
    And I should not see "Créer un questionnaire local"
    And I wait until I see "Un questionnaire de la Région"
    And I should see 2 "table.datagrid__table-manager tbody tr" elements
    And I should see "Questionnaire numéro 1"
    And I should see "Un questionnaire de la Région"

    And I should see 1 "table.datagrid__table-manager tr:contains('Questionnaire numéro 1') .action-menu-oval:contains('Voir')" elements
    And I should see 1 "table.datagrid__table-manager tr:contains('Un questionnaire de la Région') .action-menu-oval:contains('Voir')" elements
