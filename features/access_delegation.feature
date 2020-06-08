Feature:
    As an adherent
    I want to delegate some access to another adherent
    As the other adherent
    I want to access to a delegated space

    Background:
        Given the following fixtures are loaded:
            | LoadAdherentData        |
            | LoadDelegatedAccessData |
            | LoadDistrictData        |
        And I am logged as "deputy-ch-li@en-marche-dev.fr"

    @javascript
    Scenario: I can delegate a space to an adherent of my area by searching for its name
        When I go to "/espace-depute/messagerie"
        And I press "J'ai lu et j'accepte"
        Then I should see "Mon équipe"

        When I go to "/espace-depute/mon-equipe"
        Then I should see 3 ".team-organization__member-container" element
        And I should not see "Michelle Dufour"

        When I follow "Déléguer un accès à mon espace"
        Then I should be on "/espace-depute/mon-equipe/deleguer-acces"
        And I should see "Rechercher un adhérent de mon territoire"

        When I fill in "f_name" with "michelle"
        And I press "Rechercher"
        And I wait for "#js-select-adherent" element

        When I select "michelle.dufour@example.ch" from "js-select-adherent"
        Then I should see "Michelle Dufour" in the "#js-selected-adherent-name" element

        When I select "Collaborateur parlementaire" from "delegate_access_role"
        And I click the "#delegate_access_accesses label:contains('Mes messages')" selector
        And I click the "#delegate_access_accesses label:contains('Événements')" selector
        And I click the "#delegate_access_accesses label:contains('Adhérents')" selector
        And I click the "#delegate_access_accesses label:contains('Comités')" selector
        And I press "Valider"
        Then I should be on "/espace-depute/mon-equipe"
        And I should see 4 ".team-organization__member-container" element
        And I should see "Michelle Dufour" in the ".team-organization__member-container:nth-child(4)" element

    @javascript
    Scenario: I can delegate to an adherent not in my area by using its email address
        And I am on "/espace-depute/mon-equipe/deleguer-acces"
        And I press "J'ai lu et j'accepte"
        Then I should not see "Saisir une adresse e-mail"
        And I should see "L'adhérent est hors de mon territoire"

        When I click the "#js-search-by-email" selector
        Then I should see "Saisir une adresse e-mail"

        When I fill in "delegate_access_email" with "carl999@example.fr"
        And I select "Collaborateur parlementaire" from "delegate_access_role"
        And I click the "#delegate_access_accesses label:contains('Mes messages')" selector
        And I press "Valider"
        Then I should be on "/espace-depute/mon-equipe"
        And I should see 4 ".team-organization__member-container" element
        And I should see "Carl Mirabeau" in the ".team-organization__member-container:nth-child(4)" element
