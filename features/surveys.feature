Feature:
  As referent
  I can see the surveys list, create a survey and edit it.

  Background:
    Given the following fixtures are loaded:
      | LoadJecouteSurveyData      |
      | LoadJecouteDataSurveyData  |
      | LoadJecouteDataAnswerData  |

  @javascript
  Scenario: I can see the surveys list, edit a survey and show the statistics
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/jecoute/questionnaires"
    And I should see "Questionnaires locaux"
    And I should see "Questionnaire numéro 1"
    And I should not see "Un deuxième questionnaire"

    Given I click the "survey-edit-0" element
    Then I should see "Nom du questionnaire"
    And I should see "Enregistrer le questionnaire"

    Given I fill in "Nom du questionnaire" with "Questionnaire numéro 1 modifié"
    When I press "Enregistrer le questionnaire"
    And I should see "Le questionnaire a bien été mis à jour"

    Given I click the "survey-stats-0" element
    Then I should see "Statistiques : Questionnaire numéro 1"
    And I should see "Est-ce une question à choix multiple ?"
    And I should see "66,67 %"
    And I should see "Réponse A"
