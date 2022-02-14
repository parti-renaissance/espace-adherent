@javascript
@javascript3
Feature:
  As a non logged user
  I can fill a form and became a representative

  Background:
    Given the following fixtures are loaded:
      | LoadProcurationData |

  Scenario: As a non logged user, I can fill a form
    Given I am on "/choisir/proposition"
    When I click the ".form__label" selector
    And I press "Continuer"
    Then I should be on "/je-propose"
    And I should see 3 "#app_procuration_proposal_proxiesCount div" element
    When I fill in the following:
      | app_procuration_proposal[voteCountry] | FR |
    Then I should see 1 "#app_procuration_proposal_proxiesCount div.hidden" element
    And I should see "Attention : vous ne pouvez pas avoir plus de 2 procurations, et 1 seule d'entre elles peut être faite en France. Vous pouvez donc avoir : Soit 1 procuration établie en France Soit 1 procuration établie à l'étranger Soit 1 procuration établie en France et 1 procuration établie à l'étranger Soit 2 procurations établies à l'étranger."

    When I fill in the following:
      | app_procuration_proposal[voteCountry] | ES |
    Then I should see 0 "#app_procuration_proposal_proxiesCount div.hidden" element
    And I should see "Attention : vous ne pouvez pas avoir plus de 3 procurations, et 1 seule d'entre elles peut être faite en France. Vous pouvez donc avoir : Soit 1 ou 2 ou 3 procurations établies à l'étranger Soit 1 procuration établie en France Soit 1 procuration établie en France et 1 procuration établie à l'étranger Soit 1 procuration établie en France et 2 procurations établies à l'étranger."

    When I fill in the following:
      | app_procuration_proposal[country] | FR |
    Then I should see 1 "#app_procuration_proposal_state.hidden" element
    And I should see 0 "#app_procuration_proposal_postalCode.hidden" element

    When I fill in the following:
      | app_procuration_proposal[country] | ES |
    Then I should see 0 "#app_procuration_proposal_state.hidden" element
    And I should see 1 "#app_procuration_proposal_postalCode.hidden" element
