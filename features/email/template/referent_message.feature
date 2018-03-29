@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A ReferentMessage template is rendered
    When the "ReferentMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name          |
      | message             |
      | referent_first_name |
