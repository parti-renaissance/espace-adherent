@emailTemplate
Feature:
  In order to reset my password
  An email should be sent with a link to change my password

  Scenario: An AdherentResetPasswordMessage template is rendered
    When the "AdherentResetPasswordMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name |
      | reset_link |
