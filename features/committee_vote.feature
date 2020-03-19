Feature:
  As an adherent I should be able to vote/unvote in followed committees

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData |

  @javascript
  Scenario: I can show a list of followed committees, then I can remove my candidacy and change the vote committee
    Given I am logged as "jacques.picard@en-marche.fr"
    And I am on "/espace-adherent/mes-comites"
    Then I should see 8 ".adherent__activity--committee" elements
    And I should see "RETIRER LA CANDIDATURE" 1 times

    When I click the ".btn.btn--red.b__nudge--right-small" selector
    Then I should be on "/espace-adherent/mes-comites"
    And I should see 0 ".btn.btn--red.b__nudge--right-small" elements

    When I am on "/comites/antenne-en-marche-de-fontainebleau"
    Then I should see "JE CANDIDATE"

    When I am on "/comites/en-marche-paris-8"
    Then I should see "Vous pouvez candidater dans le comité où vous avez choisi de voter. Rendez-vous sur cette page pour choisir ou modifier votre comité."

    When I am on "/espace-adherent/mes-comites"
    And I click the ".adherent__activity--committee .switch" selector
    Then I should see "Changement du comité de vote"
    And I should see "Vous êtes sur le point de changer votre comité de vote. Vous ne pourrez plus voter dans le comité Antenne En Marche de Fontainebleau, êtes-vous sûr de vouloir maintenant voter dans le comitéEn Marche Paris 8 ?"
    And I should see "CONFIRMER"

    When I click the "button.btn.btn--blue" selector
    Then I wait 3 second until I see "Marche Paris 8"

    When I am on "/comites/antenne-en-marche-de-fontainebleau"
    Then I should see "Vous pouvez candidater dans le comité où vous avez choisi de voter. Rendez-vous sur cette page pour choisir ou modifier votre comité."

    When I am on "/comites/en-marche-paris-8"
    Then I should see "JE CANDIDATE"
