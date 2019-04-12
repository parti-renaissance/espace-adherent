@javascript
Feature:
  As a non logged user
  I can fill a form and became a representative

  Background:
    Given the following fixtures are loaded:
      | LoadProcurationData |

  Scenario: As a non logged user, I can fill a form
    Given I am on "/procuration/choisir/proposition"
    When I click the ".form__label" selector
    And I press "Continuer"
    Then I should be on "procuration/je-propose"
    And I should see 3 "#app_procuration_proposal_proxiesCount div" element
    When I fill in the following:
      | app_procuration_proposal[voteCountry] | FR |
    Then I should see 1 "#app_procuration_proposal_proxiesCount div.hidden" element
    And I should see "Attention, vous ne pouvez être mandataire que pour deux procurations dont une seule maximum établie en France."

    When I fill in the following:
      | app_procuration_proposal[voteCountry] | ES |
    Then I should see 0 "#app_procuration_proposal_proxiesCount div.hidden" element
    And I should see "Attention, vous ne pouvez être mandataire que pour trois procurations dont une seule maximum établie en France."

    When I fill in the following:
      | app_procuration_proposal[country] | FR |
    Then I should see 1 "#app_procuration_proposal_state.hidden" element
    And I should see 0 "#app_procuration_proposal_postalCode.hidden" element

    When I fill in the following:
      | app_procuration_proposal[country] | ES |
    Then I should see 0 "#app_procuration_proposal_state.hidden" element
    And I should see 1 "#app_procuration_proposal_postalCode.hidden" element
