@admin
Feature: Manage adherent tags from admin panel

  Background:
    Given the following fixtures are loaded:
      | LoadAdminData       |
      | LoadAdherentTagData |
    When I am logged as "superadmin@en-marche-dev.fr" admin

  Scenario: As a super admin, I should have a list of tags
    When I am on "/admin/app/adherenttag/list"
    Then the response status code should be 200
    And I should see 7 "tbody tr" elements
    And I should see 3 "thead tr th" elements
    And I should see 0 "ul.navbar-right li.sonata-actions ul.dropdown-menu li" elements
