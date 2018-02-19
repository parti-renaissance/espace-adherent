@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A EventInvitationMessage template is rendered
    When the "EventInvitationMessage" email template is rendered
    Then the email template should contain the following variables:
      | sender_first_name |
      | sender_full_name  |
      | sender_message    |
      | event_name        |
      | event_slug        |
