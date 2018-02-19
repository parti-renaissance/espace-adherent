@emailTemplate
Feature:
  In order to confirm my password has been changed
  An email should be sent to confirm these changes

  Scenario: An AdherentResetPasswordConfirmationMessage template is rendered
    When the "AdherentResetPasswordConfirmationMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name |
