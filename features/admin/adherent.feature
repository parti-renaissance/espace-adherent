Feature: Manage adherent from admin panel

  Scenario: Display list of adherents
    Given the following fixtures are loaded:
      | LoadAdminData    |
      | LoadAdherentData |

    When I am logged as "superadmin@en-marche-dev.fr" admin
    And I am on "/admin/app/adherent/list"
    Then the response status code should be 200
