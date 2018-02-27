@emailTemplate
Feature:
  In order to register on the website
  An email should be sent to confirm my registration

  Scenario: An AdherentAccountConfirmationMessage template is rendered
    When the "AdherentAccountConfirmationMessage" email template is rendered
    Then the email template should contain the following variables:
      | adherents_count  |
      | committees_count |
      | first_name       |
      | last_name        |
