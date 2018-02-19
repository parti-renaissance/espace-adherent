@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A PurchasingPowerMessage template is rendered
    When the "PurchasingPowerMessage" email template is rendered
    Then the email template should contain the following variables:
      | message |
