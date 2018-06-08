@emailTemplate
Feature:
  In order to
  An email should be sent

  Scenario: A CitizenProjectCreationNotificationMessage template is rendered
    When the "CitizenProjectCreationNotificationMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name               |
      | citizen_project_list     |
      | all_citizen_projects_url |
