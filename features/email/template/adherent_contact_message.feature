@emailTemplate
Feature:
  In order to contact another member
  An email containing my message should be sent to this member

  Scenario: An AdherentContactMessage template is rendered
    When the "AdherentContactMessage" email template is rendered
    Then the email template should contain the following variables:
      | recipient_first_name  |
      | sender_first_name     |
      | message               |
