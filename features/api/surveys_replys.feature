@api
@renaissance
Feature:

    Scenario: As a logged-in user I can reply to a national survey for phoning campaign (new body structure)
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "J'écoute" with scope "jecoute_surveys"
        When I send a "POST" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055/reply" with body:
            """
            {
                "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                "answers": [
                    {
                        "surveyQuestion": 6,
                        "textField": "Réponse libre d'un questionnaire national"
                    },
                    {
                        "surveyQuestion": 7,
                        "selectedChoices": [5, 6]
                    }
                ]
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            { "uuid": "@uuid@" }
            """
        When I send a "POST" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055/reply" with body:
            """
            {
                "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                "answers": [
                    {
                        "surveyQuestion": 6,
                        "textField": "Réponse libre d'un questionnaire national"
                    },
                    {
                        "surveyQuestion": 7,
                        "selectedChoices": [5, 6]
                    }
                ]
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "code": "already_replied",
                "message": "La réponse a été déjà envoyée"
            }
            """

    Scenario: As a logged-in user I can reply to a survey for permanent phoning campaign
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jecoute_surveys"
        When I send a "POST" request to "/api/v3/phoning_campaign_histories/a80248ff-384a-4f80-972a-177c3d0a77c4/reply" with body:
            """
            {
                "answers": [
                    {
                        "surveyQuestion": 6,
                        "textField": "Une nouvelle réponse libre à un questionnaire national"
                    },
                    {
                        "surveyQuestion": 7,
                        "selectedChoices": [6, 4]
                    }
                ]
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            { "uuid": "@uuid@" }
            """

    Scenario: As a logged-in user I can reply partially to a survey for permanent phoning campaign
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jecoute_surveys"
        When I send a "POST" request to "/api/v3/phoning_campaign_histories/a80248ff-384a-4f80-972a-177c3d0a77c4/reply" with body:
            """
            {
                "answers": [
                    {
                        "surveyQuestion": 6,
                        "textField": null
                    },
                    {
                        "surveyQuestion": 7,
                        "selectedChoices": []
                    }
                ]
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            { "uuid": "@uuid@" }
            """

    Scenario: As a logged-in user I can reply to a national survey for Jemarche data survey (new body structure)
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jecoute_surveys"
        When I send a "POST" request to "/api/v3/jemarche_data_surveys/5191f388-ccb0-4a93-b7f9-a15f107287fb/reply" with body:
            """
            {
                "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                "answers": [
                    {
                        "surveyQuestion": 6,
                        "textField": "Une nouvelle réponse libre à un questionnaire national"
                    },
                    {
                        "surveyQuestion": 7,
                        "selectedChoices": [6, 4]
                    }
                ]
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            { "uuid": "@uuid@" }
            """
        And I should have 0 email

    Scenario: As a logged-in user I cannot reply to a national survey with invalid data
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jecoute_surveys"
        When I send a "POST" request to "/api/v3/jemarche_data_surveys" with body:
            """
            {
                "last_name": "Bonsoirini Bonsoirini Bonsoirini Bonsoirini Bonsoirini",
                "first_name": "Ernestino Ernestino Ernestino Ernestino Ernestino Ernestino",
                "email_address": "ernestinobonsoirini",
                "postal_code": "59",
                "profession": "test",
                "age_range": "test",
                "gender": "test",
                "gender_other": "other other other other other other other other other other other other"
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "profession",
                        "message": "Cette valeur doit être l'un des choix proposés."
                    },
                    {
                        "propertyPath": "age_range",
                        "message": "Cette valeur doit être l'un des choix proposés."
                    },
                    {
                        "propertyPath": "gender",
                        "message": "Cette valeur doit être l'un des choix proposés."
                    }
                ]
            }
            """

    Scenario: As a logged-in user I can reply to a national survey
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jecoute_surveys"
        When I send a "POST" request to "/api/v3/jemarche_data_surveys" with body:
            """
            {
                "last_name": "Bonsoirini",
                "first_name": "Ernestino",
                "email_address": "ernestino@bonsoirini.fr",
                "agreed_to_stay_in_contact": true,
                "agreed_to_contact_for_join": true,
                "agreed_to_treat_personal_data": true,
                "postal_code": "59000",
                "profession": "employees",
                "age_range": "between_25_39",
                "gender": "male",
                "latitude": 48.856614,
                "longitude": 2.3522219
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            { "uuid": "@uuid@" }
            """

    Scenario: As a logged-in user I can reply to a national survey for pap campaign (new body structure)
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "POST" request to "/api/v3/pap_campaign_histories/6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da/reply" with body:
            """
            {
                "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                "answers": [
                    {
                        "surveyQuestion": 6,
                        "textField": "Réponse libre à un questionnaire national de la campagne de PAP"
                    },
                    {
                        "surveyQuestion": 7,
                        "selectedChoices": [11, 12]
                    }
                ]
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            { "uuid": "@uuid@" }
            """
        When I send a "POST" request to "/api/v3/pap_campaign_histories/6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da/reply" with body:
            """
            {
                "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                "answers": [
                    {
                        "surveyQuestion": 6,
                        "textField": "Une nouvelle réponse libre à un questionnaire national"
                    },
                    {
                        "surveyQuestion": 7,
                        "selectedChoices": [5, 6]
                    }
                ]
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "code": "already_replied",
                "message": "La réponse a été déjà envoyée"
            }
            """
