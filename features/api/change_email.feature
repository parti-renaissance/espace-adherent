@api
@renaissance
@debug
Feature:
    As a logged-in user
    I should be able to change my email address

    Scenario: As a logged-in user I can request an email address change
        Given I am logged with "carl999@example.fr" via OAuth client "JeMengage Mobile" with scopes "read:profile write:profile"
        When I send a "POST" request to "/api/v3/profile/email/request" with body:
        """
        {
            "email": "carl123@example.fr"
        }
        """
        Then the response status code should be 200
        And the response should be in JSON
        And print last JSON response
        And the JSON should be equal to:
        """
        {
            "message": "Un mail vous a été envoyé pour confirmer votre changement d'adresse email."
        }
        """
