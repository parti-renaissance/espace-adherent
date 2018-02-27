@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A CitizenActionRegistrationConfirmationMessage template is rendered
    When the "CitizenActionRegistrationConfirmationMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name                  |
      | citizen_action_name         |
      | citizen_action_organiser    |
      | citizen_action_calendar_url |
