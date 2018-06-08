@emailTemplate
Feature:
  In order to
  An email should be sent

  Scenario: A CommitteeNewFollowerMessage template is rendered
    When the "CommitteeNewFollowerMessage" email template is rendered
    Then the email template should contain the following variables:
      | recipient_first_name |
      | committee_name       |
      | member_first_name    |
      | member_last_name     |
      | member_age           |
      | member_city          |
