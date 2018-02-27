@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A InvitationMessage template is rendered
    When the "InvitationMessage" email template is rendered
    Then the email template should contain the following variables:
      | sender_firstname |
      | sender_lastname  |
      | message          |
