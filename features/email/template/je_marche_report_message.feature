@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A JeMarcheReportMessage template is rendered
    When the "JeMarcheReportMessage" email template is rendered
    Then the email template should contain the following variables:
      | nombre_emails_convaincus    |
      | nombre_emails_indecis       |
      | emails_collected_convaincus |
      | emails_collected_indecis    |
