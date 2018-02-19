@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A DonationMessage template is rendered
    When the "DonationMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name |
      | year       |
