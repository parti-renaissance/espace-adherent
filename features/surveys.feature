@surveys
Feature:
  As a Jecoute manager
  I can manage the local surveys and see the nationcal surveys

  Background:
    Given the following fixtures are loaded:
      | LoadJecouteSurveyData      |
      | LoadJecouteDataSurveyData  |
      | LoadJecouteDataAnswerData  |

  @javascript
  Scenario: I can create a local survey, edit it and show the statistics page
    Given I am logged as "damien.schmidt@example.ch"
    When I am on "/espace-responsable-jecoute"
    Then I should see "Mon espace J'écoute"
    And I should see "Vous gérez : CH, ES, 92, 76, 77, 13, 59"

    # Create
    Given I am on "/espace-responsable-jecoute/questionnaire/creer"
    And I press "OK"
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

  @javascript
  Scenario: I can see the national surveys list and show the statistics
    Given I am logged as "damien.schmidt@example.ch"
    When I am on "/espace-responsable-jecoute/questionnaires-nationaux"
    And I should see "Questionnaires nationaux"
    And I should see "Questionnaire national numéro 1"
    Given I hover "table.datagrid__table-manager tbody tr td div.action-menu-oval"
    And I follow "Statistiques"
    Then I should see "Statistiques : Questionnaire national numéro 1"
    And I should see "Une première question du 1er questionnaire national ?"
    And I should see "Aucune donnée n'est disponible pour le moment."
