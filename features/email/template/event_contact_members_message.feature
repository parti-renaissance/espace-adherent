@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A EventContactMembersMessage template is rendered
    When the "EventContactMembersMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name           |
      | organizer_first_name |
      | subject              |
      | message              |
