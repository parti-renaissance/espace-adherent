@emailTemplate
Feature:
  In order to
  An email should be sent

  Scenario: A CitizenProjectCreationCoordinatorNotificationMessage template is rendered
    When the "CitizenProjectCreationCoordinatorNotificationMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name                      |
      | citizen_project_host_first_name |
      | citizen_project_host_last_name  |
      | citizen_project_name            |
      | citizen_project_url             |
