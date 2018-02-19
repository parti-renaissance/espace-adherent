@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A CommitteeMessageNotificationMessage template is rendered
    When the "CommitteeMessageNotificationMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name      |
      | host_first_name |
      | subject         |
      | message         |
