@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A LegislativeCampaignContactMessage template is rendered
    When the "LegislativeCampaignContactMessage" email template is rendered
    Then the email template should contain the following variables:
      | email                     |
      | first_name                |
      | last_name                 |
      | department_number         |
      | electoral_district_number |
      | role                      |
      | subject                   |
      | message                   |
