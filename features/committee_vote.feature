Feature:
  As an adherent I should be able to vote/unvote in followed committees

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData |

  @javascript
  Scenario: I can show a list of followed committees, then I can remove my candidacy and change the vote committee
    Given I am logged as "assesseur@en-marche-dev.fr"
    And I am on "/espace-adherent/mes-comites"
    Then I should see 4 ".adherent__activity--committee" elements
    And I should see "RETIRER LA CANDIDATURE" 1 times

    When I click the ".btn.btn--red.b__nudge--right-small" selector
    Then I should be on "/espace-adherent/mes-comites"
    And I should see 0 ".btn.btn--red.b__nudge--right-small" elements

    When I am on "/comites/en-marche-comite-de-rouen"
    Then I should see "JE CANDIDATE"

    When I am on "/comites/en-marche-comite-de-evry"
    Then I should see "Vous pouvez candidater dans le comité où vous avez choisi de voter. Rendez-vous sur cette page pour choisir ou modifier votre comité."

    When I am on "/espace-adherent/mes-comites"
    And I click the ".adherent__activity--committee .switch" selector
    Then I should see "Changement du comité de vote"
    And I should see "Vous êtes sur le point de changer votre comité de vote. Vous ne pourrez plus voter dans le comité En Marche - Comité de Rouen, êtes-vous sûr de vouloir maintenant voter dans le comité En Marche - Comité de Évry ?"
    And I should see "CONFIRMER"

    When I click the "button.btn.btn--blue" selector
    Then I wait 3 second until I see "En Marche - Comité de Évry"

    When I am on "/comites/en-marche-comite-de-rouen"
    Then I should see "Vous pouvez candidater dans le comité où vous avez choisi de voter. Rendez-vous sur cette page pour choisir ou modifier votre comité."

    When I am on "/comites/en-marche-comite-de-evry"
    Then I should see "JE CANDIDATE"

  @javascript
  Scenario: As member of the committee, I can see its candidacies modal
    Given I am logged as "assesseur@en-marche-dev.fr"
    When I am on "/comites/en-marche-comite-de-rouen"
    Then I should see "JE RETIRE MA CANDIDATURE"
    And I should see "Voir la liste des candidats"

    When I click the "candidacies-list-modal--trigger" element
    Then I wait 5 second until I see "Liste des candidat(e)s :"
    And I should see "Bob Assesseur"
