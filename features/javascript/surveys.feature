@javascript
Feature:
  As a Jecoute manager
  I can manage the local surveys and see the national surveys

  Background:
    Given the following fixtures are loaded:
      | LoadJecouteSurveyData       |
      | LoadJemarcheDataSurveyData  |
      | LoadJecouteDataAnswerData   |

  Scenario: I can see local surveys and their actions
    Given I am logged as "damien.schmidt@example.ch"
    When I am on "/espace-responsable-des-questionnaires"
    Then I should see "Mon espace responsable des questionnaires"
    And I should see "Vous gérez : Seine-et-Marne (77)"
    And I should see 2 "table.datagrid__table-manager tbody tr" elements
    And I should see "Questionnaire numéro 1"
    And I should see "Un questionnaire de la Région"

    And I should see 1 "table.datagrid__table-manager tr:contains('Questionnaire numéro 1') .action-menu-oval:contains('Éditer')" elements
    And I should see 0 "table.datagrid__table-manager tr:contains('Questionnaire numéro 1') .action-menu-oval:contains('Voir')" elements
    And I should see 1 "table.datagrid__table-manager tr:contains('Questionnaire numéro 1') .action-menu-oval:contains('Statistiques')" elements
    And I should see 1 "table.datagrid__table-manager tr:contains('Questionnaire numéro 1') .action-menu-oval:contains('Télécharger les résultats')" elements

    And I should see 0 "table.datagrid__table-manager tr:contains('Un questionnaire de la Région') .action-menu-oval:contains('Éditer')" elements
    And I should see 1 "table.datagrid__table-manager tr:contains('Un questionnaire de la Région') .action-menu-oval:contains('Voir')" elements
    And I should see 1 "table.datagrid__table-manager tr:contains('Un questionnaire de la Région') .action-menu-oval:contains('Statistiques')" elements
    And I should see 1 "table.datagrid__table-manager tr:contains('Un questionnaire de la Région') .action-menu-oval:contains('Télécharger les résultats')" elements

  Scenario: I can create a local survey, edit it and show the statistics page
    # Create
    Given I am logged as "damien.schmidt@example.ch"
    And I am on "/espace-responsable-des-questionnaires/creer"
    When I wait 10 seconds until I see "⟵ Retour"
    And I fill in the following:
      | survey_form[name]                            | Un questionnaire jecoute manager |
      | survey_form[questions][0][question][content] | Une question ?                   |
    And I wait 10 seconds until I see "Champ libre"
    And I click the "#survey_form_questions_0_question_type .form__radio:nth-child(3) > label" selector
    And I press "Enregistrer le questionnaire local"
    And I wait 10 seconds until I see "NOM DU QUESTIONNAIRE"
    Then I should see "Le questionnaire a bien été enregistré."

    # Edit
    Given I should see "Un questionnaire jecoute manager"
    When I hover "table.datagrid__table-manager tbody tr td div.action-menu-oval"
    And I follow "Éditer"
    And I should see "Nom du questionnaire"
    And I should see "Enregistrer le questionnaire"

    Given I fill in "Nom du questionnaire" with "Un questionnaire jecoute manager (modifié)"
    When I press "Enregistrer le questionnaire"
    Then I should see "Le questionnaire a bien été mis à jour."

    # Show statistics
    Given I wait until I see "Un questionnaire jecoute manager (modifié)"
    And I hover "table.datagrid__table-manager tbody tr td div.action-menu-oval"
    And I follow "Statistiques"
    Then I should see "Statistiques : Un questionnaire jecoute manager (modifié)"
    And I should see "Aucune donnée n'est disponible pour le moment."

  Scenario: I can see the national surveys list and see the statistics
    Given I am logged as "damien.schmidt@example.ch"
    When I am on "/espace-responsable-des-questionnaires/questionnaires-nationaux"
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
