@admin
Feature: Merge committees from admin panel

  Background:
    Given the following fixtures are loaded:
      | LoadAdminData    |
      | LoadAdherentData |
    When I am logged as "superadmin@en-marche-dev.fr" admin

  Scenario: A committee can not be merged if it is not approved
    Given I am on "/admin/app/reporting-committeemergehistory/merge"
    When I fill in the following:
      | ID du comité source         | 2 |
      | ID du comité de destination | 1 |
    Then I clean the "api_sync" queue
    And I press "Fusionner"
    Then the response status code should be 200
    And I should see "Le comité \"En Marche Marseille 3\" (2) doit être approuvé pour être fusionné."
    And I should not see "Confirmer la fusion"
    And "api_sync" should have 0 message

  Scenario: A committee can not be merged if the destination committee is not approved
    Given I am on "/admin/app/reporting-committeemergehistory/merge"
    When I fill in the following:
      | ID du comité source         | 1 |
      | ID du comité de destination | 2 |
    Then I clean the "api_sync" queue
    And I press "Fusionner"
    Then the response status code should be 200
    And I should see "Le comité \"En Marche Marseille 3\" (2) doit être approuvé pour être fusionné."
    And I should not see "Confirmer la fusion"
    And "api_sync" should have 0 message

  Scenario: A committee can not be merged into itself
    Given I am on "/admin/app/reporting-committeemergehistory/merge"
    When I fill in the following:
      | ID du comité source         | 1 |
      | ID du comité de destination | 1 |
    Then I clean the "api_sync" queue
    And I press "Fusionner"
    Then the response status code should be 200
    And I should see "Veuillez spécifier des comités différents."
    And I should not see "Confirmer la fusion"
    And "api_sync" should have 0 message

    @debug
  Scenario: A committee merge and revert must trigger events in RabbitMQ
    Given I am on "/admin/committee/1/members"
    Then I should see 4 ".committee-members tbody tr" elements
    And I should not see "francis.brioul@yahoo.com"
    Given I am on "/admin/app/reporting-committeemergehistory/merge"
    When I fill in the following:
      | ID du comité source         | 3 |
      | ID du comité de destination | 1 |
    Then I clean the "api_sync" queue
    And I press "Fusionner"
    Then the response status code should be 200
    And I should see "l'arrivée de 1 nouveau(x) membre(s) au sein du comité de destination En Marche Paris 8 (1)"
    And "api_sync" should have 0 message
    Then I press "Confirmer la fusion"
    And the response status code should be 200
    And I should be on "/admin/app/reporting-committeemergehistory/list"
    And "api_sync" should have 2 messages
    And "api_sync" should have messages below:
      | routing_key       | body                                                                                                                                                                                                                                                                                                                 |
      | committee.updated | {"uuid":"b0cd0e52-a5a4-410b-bba3-37afdd326a0a","status":"REFUSED","membersCount":2,"name":"En Marche Dammarie-les-Lys","slug":"en-marche-dammarie-les-lys","tags":["77"],"longitude":2.624205,"latitude":48.5182194,"country":"FR","address":"824 Avenue du Lys","zipCode":"77190","city":"Dammarie-les-Lys"}     |
      | committee.updated | {"uuid":"515a56c0-bde8-56ef-b90c-4745b1c93818","status":"APPROVED","membersCount":5,"name":"En Marche Paris 8","slug":"en-marche-paris-8","tags":["75008","75"],"longitude":2.313243,"latitude":48.870506,"country":"FR","address":"60 avenue des Champs-\u00c9lys\u00e9es","zipCode":"75008","city":"Paris 8e"} |
    Then I am on "/admin/app/committee/3/members"
    And I should not see "Animateur principal"
    And I should not see "Co-animateur"
    Then I am on "/admin/committee/1/members"
    And I should see 5 ".committee-members tbody tr" elements
    And I should see "francis.brioul@yahoo.com"

    Given I clean the queues
    And I am on "/admin/app/reporting-committeemergehistory/list"
    And I follow "Annuler la fusion"
    Then the response status code should be 200
    And I press "Confirmer"
    Then print last response
    Then the response status code should be 200
    And I should be on "/admin/committee/3/members"
    And I should see "La fusion de comités a bien été annulée."
    And "api_sync" should have 2 messages

    Then I am on "/admin/committee/1/members"
    And I should see 4 ".committee-members tbody tr" elements
    And I should not see "francis.brioul@yahoo.com"

