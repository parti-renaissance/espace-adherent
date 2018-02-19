@emailTemplate
Feature:
  In order to confirm my membership has been terminated
  An email should be sent to confirm these changes

  Scenario: An AdherentTerminateMembershipMessage template is rendered
    When the "AdherentTerminateMembershipMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name |
