@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A CitizenProjectCommentMessage template is rendered
    When the "CitizenProjectCommentMessage" email template is rendered
    Then the email template should contain the following variables:
      | citizen_project_host_first_name |
      | citizen_project_host_message    |
