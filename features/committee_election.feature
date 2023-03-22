@app
@renaissance
Feature:
    As an adherent I should be able to see the current election in my committee

    Scenario: As a voter in the committee, I can see its candidacies lists
        Given I am logged as "adherent-male-55@en-marche-dev.fr"
        When I am on "/comites/5e00c264-1d4b-43b8-862e-29edc38389b3/listes-candidats"
        Then I should see "Election AL - comité des 3 communes"
        And I should see "Liste 1 (3 membres)"
        And I should see "Liste 2 (3 membres)"
        And I should see "Liste 3 (3 membres)"
        And I should see "Liste 4 (1 membre)"
