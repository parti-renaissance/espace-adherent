@api
Feature:
  In order to subscribe to legislative candidate newsletter
  I should be able to access legislative newsletter subscription API

  Scenario: As a non logged-in user I cannot subscribe to a candidate newsletter with wrong captcha
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/legislative_newsletter_subscriptions" with body:
    """
    {
      "email_address": "lucile@example.org",
      "first_name": "lucile",
      "postal_code": "75001",
      "from_zone": "75-1",
      "personal_data_collection": true,
      "recaptcha": "wrong_answer",
      "recaptcha_site_key": "fake_key"
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "recaptcha: Le captcha soumis est invalide.",
      "violations": [
        {
          "propertyPath": "recaptcha",
          "message": "Le captcha soumis est invalide."
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can subscribe to another candidate newsletter
    Given I should have 0 email
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/legislative_newsletter_subscriptions" with body:
    """
    {
      "email_address": "john@example.org",
      "first_name": "John",
      "postal_code": "75009",
      "from_zone": "75-9",
      "personal_data_collection": true,
      "recaptcha": "fake123",
      "recaptcha_site_key": "fake_key"
    }
    """
    Then the response status code should be 201
    And I should have 0 email

  Scenario: As a non logged-in I can subscribe to a candidate newsletter
    Given I should have 0 email
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/legislative_newsletter_subscriptions" with body:
    """
    {
      "email_address": "lucile@example.org",
      "first_name": "lucile",
      "postal_code": "75001",
      "from_zone": "75-1",
      "personal_data_collection": true,
      "recaptcha": "fake123",
      "recaptcha_site_key": "fake_key"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And I should have 1 email
    And I should have 1 email "LegislativeNewsletterSubscriptionConfirmationMessage" for "lucile@example.org" with payload:
    """
    {
        "template_name": "legislative-newsletter-subscription-confirmation",
        "template_content": [],
        "message": {
            "subject": "Confirmez votre adresse email",
            "from_email": "ne-pas-repondre@avecvous.fr",
            "from_name": "La majorité présidentielle avec vous",
            "global_merge_vars": [
                {
                    "name": "confirmation_link",
                    "content": "http://test.enmarche.code/newsletters/confirmation/@string@/@string@"
                }
            ],
            "to": [
                {
                    "email": "lucile@example.org",
                    "type": "to",
                    "name": "lucile"
                }
            ]
        }
    }
    """

  Scenario: I can confirm my newsletter subscription
    Given I stop following redirections
    When I am on "/newsletters/confirmation/9e0e1a9e-2c5d-4d3a-a6c8-4a582ff78e22/bd879a23-43bf-47d2-b67b-9c7cbb085547"
    Then the response status code should be 302
    And the message "MailchimpSyncLegislativeNewsletterCommand" should be dispatched
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/legislative_newsletter_subscriptions" with body:
    """
    {
      "email_address": "jane@example.org",
      "first_name": "Jane",
      "postal_code": "75001",
      "from_zone": "75-1",
      "personal_data_collection": true,
      "recaptcha": "fake123",
      "recaptcha_site_key": "fake_key"
    }
    """
    Then the response status code should be 201
    And the message "MailchimpSyncLegislativeNewsletterCommand" should be dispatched
