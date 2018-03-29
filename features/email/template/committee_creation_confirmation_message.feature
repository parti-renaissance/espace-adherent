@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A CommitteeCreationConfirmationMessage template is rendered
    When the "CommitteeCreationConfirmationMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name     |
      | committee_city |
