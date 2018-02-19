@emailTemplate
Feature:
  In order to register on the website
  An email should be sent with the informations I need to activate my account

  Scenario: An AdherentAccountActivationMessage template is rendered
    When the "AdherentAccountActivationMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name        |
      | confirmation_link |
