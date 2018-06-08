@emailTemplate
Feature:
  In order to
  An email should be sent

  Scenario: A CommitteeContactMembersMessage template is rendered
    When the "CommitteeContactMembersMessage" email template is rendered
    Then the email template should contain the following variables:
      | recipient_first_name |
      | sender_first_name    |
      | subject              |
      | message              |
