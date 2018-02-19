@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A CitizenProjectNewFollowerMessage template is rendered
    When the "CitizenProjectNewFollowerMessage" email template is rendered
    Then the email template should contain the following variables:
      | citizen_project_name |
      | follower_first_name  |
      | follower_last_name   |
      | follower_age         |
      | follower_city        |
