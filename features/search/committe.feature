@front
Feature:

    Scenario: The search city is base on user's city
        When I am on "/comites"
        Then the "search-city" field should contain "Paris"

        When I am logged as "benjyd@aol.com"
        And I am on "/comites"
        Then the "search-city" field should contain "Marseille 3e, France"
