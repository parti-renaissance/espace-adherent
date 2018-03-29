@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A CitizenProjectApprovalConfirmationMessage template is rendered
    When the "CitizenProjectApprovalConfirmationMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name           |
      | citizen_project_name |
