@emailTemplate
Feature:
  In order to
  An email should be sent

  Scenario: A CitizenProjectRequestCommitteeSupportMessage template is rendered
    When the "CitizenProjectRequestCommitteeSupportMessage" email template is rendered
    Then the email template should contain the following variables:
      | recipient_first_name                  |
      | citizen_project_name                  |
      | citizen_project_host_first_name       |
      | citizen_project_host_last_name        |
      | citizen_project_committee_support_url |
