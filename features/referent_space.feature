Feature:
  As a referent
  In order to see adherents, committees, citizen projects of my managed area
  I should be able to access my referent space

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                             |
      | LoadCitizenProjectData                       |
      | LoadApplicationRequestRunningMateRequestData |
      | LoadApplicationRequestVolunteerRequestData   |
      | LoadJecouteSurveyData      |
      | LoadJecouteDataSurveyData  |
      | LoadJecouteDataAnswerData  |

  @javascript
  Scenario: I can see citizen projects of my managed area
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/projets-citoyens"
    Then I should see 5 "table.managed__list__table tbody tr" elements
    And I should see "Un stage pour tous"
    And I should see "Le projet citoyen à Dammarie-les-Lys"
    And I should see "En Marche - Projet citoyen"
    And I should see "Massive Open Online Course"
    And I should see "Formation en ligne ouverte à tous"

  # Municipales space
  @javascript
  Scenario: I cannot see running mate request for the zones I don't manage
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/municipale/candidature-colistiers"
    Then I should see "Aucun résultat" in the "#datagrid div table.managed__list__table tbody tr td" element

  @javascript
  Scenario: I cannot see running mate request for the zones I don't manage
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/municipale/candidature-benevole"
    Then I should see "Aucun résultat" in the "#datagrid div table.managed__list__table tbody tr td" element

  @javascript
  Scenario: I can see running mate request for the zones I manage, I can see the detail and I can add tags
    Given I am logged as "referent-75-77@en-marche-dev.fr"
    When I am on "/espace-referent/municipale/candidature-colistiers"
    And I wait 10 seconds until I see "J'ai lu et j'accepte"
    And I accept terms of use
    And I wait 10 seconds until I see NOM
    And I should see "Banner"
    And I should see "Bruce"
    And I should see "+33 6 06 06 06 06"
    And I should see "Camphin-en-Pévèle, Mons-en-Baroeul"
    And I should see "Seclin"
    And I should see "Télécharger le CV"
    And I should see "Oui"

    When I click the "application-detail-0" element
<<<<<<< HEAD
    And I wait 10 seconds until I see "Retour à la liste des candidats colistiers"
    Then I should see "Profession : Scientist"
=======
    Then I should see "⟵ Retour"
    And I should see "Profession : Scientist"
>>>>>>> Add style on candidate detail view
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
  Scenario: I can see volunteer request for the zones I manage and I can see the detail
    Given I am logged as "referent-75-77@en-marche-dev.fr"
    When I am on "/espace-referent/municipale/candidature-benevole"
    And I wait 10 seconds until I see "J'ai lu et j'accepte"
    And I accept terms of use
    And I wait 10 seconds until I see NOM
    And I should see "Stark"
    And I should see "Tony"
    And I should see "Camphin-en-Pévèle, Mons-en-Baroeul"
    And I should see "Seclin"
    And I should see "Oui"

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
  Scenario: I can see the local surveys list, edit a survey and show the statistics
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/jecoute"
    And I should see "Questionnaires locaux"
    And I wait until I see "Questionnaire numéro 1"
    And I should not see "Un deuxième questionnaire"

    Given I click the "survey-edit-0" element
    Then I should see "Nom du questionnaire"
    And I should see "Enregistrer le questionnaire"

    Given I fill in "Nom du questionnaire" with "Questionnaire numéro 1 modifié"
    When I press "Enregistrer le questionnaire"
    And I should see "Le questionnaire a bien été mis à jour"

    Given I wait until I see "Questionnaire numéro 1 modifié"
    And I click the "survey-stats-0" element
    Then I should see "Statistiques : Questionnaire numéro 1"
    And I should see "Est-ce une question à choix multiple ?"
    And I should see "66,67 %"
    And I should see "Réponse A"

  @javascript
  Scenario: I can see the national surveys list and show the statistics
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/espace-referent/jecoute/questionnaires-nationaux"
    And I should see "Questionnaires nationaux"
    And I should see "Questionnaire national numéro 1"
    Given I click the "survey-stats-0" element
    Then I should see "Statistiques : Questionnaire national numéro 1"
    And I should see "Une première question du 1er questionnaire national ?"
    And I should see "Aucune donnée n'est disponible pour le moment."
