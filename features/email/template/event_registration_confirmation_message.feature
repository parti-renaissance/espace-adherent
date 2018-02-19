@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A EventRegistrationConfirmationMessage template is rendered
    When the "EventRegistrationConfirmationMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name      |
      | event_name      |
      | event_organizer |
      | event_link      |
