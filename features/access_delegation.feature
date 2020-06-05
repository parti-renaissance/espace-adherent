Feature:
    As an adherent
    I want to delegate some access to another adherent
    As the other adherent
    I want to access to a delegated space

    @javascript
    Scenario: I can delegate a space to an adherent of my area by searching for its name
        Given I am logged as "deputy@en-marche-dev.fr"
        When I go to "/espace-depute"
        Then I should see "Mon équipe"

        When I follow "Mon équipe"
        Then I should be on "/espace-depute/mon-equipe"
        And I should see 0 ".team-member" element

        When I press "Déléguer un accès à mon équipe"
        Then I should be on "/espace-depute/mon-equipe/deleguer-acces"
        And I should see "Trouver un adhérent"

        When I fill in "Nom" with "michelle"
        And I press "Rechercher"
        And I wait for "#js-select-adherent" element

        When I select "Michelle Dufour" from "#js-select-adherent"
        Then I should see "name" in the "#js-selected-adherent-name" element

        When I fill in "Roles" with "Collaborateur parlementaire"
        And I check "messages"
        And I check "events"
        And I check "adherents"
        And I check "committee"
        And I press "Valider"
        Then I should be on "/espace-depute/mon-equipe"
        And I should see 1 ".team-member" element
        And I should see "name" in the ".team-member:nth-child(0)" element

    Scenario: I can delegate to an adherent not in my area by using its email address
        Given I am logged as "deputy@en-marche-dev.fr"
        And I am on "/espace-depute/mon-equipe/deleguer-acces"
        When I fill in "Roles" with "Collaborateur parlementaire"
        And I check "messages"
        And I fill in "Ou entrer une adresse mail d'adherent" with "carl999@example.fr"
        And I press "Valider"
        Then I should be on "/espace-depute/mon-equipe"
        And I should see 1 ".team-member" element
        And I should see "Carl Mirabeau" in the ".team-member:nth-child(0)" element
