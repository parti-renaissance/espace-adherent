Feature:
  In order to use the website
  I should be able to navigate through the nav

  Scenario: Simple user have simple nav
    Given I am logged as "simple-user@example.ch"
    And I am on "/"
    Then the ".nav-dropdown" element should not contain "Profil"
    And the ".nav-dropdown" element should not contain "Mes activités"

    When I am logged as "carl999@example.fr"
    And I am on "/"
    Then the ".nav-dropdown" element should contain "Profil"
    And the ".nav-dropdown" element should contain "Mes activités"
