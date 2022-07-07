@javascript
@javascript2
Feature:
  As deputy
  I can send messages to the adherents, see committees and events of my district

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData      |
      | LoadDistrictData      |
      | LoadEventCategoryData |
      | LoadCommitteeEventData |

  Scenario: As deputy of 1st Paris district I can see committees.
    Given I am logged as "deputy@en-marche-dev.fr"
    When I am on "/espace-depute/comites"
    And I wait 3 second until I see "En Marche Paris 8"
    Then I should see 1 "table.managed__list__table tbody tr" elements
