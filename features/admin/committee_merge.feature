@app
Feature: Merge committees from admin panel

  Scenario: A committee can not be merged if it is not approved
    Given I am logged as "superadmin@en-marche-dev.fr" admin
    And I am on "/admin/app/reporting-committeemergehistory/merge"
    When I fill in the following:
      | ID du comité source         | 2 |
      | ID du comité de destination | 1 |
    Then I press "Fusionner"
    Then the response status code should be 200
    And I should see "Le comité \"En Marche Marseille 3\" (2) doit être approuvé pour être fusionné."
    And I should not see "Confirmer la fusion"

  Scenario: A committee can not be merged if the destination committee is not approved
    Given I am logged as "superadmin@en-marche-dev.fr" admin
    And I am on "/admin/app/reporting-committeemergehistory/merge"
    When I fill in the following:
      | ID du comité source         | 1 |
      | ID du comité de destination | 2 |
    Then I press "Fusionner"
    Then the response status code should be 200
    And I should see "Le comité \"En Marche Marseille 3\" (2) doit être approuvé pour être fusionné."
    And I should not see "Confirmer la fusion"

  Scenario: A committee can not be merged into itself
    Given I am logged as "superadmin@en-marche-dev.fr" admin
    And I am on "/admin/app/reporting-committeemergehistory/merge"
    When I fill in the following:
      | ID du comité source         | 1 |
      | ID du comité de destination | 1 |
    Then I press "Fusionner"
    Then the response status code should be 200
    And I should see "Veuillez spécifier des comités différents."
    And I should not see "Confirmer la fusion"

  Scenario: A committee merge and revert must trigger events in RabbitMQ
    Given I am logged as "superadmin@en-marche-dev.fr" admin
    And I am on "/admin/committee/1/members"
    Then I should see 4 ".committee-members tbody tr" elements
    And I should see 1 ".label-primary:contains('Co-animateur')" elements
    And I should not see "francis.brioul@yahoo.com"
    When I am on "/admin/committee/3/mandates"
    Then I should see 5 ".committee-active-mandates tbody tr" elements
    And I should see 1 ".committee-inactive-mandates tbody tr" elements
    And I should see "Pas de mandats" in the ".committee-inactive-mandates tbody tr" element
    When I am on "/admin/committee/1/mandates"
    Then I should see 3 ".committee-active-mandates tbody tr" elements
    And I should see 2 ".committee-inactive-mandates tbody tr" elements
    When I am on "/admin/app/reporting-committeemergehistory/merge"
    And I fill in the following:
      | ID du comité source         | 3 |
      | ID du comité de destination | 1 |
    Then I press "Fusionner"
    Then the response status code should be 200
    And I should see "l'arrivée de 1 nouveau(x) membre(s) au sein du comité de destination En Marche Paris 8 (1)"
    Then I press "Confirmer la fusion"
    And the response status code should be 200
    And I should be on "/admin/app/reporting-committeemergehistory/list"
    Then I am on "/admin/app/committee/3/members"
    And I should not see "Animateur principal"
    And I should not see "Co-animateur"
    And I should not see "Transformer en"
    And I should see 0 ".label-primary:contains('Co-animateur')" elements
    When I am on "/admin/committee/3/mandates"
    And I should see "Pas de mandats" in the ".committee-active-mandates tbody tr" element
    Then I should see 1 ".committee-active-mandates tbody tr" elements
    And I should see 5 ".committee-inactive-mandates tbody tr" elements
    When I am on "/admin/committee/1/members"
    Then I should see 5 ".committee-members tbody tr" elements
    And I should see "francis.brioul@yahoo.com"
    And I should see 0 ".label-primary:contains('Co-animateur')" elements
    When I am on "/admin/committee/1/mandates"
    Then I should see 3 ".committee-active-mandates tbody tr" elements
    And I should see 2 ".committee-inactive-mandates tbody tr" elements

    Given I am on "/admin/app/reporting-committeemergehistory/list"
    And I follow "Annuler la fusion"
    Then the response status code should be 200
    And I press "Confirmer"
    Then the response status code should be 200
    And I should be on "/admin/committee/3/members"
    And I should see "La fusion de comités a bien été annulée."

    Then I am on "/admin/committee/1/members"
    And I should see 4 ".committee-members tbody tr" elements
    And I should not see "francis.brioul@yahoo.com"

  Scenario: All candidacies of merged committee should be transferred to the new committee
    Given I am logged as "adherent-male-49@en-marche-dev.fr"
    When I am on "/comites/en-marche-allemagne"
    Then I should see "Isabelle Responsable Communal doit accepter votre demande pour que votre candidature soit confirmée"

    Given I am logged as "superadmin@en-marche-dev.fr" admin
    And I am on "/admin/app/reporting-committeemergehistory/merge"
    When I fill in the following:
      | ID du comité source         | 12 |
      | ID du comité de destination | 10 |
    And I press "Fusionner"
    Then the response status code should be 200
    And I should see "l'arrivée de 4 nouveau(x) membre(s) au sein du comité de destination En Marche - Suisse (10)"
    Then I press "Confirmer la fusion"
    And the response status code should be 200
    And I should be on "/admin/app/reporting-committeemergehistory/list"

    Given I am logged as "adherent-male-49@en-marche-dev.fr"
    When I am on "/comites/en-marche-allemagne"
    Then the response status code should be 403

    When I am on "/comites/en-marche-suisse"
    Then the response status code should be 200
    And I should see "Isabelle Responsable Communal doit accepter votre demande pour que votre candidature soit confirmée"
