@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A CitizenProjectRequestCommitteeSupportMessage template is rendered
    When the "CitizenProjectRequestCommitteeSupportMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name           |
      | citizen_project_name |
      | creator_first_name   |
      | creator_last_name    |
      | validation_url       |
