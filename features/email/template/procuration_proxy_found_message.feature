@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A ProcurationProxyFoundMessage template is rendered
    When the "ProcurationProxyFoundMessage" email template is rendered
    Then the email template should contain the following variables:
      | first_name         |
      | info_link          |
      | elections          |
      | voter_first_name   |
      | voter_last_name    |
      | voter_phone        |
      | mandant_first_name |
      | mandant_last_name  |
      | mandant_phone      |
