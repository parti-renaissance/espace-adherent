@admin
Feature: Manage adherent from admin panel

  Background:
    Given the following fixtures are loaded:
      | LoadAdminData    |
      | LoadAdherentData |
    When I am logged as "superadmin@en-marche-dev.fr" admin

  Scenario: Display list of adherents
    When I am on "/admin/app/adherent/list"
    Then the response status code should be 200
    And I should see 32 "tbody tr" elements
    And I should see 15 "thead tr th" elements

  Scenario: A user update must trigger an event in RabbitMQ
    Given I am on "/admin/app/adherent/list"
    And I follow "SCHMIDT"
    And I clean the "api_sync" queue
    When I press "Mettre à jour"
    Then the response status code should be 200
    And "api_sync" should have 1 message
    And "api_sync" should have message below:
      | routing_key  | body                                                                                                                                                                                                                             |
      | user.updated | {"uuid":"511c21bf-1240-5271-abaa-3393d3f40740","subscriptionExternalIds":["123abc","456def"],"city":"Kilchberg","country":"CH","zipCode":"8802","tags":["CH"],"emailAddress":"damien.schmidt@example.ch","firstName":"Damien","lastName":"Schmidt"} |
