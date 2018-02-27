@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A BoardMemberMessage template is rendered
    When the "BoardMemberMessage" email template is rendered
    Then the email template should contain the following variables:
      | member_first_name |
      | member_last_name  |
      | subject           |
      | message           |
