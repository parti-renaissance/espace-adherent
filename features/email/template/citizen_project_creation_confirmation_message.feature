@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A CitizenProjectCreationConfirmationMessage template is rendered
    When the "CitizenProjectCreationConfirmationMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name           |
      | citizen_project_name |
      | create_action_link   |
