@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A CitizenActionCancellationMessage template is rendered
    When the "CitizenActionCancellationMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name          |
      | citizen_action_name |
