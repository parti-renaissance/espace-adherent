Feature:
  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData |
      | LoadEventData    |

  Scenario: The search city is based on user's city

    When I am on "/evenements"
    Then the "search-city" field should contain "Paris"

    When I am logged as "benjyd@aol.com"
    And I am on "/evenements"
    Then the "search-city" field should contain "Marseille 3e, France"

  Scenario: As a non logged-in user, I can not see the participants count of an event

    When I am on "/evenements"
    Then I should not see "1 inscrit"
    And I should not see "2 inscrits"

    When I am logged as "jacques.picard@en-marche.fr"
    Then I am on "/evenements"
    Then I should see "1 inscrit"
    And I should see "2 inscrits"
