@emailTemplate
Feature:
  In order to
  An email should be sent

  Scenario: A ProcurationProxyReminderMessage template is rendered
    When the "ProcurationProxyReminderMessage" email template is rendered
    Then the email template should contain the following variables:
      | info_url           |
      | voter_first_name   |
      | voter_phone        |
      | mandant_first_name |
      | mandant_phone      |
