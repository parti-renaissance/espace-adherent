@emailTemplate
Feature:
  In order to
  An email should be sent

  Scenario: A CitizenActionNotificationMessage template is rendered
    When the "CitizenActionNotificationMessage" email template is rendered
    Then the email template should contain the following variables:
      | recipient_first_name      |
      | host_first_name           |
      | citizen_project_name      |
      | citizen_action_name       |
      | citizen_action_date       |
      | citizen_action_hour       |
      | citizen_action_address    |
      | citizen_action_attend_url |
