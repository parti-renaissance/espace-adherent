@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A ProcurationProxyCancelledMessage template is rendered
    When the "ProcurationProxyCancelledMessage" email template is rendered
    Then the email template should contain the following variables:
      | target_first_name |
      | voter_first_name  |
      | voter_last_name   |
