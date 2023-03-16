@app
@renaissance
Feature:
  As an adherent I should be able to see the current election in my committee

  Scenario: As member of the committee, I can see its candidacies lists
    Given I am logged as "gisele-berthoux@caramail.com"
    When I am on "/comites/8c4b48ec-9290-47ae-a5db-d1cf2723e8b3/listes-candidats"
    Then I should see "Election AL - second comité des 3 communes"
    And I should see "Liste 1 (1 membre)"
