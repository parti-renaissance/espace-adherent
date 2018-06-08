@emailTemplate
Feature:
  In order to
  An email should be sent

  Scenario: A PayboxPaymentUnsubscriptionConfirmationMessage template is rendered
    When the "PayboxPaymentUnsubscriptionConfirmationMessage" email template is rendered
    Then the email template should contain the following variables:
      | recipient_first_name      |
