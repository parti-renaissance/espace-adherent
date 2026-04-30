@api
Feature:
    In order to invite an unsubscribed militant to resubscribe to emails
    As a manager with the contacts feature
    I should be able to trigger a resubscribe email only for an adherent in my managed zone

    Scenario: A correspondent cannot send a resubscribe email to an adherent outside their zone
        Given I am logged with "je-mengage-user-1@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/adherents/a9fc8d48-6f57-4d89-ae73-50b3f9b586f4/send-resubscribe-email?scope=correspondent"
        Then the response status code should be 403

    Scenario: An Agora President cannot send a resubscribe email to an adherent who is not member of their agora
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        # michelle.dufour is President of agora-1; adherent a9fc8d48 is NOT a member and not in Michelle's geographic zone (CH).
        When I send a "POST" request to "/api/v3/adherents/a9fc8d48-6f57-4d89-ae73-50b3f9b586f4/send-resubscribe-email?scope=agora_president"
        Then the response status code should be 403

    Scenario: An Agora President can reach send-resubscribe-email for a member of their agora
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        # adherent e6977a4d (carl999@example.fr) is member of agora-1; the security check passes through the agora membership path.
        # adherent-2 is currently subscribed to emails -> the controller returns 400 "déjà abonné", which proves we passed the
        # security boundary (otherwise we would receive 403).
        When I send a "POST" request to "/api/v3/adherents/e6977a4d-2646-5f6c-9c82-88e58dca8458/send-resubscribe-email?scope=agora_president"
        Then the response status code should be 400
