@procurationRequest
@javascript
Feature:
  As a non logged user
  I can fill a form and submit a procuration request

  Background:
    Given the following fixtures are loaded:
      | LoadProcurationData |

  Scenario: As a non logged user, I can fill a form
    Given I am on "/procuration/choisir/demande"
    And I click the ".cc-btn" selector
    When I click the ".form__label" selector
    And I press "Continuer"
    Then I should be on "procuration/je-demande/mon-lieu-de-vote"

    Given I fill in the following:
      | app_procuration_request[voteCountry]    | FR    |
      | app_procuration_request[votePostalCode] | 59000 |
      | app_procuration_request[voteOffice]     | Lorem |
    When I press "Je continue"
    Then I should be on "procuration/je-demande/mes-coordonnees"

    When I fill in the following:
      | app_procuration_request[country] | FR |
    Then I should see 1 "#app_procuration_request_state.hidden" element
    And I should see 0 "#app_procuration_request_postalCode.hidden" element

    When I fill in the following:
      | app_procuration_request[country] | ES |
    Then I should see 0 "#app_procuration_request_state.hidden" element
    And I should see 1 "#app_procuration_request_postalCode.hidden" element
