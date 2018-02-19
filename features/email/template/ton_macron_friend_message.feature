@emailTemplate
Feature:
  In order to 
  An email should be sent 

  Scenario: A TonMacronFriendMessage template is rendered
    When the "TonMacronFriendMessage" email template is rendered
    Then the email template should contain the following variables:
      | message |
