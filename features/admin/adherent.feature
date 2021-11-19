@app
Feature: Manage adherent from admin panel

  Background:
    When I am logged as "superadmin@en-marche-dev.fr" admin

  Scenario: Display list of adherents
    When I am on "/admin/app/adherent/list"
    Then the response status code should be 200
    And I should see 32 "tbody tr" elements
    And I should see 14 "thead tr th" elements

  Scenario: A user update must trigger an event in RabbitMQ
    Given I am on "/admin/app/adherent/list"
    And I follow "SCHMIDT"
    And I clean the "api_sync" queue
    When I press "Mettre à jour"
    Then the response status code should be 200
