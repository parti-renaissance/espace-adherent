@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A NewsletterSubscriptionMessage template is rendered
    When the "NewsletterSubscriptionMessage" email template is rendered
    Then the email template should not contain any variable
