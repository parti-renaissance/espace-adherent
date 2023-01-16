@javascript
@javascript3
Feature:
  As a referent
  In order to see adherents, committees of my managed area
  I should be able to access my referent space

  Background:
    Given the following fixtures are loaded:
      | LoadReferentTagsZonesLinksData                |
      | LoadReferentTagData                           |
      | LoadGeoZoneData                               |
      | LoadAdherentData                              |
      | LoadApplicationRequestRunningMateRequestData  |
      | LoadApplicationRequestVolunteerRequestData    |
      | LoadJecouteSurveyData                         |
      | LoadJemarcheDataSurveyData                    |
      | LoadJecouteDataAnswerData                     |

  Scenario: I can see the local surveys list, edit a survey and show the statistics
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/questionnaires"
    And I should see "Questionnaires locaux"
    And I wait until I see "Questionnaire numéro 1"
    And I should see "Un deuxième questionnaire"

    Given I hover "table.datagrid__table-manager tbody tr td div.action-menu-oval"
    And I follow "Éditer"
    Then I should see "Nom du questionnaire"
    And I should see "Enregistrer le questionnaire"

    Given I fill in "Nom du questionnaire" with "Questionnaire numéro 1 modifié"
    When I press "Enregistrer le questionnaire"
    And I should see "Le questionnaire a bien été mis à jour"

    Given I wait until I see "Questionnaire numéro 1 modifié"
    And I hover "table.datagrid__table-manager tbody tr td div.action-menu-oval"
    And I follow "Statistiques"
    Then I should see "Statistiques : Questionnaire numéro 1"
    And I should see "Est-ce une question à choix multiple ?"
    And I should see "66,67 %"
    And I should see "Réponse A"

  Scenario: I can see the national surveys list and show the statistics
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/questionnaires/questionnaires-nationaux"
    And I should see "Questionnaires nationaux"
    And I should see "Questionnaire national numéro 1"
    Given I hover "table.datagrid__table-manager tbody tr td div.action-menu-oval"
    And I follow "Statistiques"
    Then I should see "Statistiques : Questionnaire national numéro 1"
    And I should see "Une première question du 1er questionnaire national ?"
    And I should see "Voir les 2 réponses"
    And I should see "Une première question du 1er questionnaire national ?"
    And I should see "4 réponses"
    And I should see "25 %"
