@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A CommitteeApprovalConfirmationMessage template is rendered
    When the "CommitteeApprovalConfirmationMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name     |
      | committee_city |
      | committee_url  |
