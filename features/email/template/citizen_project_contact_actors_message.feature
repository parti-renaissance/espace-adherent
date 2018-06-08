@emailTemplate
Feature:
  In order to
  An email should be sent

  Scenario: A CitizenProjectContactActorsMessage template is rendered
    When the "CitizenProjectContactActorsMessage" email template is rendered
    Then the email template should contain the following variables:
      | subject                         |
      | recipient_first_name            |
      | citizen_project_host_message    |
      | citizen_project_host_first_name |
