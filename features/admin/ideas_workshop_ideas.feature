@admin
Feature: Manage idea from admin panel

  Background:
    Given the following fixtures are loaded:
      | LoadAdminData |
      | LoadIdeaData  |
    When I am logged as "superadmin@en-marche-dev.fr" admin

  Scenario: As a super admin, I should have a list of ideas
    When I am on "/admin/app/ideasworkshop-idea/list"
    Then the response status code should be 200
    And I should see 8 "tbody tr" elements
    And I should see 10 "thead tr th" elements
    And I should see 6 "ul.navbar-right li.sonata-actions ul.dropdown-menu li" elements
