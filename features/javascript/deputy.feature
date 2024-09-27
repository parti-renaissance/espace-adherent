@javascript
@javascript2
Feature:
    As deputy
    I can send messages to the adherents, see committees and events of my district

    Background:
        Given the following fixtures are loaded:
            | LoadAdherentData      |
            | LoadEventCategoryData |
            | LoadCommitteeV1Data   |

    Scenario: As deputy of 1st Paris district I can see committees.
        Given I am logged as "deputy@en-marche-dev.fr"
        And I am on "/espace-depute/comites"
        When I wait 3 second until I see "Charte de bonne utilisation des outils numériques"
        And I press "J'ai lu et j'accepte"
        And I wait 3 second until I see "En Marche Paris 8"
        Then I should see 1 "table.managed__list__table tbody tr" elements
