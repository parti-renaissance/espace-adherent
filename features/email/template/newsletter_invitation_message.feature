@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A NewsletterInvitationMessage template is rendered
    When the "NewsletterInvitationMessage" email template is rendered
    Then the email template should contain the following variables:
      | sender_first_name |
      | subscribe_link    |
