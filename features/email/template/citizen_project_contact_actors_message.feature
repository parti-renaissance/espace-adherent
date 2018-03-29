@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A CitizenProjectContactActorsMessage template is rendered
    When the "CitizenProjectContactActorsMessage" email template is rendered
    Then the email template should contain the following variables:
      | host_first_name |
      | subject         |
      | message         |
