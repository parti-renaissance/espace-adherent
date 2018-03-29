@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A CommitteeApprovalReferentMessage template is rendered
    When the "CommitteeApprovalReferentMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name           |
      | committee_name       |
      | committee_city       |
      | creator_first_name   |
      | creator_contact_link |
