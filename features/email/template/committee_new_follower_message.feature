@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A CommitteeNewFollowerMessage template is rendered
    When the "CommitteeNewFollowerMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name          |
      | committee_name      |
      | committee_admin_url |
      | member_first_name   |
      | member_last_name    |
      | member_age          |
