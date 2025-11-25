@api
@renaissance
Feature:
    In order to get all surveys
    As a non logged-in user
    I should be able to access to the surveys configuration and be able to answer to it

    Scenario: As a non logged-in user I cannot get the surveys
        When I send a "GET" request to "/api/jecoute/survey"
        Then the response status code should be 401

    Scenario: As a simple logged-in user I can get the surveys
        Given I am logged with "simple-user@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "GET" request to "/api/jecoute/survey"
        Then the response status code should be 200

    Scenario: As a logged-in user I can get the surveys of my referent(s) and the national surveys
        Given I am logged with "francis.brioul@yahoo.com" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "GET" request to "/api/jecoute/survey"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
              {
                "id": @integer@,
                "uuid": "@uuid@",
                "type": "local",
                "created_at": "@string@.isDateTime()",
                "name": "Questionnaire numéro 1",
                "zone":{
                   "code": "77",
                   "name": "Seine-et-Marne",
                   "created_at": "@string@.isDateTime()"
                },
                "questions": [
                  {
                    "id": @integer@,
                    "type": "simple_field",
                    "content": "Ceci est-il un champ libre ?",
                    "choices": []
                  },
                  {
                    "id": @integer@,
                    "type": "multiple_choice",
                    "content": "Est-ce une question à choix multiple ?",
                    "choices": [
                      {
                        "id": @integer@,
                        "content": "Réponse A"
                      },
                      {
                        "id": @integer@,
                        "content": "Réponse B"
                      }
                    ]
                  },
                  {
                    "id": @integer@,
                    "type": "unique_choice",
                    "content": "Est-ce une question à choix unique ?",
                    "choices": [
                      {
                        "id": @integer@,
                        "content": "Réponse unique 1"
                      },
                      {
                        "id": @integer@,
                        "content": "Réponse unique 2"
                      }
                    ]
                  },
                  {
                    "id": @integer@,
                    "type": "simple_field",
                    "content": "Ceci est-il un champ libre d'une question suggérée ?",
                    "choices": []
                  }
                ]
              },
              {
                "id": @integer@,
                "uuid": "@uuid@",
                "type": "national",
                "created_at": "@string@.isDateTime()",
                "name": "Les enjeux des 10 prochaines années",
                "questions": [
                  {
                    "id": @integer@,
                    "type": "simple_field",
                    "content": "A votre avis quels seront les enjeux des 10 prochaines années?",
                    "choices": []
                  },
                  {
                    "id": @integer@,
                    "type": "multiple_choice",
                    "content": "L'écologie est selon vous, importante pour :",
                    "choices": [
                      {
                        "id": @integer@,
                        "content": "L'héritage laissé aux générations futures"
                      },
                      {
                        "id": @integer@,
                        "content": "Le bien-être sanitaire"
                      },
                      {
                        "id": @integer@,
                        "content": "L'aspect financier"
                      },
                      {
                        "id": @integer@,
                        "content": "La préservation de l'environnement"
                      }
                    ]
                  }
                ]
              },
              {
                "id": @integer@,
                "uuid":"@uuid@",
                "type":"national",
                "created_at": "@string@.isDateTime()",
                "name": "Le deuxième questionnaire national",
                "questions":[
                  {
                    "id": @integer@,
                    "type": "unique_choice",
                    "content": "La question du 2eme questionnaire national ?",
                    "choices": [
                      {
                        "id": @integer@,
                        "content": "Réponse nationale E"
                      },
                      {
                        "id": @integer@,
                        "content": "Réponse nationale F"
                      },
                      {
                        "id": @integer@,
                        "content": "Réponse nationale G"
                      }
                    ]
                  }
                ]
              },
              {
                "id": @integer@,
                "uuid": "@uuid@",
                "type": "national",
                "created_at": "@string@.isDateTime()",
                "name": "Questionnaire national numéro 1",
                "questions": [
                  {
                    "id": @integer@,
                    "type": "simple_field",
                    "content": "Une première question du 1er questionnaire national ?",
                    "choices": []
                  },
                  {
                    "id": @integer@,
                    "type": "multiple_choice",
                    "content": "Une deuxième question du 1er questionnaire national ?",
                    "choices": [
                      {
                        "id": @integer@,
                        "content": "Réponse nationale A"
                      },
                      {
                        "id": @integer@,
                        "content": "Réponse nationale B"
                      },
                      {
                        "id": @integer@,
                        "content": "Réponse nationale C"
                      },
                      {
                        "id": @integer@,
                        "content": "Réponse nationale D"
                      }
                    ]
                  }
                ]
              }
            ]
            """

    Scenario: As a logged-in user I can reply to a national survey (new body structure)
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "POST" request to "/api/jecoute/survey/reply" with body:
            """
            {
                "dataSurvey": {
                    "survey": 1,
                    "answers": [
                        {
                            "surveyQuestion": 6,
                            "textField": "Réponse libre d'un questionnaire national"
                        },
                        {
                            "surveyQuestion": 7,
                            "selectedChoices": ["5", "6"]
                        }
                    ]
                },
                "lastName": "Bonsoirini",
                "firstName": "Ernestino",
                "emailAddress": "ernestino@bonsoirini.fr",
                "agreedToStayInContact": true,
                "agreedToContactForJoin": true,
                "agreedToTreatPersonalData": true,
                "postalCode": "59000",
                "profession": "employees",
                "ageRange": "between_25_39",
                "gender": "male",
                "latitude": 48.856614,
                "longitude": 2.3522219
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "status": "ok"
            }
            """
        And I should have 1 email "DataSurveyAnsweredMessage" for "ernestino@bonsoirini.fr" with payload:
            """
            {
                "template_name": "data-survey-answered",
                "template_content": [],
                "message": {
                    "subject": "Votre adhésion à La République En Marche !",
                    "from_email": "contact@parti-renaissance.fr",
                    "html": null,
                    "global_merge_vars": [
                        {
                            "name": "first_name",
                            "content": "Ernestino"
                        }
                    ],
                    "from_name": "Renaissance",
                    "to": [
                        {
                            "email": "ernestino@bonsoirini.fr",
                            "type": "to"
                        }
                    ]
                }
            }
            """

    Scenario Outline: As a logged-in user I can reply to a national survey
        Given I am logged with "<email>" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "POST" request to "/api/jecoute/survey/reply" with body:
            """
            {
                "survey": 1,
                "type": "national",
                "lastName": "Bonsoirini",
                "firstName": "Ernestino",
                "emailAddress": "ernestino@bonsoirini.fr",
                "agreedToStayInContact": true,
                "agreedToContactForJoin": true,
                "agreedToTreatPersonalData": true,
                "postalCode": "59000",
                "profession": "employees",
                "ageRange": "between_25_39",
                "gender": "male",
                "latitude": 48.856614,
                "longitude": 2.3522219,
                "answers": [
                    {
                        "surveyQuestion": 6,
                        "textField": "Réponse libre d'un questionnaire national"
                    },
                    {
                        "surveyQuestion": 7,
                        "selectedChoices": ["5", "6"]
                    }
                ]
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "status": "ok"
            }
            """
        And I should have 1 email "DataSurveyAnsweredMessage" for "ernestino@bonsoirini.fr" with payload:
            """
            {
                "template_name": "data-survey-answered",
                "template_content": [],
                "message": {
                    "subject": "Votre adhésion à La République En Marche !",
                    "from_email": "contact@parti-renaissance.fr",
                    "html": null,
                    "global_merge_vars": [
                        {
                            "name": "first_name",
                            "content": "Ernestino"
                        }
                    ],
                    "from_name": "Renaissance",
                    "to": [
                        {
                            "email": "ernestino@bonsoirini.fr",
                            "type": "to"
                        }
                    ]
                }
            }
            """

        Examples:
            | email                      |
            | michelle.dufour@example.ch |
            | simple-user@example.ch     |

    Scenario: As a logged-in user I can reply to a national survey without agreeing to join
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "POST" request to "/api/jecoute/survey/reply" with body:
            """
            {
                "survey": 1,
                "type": "national",
                "lastName": "Bonsoirini",
                "firstName": "Ernestino",
                "emailAddress": "ernestino2@bonsoirini.fr",
                "agreedToStayInContact": true,
                "agreedToContactForJoin": false,
                "agreedToTreatPersonalData": true,
                "postalCode": "59000",
                "profession": "employees",
                "ageRange": "between_25_39",
                "gender": "male",
                "latitude": 48.856614,
                "longitude": 2.3522219,
                "answers": [
                    {
                        "surveyQuestion": 6,
                        "textField": "Réponse libre d'un questionnaire national"
                    },
                    {
                        "surveyQuestion": 7,
                        "selectedChoices": ["5", "6"]
                    }
                ]
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "status": "ok"
            }
            """
        And I should have 0 email

    Scenario: As a logged-in user I can reply to a local survey
        Given I am logged with "francis.brioul@yahoo.com" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "POST" request to "/api/jecoute/survey/reply" with body:
            """
            {
                "survey": 3,
                "type": "local",
                "lastName": "Bonsoirini",
                "firstName": "Ernestino",
                "emailAddress": "ernestino@bonsoirini.fr",
                "agreedToStayInContact": true,
                "agreedToContactForJoin": true,
                "agreedToTreatPersonalData": true,
                "postalCode": "59000",
                "profession": "employees",
                "ageRange": "between_25_39",
                "gender": "male",
                "latitude": 48.856614,
                "longitude": 2.3522219,
                "answers": [
                    {
                        "surveyQuestion": 1,
                        "textField": "Réponse libre"
                    },
                    {
                        "surveyQuestion": 2,
                        "selectedChoices": ["1", "2"]
                    },
                    {
                        "surveyQuestion": 3,
                        "selectedChoices": ["1"]
                    }
                ]
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "status": "ok"
            }
            """
        And I should have 1 email "DataSurveyAnsweredMessage" for "ernestino@bonsoirini.fr" with payload:
            """
            {
                "template_name": "data-survey-answered",
                "template_content": [],
                "message": {
                    "subject": "Votre adhésion à La République En Marche !",
                    "from_email": "contact@parti-renaissance.fr",
                    "html": null,
                    "global_merge_vars": [
                        {
                            "name": "first_name",
                            "content": "Ernestino"
                        }
                    ],
                    "from_name": "Renaissance",
                    "to": [
                        {
                            "email": "ernestino@bonsoirini.fr",
                            "type": "to"
                        }
                    ]
                }
            }
            """

    Scenario: As a logged-in user I cannot reply to a local survey with errors
        Given I am logged with "francis.brioul@yahoo.com" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "POST" request to "/api/jecoute/survey/reply" with body:
            """
            {
                "survey": null,
                "type": "local",
                "lastName": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
                "firstName": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
                "emailAddress": "bonsoirini.fr",
                "agreedToStayInContact": true,
                "postalCode": "59",
                "profession": "bonsoir",
                "ageRange": "between_00_00",
                "gender": "invalid_gender",
                "genderOther": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
                "latitude": "bad_latitude",
                "longitude": "bad_longitude",
                "answers": [
                    {
                        "surveyQuestion": 1
                    },
                    {
                        "surveyQuestion": 2,
                        "selectedChoices": ["1", "2"]
                    },
                    {
                        "surveyQuestion": 3,
                        "textField": "Réponse non autorisée",
                        "selectedChoices": ["1", "2"]
                    }
                ]
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "status": "error",
                "errors": {
                    "dataSurvey": {
                        "survey": ["Il n'existe aucun sondage correspondant à cet ID."],
                        "answers": {
                            "2": {
                                "surveyQuestion": ["La question 3 est une question à choix unique et ne doit pas contenir de champ texte."]
                            }
                        }
                    },
                    "profession": ["Le choix sélectionné est invalide."],
                    "ageRange": ["Le choix sélectionné est invalide."],
                    "latitude": ["Veuillez saisir un nombre."],
                    "longitude": ["Veuillez saisir un nombre."],
                    "gender": ["Le choix sélectionné est invalide."]
                }
            }
            """

    Scenario: As a logged-in user I cannot reply to a local survey with errors (new body structure)
        Given I am logged with "francis.brioul@yahoo.com" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "POST" request to "/api/jecoute/survey/reply" with body:
            """
            {
                "dataSurvey": {
                    "survey": null,
                    "answers": [
                        {
                            "surveyQuestion": 1
                        },
                        {
                            "surveyQuestion": 2,
                            "selectedChoices": ["1", "2"]
                        },
                        {
                            "surveyQuestion": 3,
                            "textField": "Réponse non autorisée",
                            "selectedChoices": ["1", "2"]
                        }
                    ]
                },
                "lastName": "Bonsoirini",
                "firstName": "Ernestino",
                "emailAddress": "bonsoirini.fr",
                "agreedToStayInContact": true,
                "postalCode": "59",
                "profession": "bonsoir",
                "ageRange": "between_00_00",
                "latitude": "bad_latitude",
                "longitude": "bad_longitude"
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "status": "error",
                "errors": {
                    "dataSurvey": {
                        "survey": ["Il n'existe aucun sondage correspondant à cet ID."],
                        "answers": {
                            "2": {
                                "surveyQuestion": ["La question 3 est une question à choix unique et ne doit pas contenir de champ texte."]
                            }
                        }
                    },
                    "profession": ["Le choix sélectionné est invalide."],
                    "ageRange": ["Le choix sélectionné est invalide."],
                    "latitude": ["Veuillez saisir un nombre."],
                    "longitude": ["Veuillez saisir un nombre."]
                }
            }
            """

    Scenario: As a logged-in user I can reply to a local survey with custom validations errors
        Given I am logged with "francis.brioul@yahoo.com" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "POST" request to "/api/jecoute/survey/reply" with body:
            """
            {
                "survey": 3,
                "type": "local",
                "lastName": "Bonsoirini",
                "firstName": "Ernestino",
                "emailAddress": "ernestino@bonsoirini.fr",
                "agreedToStayInContact": false,
                "agreedToContactForJoin": true,
                "postalCode": "59000",
                "ageRange": "between_25_39",
                "gender": "other",
                "answers": [
                    {
                        "surveyQuestion": 1,
                        "textField": "Réponse libre"
                    },
                    {
                        "surveyQuestion": 2,
                        "selectedChoices": ["1", "2"]
                    },
                    {
                        "surveyQuestion": 3,
                        "selectedChoices": ["1"]
                    }
                ]
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "status": "ok"
            }
            """

    Scenario: As a phoning national manager I can get the survey list filtered by the name
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/surveys?name=national&scope=phoning_national_manager"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 2,
                    "items_per_page": 2,
                    "count": 2,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "uuid": "@uuid@",
                        "type": "national",
                        "name": "Le deuxième questionnaire national",
                        "created_at": "@string@.isDateTime()",
                        "published": true,
                        "creator": null,
                        "nb_questions": 1,
                        "nb_answers": 0
                    },
                    {
                        "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                        "type": "national",
                        "name": "Questionnaire national numéro 1",
                        "created_at": "@string@.isDateTime()",
                        "published": true,
                        "creator": null,
                        "nb_questions": 2,
                        "nb_answers": 14
                    }
                ]
            }
            """

    Scenario Outline: As a user with national scope I can access only national surveys
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/surveys?scope=<scope>&page_size=10"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 3,
                    "items_per_page": 10,
                    "count": 3,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "uuid": "4c3594d4-fb6f-4e25-ac2e-7ef81694ec47",
                        "type": "national",
                        "name": "Les enjeux des 10 prochaines années",
                        "created_at": "@string@.isDateTime()",
                        "published": true,
                        "creator": null,
                        "nb_questions": 2,
                        "nb_answers": 6
                    },
                    {
                        "uuid": "@uuid@",
                        "type": "national",
                        "name": "Le deuxième questionnaire national",
                        "created_at": "@string@.isDateTime()",
                        "published": true,
                        "creator": null,
                        "nb_questions": 1,
                        "nb_answers": 0
                    },
                    {
                        "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                        "type": "national",
                        "name": "Questionnaire national numéro 1",
                        "created_at": "@string@.isDateTime()",
                        "published": true,
                        "creator": null,
                        "nb_questions": 2,
                        "nb_answers": 14
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                    |
            | deputy@en-marche-dev.fr   | national                 |
            | referent@en-marche-dev.fr | phoning_national_manager |

    Scenario Outline: As a user with (delegated) referent role I can access to national and my local managed zones surveys
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/surveys?scope=<scope>&page_size=10"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 7,
                    "items_per_page": 10,
                    "count": 7,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "zone": {
                            "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                            "code": "77",
                            "name": "Seine-et-Marne",
                            "created_at": "@string@.isDateTime()"
                        },
                        "uuid": "138140e9-1dd2-11b2-a08e-41ae5b09da7d",
                        "created_at": "@string@.isDateTime()",
                        "type": "local",
                        "name": "Questionnaire numéro 1",
                        "published": true,
                        "nb_questions": 4,
                        "creator": {
                            "first_name": "Referent",
                            "last_name": "Referent",
                            "uuid": "@uuid@"
                        },
                        "nb_answers": 3
                    },
                    {
                        "zone": {
                            "uuid": "e3eff020-906e-11eb-a875-0242ac150002",
                            "code": "59",
                            "name": "Nord",
                            "created_at": "@string@.isDateTime()"
                        },
                        "uuid": "dda4cd3a-f7ea-1bc6-9b2f-4bca1f9d02ea",
                        "created_at": "@string@.isDateTime()",
                        "type": "local",
                        "name": "Un deuxième questionnaire",
                        "published": true,
                        "nb_questions": 1,
                        "creator": {
                            "first_name": "Referent75and77",
                            "last_name": "Referent75and77",
                            "uuid": "@uuid@"
                        },
                        "nb_answers": 0
                    },
                    {
                        "zone": {
                            "uuid": "e3efe139-906e-11eb-a875-0242ac150002",
                            "code": "11",
                            "name": "Île-de-France",
                            "created_at": "@string@.isDateTime()"
                        },
                        "uuid": "478a2e65-7e86-1bb9-8078-8b70de061a8a",
                        "created_at": "@string@.isDateTime()",
                        "type": "local",
                        "name": "Un questionnaire de la Région",
                        "published": true,
                        "nb_questions": 0,
                        "creator": {
                            "first_name": "Jacques",
                            "last_name": "Picard",
                            "uuid": "@uuid@"
                        },
                        "nb_answers": 0
                    },
                    {
                        "zone": {
                            "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                            "code": "92",
                            "name": "Hauts-de-Seine",
                            "created_at": "@string@.isDateTime()"
                        },
                        "uuid": "0de90b18-47f5-1606-af9d-74eb1fa4a30a",
                        "created_at": "@string@.isDateTime()",
                        "type": "local",
                        "name": "Un questionnaire avec modification bloquée",
                        "published": true,
                        "nb_questions": 0,
                        "creator": {
                            "first_name": "Referent",
                            "last_name": "Referent",
                            "uuid": "@uuid@"
                        },
                        "nb_answers": 0
                    },
                    {
                        "uuid": "4c3594d4-fb6f-4e25-ac2e-7ef81694ec47",
                        "created_at": "@string@.isDateTime()",
                        "type": "national",
                        "name": "Les enjeux des 10 prochaines années",
                        "published": true,
                        "nb_questions": 2,
                        "creator": null,
                        "nb_answers": 6
                    },
                    {
                        "uuid": "1f07832c-2a69-1e80-a33a-d5f9460e838f",
                        "created_at": "@string@.isDateTime()",
                        "type": "national",
                        "name": "Le deuxième questionnaire national",
                        "published": true,
                        "nb_questions": 1,
                        "creator": null,
                        "nb_answers": 0
                    },
                    {
                        "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                        "created_at": "@string@.isDateTime()",
                        "type": "national",
                        "name": "Questionnaire national numéro 1",
                        "published": true,
                        "nb_questions": 2,
                        "creator": null,
                        "nb_answers": 14
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a user with local role I can filter surveys by type
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/surveys?scope=president_departmental_assembly&page_size=2&type=national"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 3,
                    "items_per_page": 2,
                    "count": 2,
                    "current_page": 1,
                    "last_page": 2
                },
                "items": [
                    {
                        "uuid": "@uuid@",
                        "type": "national",
                        "name": "Les enjeux des 10 prochaines années",
                        "created_at": "@string@.isDateTime()",
                        "published": true,
                        "creator": null,
                        "nb_questions": 2,
                        "nb_answers": 6
                    },
                    {
                        "uuid": "1f07832c-2a69-1e80-a33a-d5f9460e838f",
                        "type": "national",
                        "name": "Le deuxième questionnaire national",
                        "created_at": "@string@.isDateTime()",
                        "published": true,
                        "creator": null,
                        "nb_questions": 1,
                        "nb_answers": 0
                    }
                ]
            }
            """

        When I send a "GET" request to "/api/v3/surveys?scope=president_departmental_assembly&page_size=2&type=local"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 4,
                    "items_per_page": 2,
                    "count": 2,
                    "current_page": 1,
                    "last_page": 2
                },
                "items": [
                    {
                        "zone": {
                            "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                            "code": "77",
                            "name": "Seine-et-Marne",
                            "created_at": "@string@.isDateTime()"
                        },
                        "uuid": "138140e9-1dd2-11b2-a08e-41ae5b09da7d",
                        "created_at": "@string@.isDateTime()",
                        "type": "local",
                        "name": "Questionnaire numéro 1",
                        "published": true,
                        "nb_questions": 4,
                        "creator": {
                            "first_name": "Referent",
                            "last_name": "Referent",
                            "uuid": "@uuid@"
                        },
                        "nb_answers": 3
                    },
                    {
                        "zone": {
                            "uuid": "e3eff020-906e-11eb-a875-0242ac150002",
                            "code": "59",
                            "name": "Nord",
                            "created_at": "@string@.isDateTime()"
                        },
                        "uuid": "dda4cd3a-f7ea-1bc6-9b2f-4bca1f9d02ea",
                        "created_at": "@string@.isDateTime()",
                        "type": "local",
                        "name": "Un deuxième questionnaire",
                        "published": true,
                        "nb_questions": 1,
                        "creator": {
                            "first_name": "Referent75and77",
                            "last_name": "Referent75and77",
                            "uuid": "@uuid@"
                        },
                        "nb_answers": 0
                    }
                ]
            }
            """

    Scenario: As a user with national role I cannot read a local survey
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/surveys/138140e9-1dd2-11b2-a08e-41ae5b09da7d?scope=national"
        Then the response status code should be 403

    Scenario Outline: As a user with a (delegated) referent role I can read a local survey of my managed zone
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/surveys/138140e9-1dd2-11b2-a08e-41ae5b09da7d?scope=<scope>"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
              "type": "local",
              "zone": {
                "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                "code": "77",
                "name": "Seine-et-Marne"
              },
              "uuid": "138140e9-1dd2-11b2-a08e-41ae5b09da7d",
              "creator": {
                "first_name": "Referent",
                "last_name": "Referent",
                "uuid": "@uuid@"
              },
              "name": "Questionnaire numéro 1",
              "published": true,
              "questions": [
                {
                  "id": @integer@,
                  "type": "simple_field",
                  "content": "Ceci est-il un champ libre ?",
                  "choices": []
                },
                {
                  "id": @integer@,
                  "type": "multiple_choice",
                  "content": "Est-ce une question à choix multiple ?",
                  "choices": [
                    {
                      "id": @integer@,
                      "content": "Réponse A"
                    },
                    {
                      "id": @integer@,
                      "content": "Réponse B"
                    }
                  ]
                },
                {
                  "id": @integer@,
                  "type": "unique_choice",
                  "content": "Est-ce une question à choix unique ?",
                  "choices": [
                    {
                      "id": @integer@,
                      "content": "Réponse unique 1"
                    },
                    {
                      "id": @integer@,
                      "content": "Réponse unique 2"
                    }
                  ]
                },
                {
                  "id": @integer@,
                  "type": "simple_field",
                  "content": "Ceci est-il un champ libre d'une question suggérée ?",
                  "choices": []
                }
              ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a user with national scope I cannot create a local survey
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/surveys?scope=national" with body:
            """
            {
                "type": "local",
                "name": "test questionnaire local du 92",
                "zone": "e3efe6fd-906e-11eb-a875-0242ac150002",
                "published": true,
                "questions": [
                    {
                        "question": {
                            "type": "simple_field",
                            "content": "Aimez vous Noël ?"
                        }
                    },
                    {
                        "question": {
                            "type": "unique_choice",
                            "content": "la question à choix unique",
                            "choices": [
                                {
                                    "content": "réponse A"
                                },
                                {
                                    "content": "réponse B"
                                }
                            ]
                        }
                    },
                    {
                        "question": {
                            "type": "multiple_choice",
                            "content": "la question à choix multiple",
                            "choices": [
                                {
                                    "content": "réponse A"
                                },
                                {
                                    "content": "réponse B"
                                },
                                {
                                    "content": "réponse C"
                                },
                                {
                                    "content": "réponse D"
                                }
                            ]
                        }
                    }
                ]
            }
            """
        Then the response status code should be 400
        And the JSON node "violations[0].message" should be equal to "Vous ne pouvez pas créer ou modifier un questionnaire de type local avec le scope national."

    Scenario: As a user with national scope I cannot create a local survey
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/surveys?scope=president_departmental_assembly" with body:
            """
            {
                "type": "national",
                "name": "test questionnaire national 2202",
                "published": true,
                "questions": [
                    {
                        "question": {
                            "type": "simple_field",
                            "content": "Aimez vous Noël ?"
                        }
                    },
                    {
                        "question": {
                            "type": "unique_choice",
                            "content": "la question à choix unique",
                            "choices": [
                                {
                                    "content": "réponse A"
                                },
                                {
                                    "content": "réponse B"
                                }
                            ]
                        }
                    },
                    {
                        "question": {
                            "type": "multiple_choice",
                            "content": "la question à choix multiple",
                            "choices": [
                                {
                                    "content": "réponse A"
                                },
                                {
                                    "content": "réponse B"
                                },
                                {
                                    "content": "réponse C"
                                },
                                {
                                    "content": "réponse D"
                                }
                            ]
                        }
                    }
                ]
            }
            """
        Then the response status code should be 400
        And the JSON node "violations[0].message" should be equal to "Vous ne pouvez pas créer ou modifier un questionnaire de type national avec le scope president_departmental_assembly."

    Scenario: As a user with national role I can create a national survey
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/surveys?scope=national" with body:
            """
            {
                "type": "national",
                "name": "test questionnaire national 2202",
                "published": true,
                "questions": [
                    {
                        "question": {
                            "type": "simple_field",
                            "content": "Aimez vous Noël ?"
                        }
                    },
                    {
                        "question": {
                            "type": "unique_choice",
                            "content": "la question à choix unique",
                            "choices": [
                                {
                                    "content": "réponse A"
                                },
                                {
                                    "content": "réponse B"
                                }
                            ]
                        }
                    },
                    {
                        "question": {
                            "type": "multiple_choice",
                            "content": "la question à choix multiple",
                            "choices": [
                                {
                                    "content": "réponse A"
                                },
                                {
                                    "content": "réponse B"
                                },
                                {
                                    "content": "réponse C"
                                },
                                {
                                    "content": "réponse D"
                                }
                            ]
                        }
                    }
                ]
            }
            """
        Then the response status code should be 201
        And the JSON should be equal to:
            """
            {
              "type": "national",
              "uuid": "@uuid@",
              "name": "test questionnaire national 2202",
              "published": true,
              "creator": {
                "first_name": "Député",
                "last_name": "PARIS I",
                "uuid": "@uuid@"
              },
              "questions": [
                {
                  "id": @integer@,
                  "type": "simple_field",
                  "content": "Aimez vous Noël ?",
                  "choices": []
                },
                {
                  "id": @integer@,
                  "type": "unique_choice",
                  "content": "la question à choix unique",
                  "choices": [
                    {
                      "id": @integer@,
                      "content": "réponse A"
                    },
                    {
                      "id": @integer@,
                    "content": "réponse B"
                    }
                  ]
                },
                {
                  "id": @integer@,
                  "type": "multiple_choice",
                  "content": "la question à choix multiple",
                  "choices": [
                    {
                      "id": @integer@,
                      "content": "réponse A"
                    },
                    {
                      "id": @integer@,
                      "content": "réponse B"
                    },
                    {
                      "id": @integer@,
                      "content": "réponse C"
                    },
                    {
                      "id": @integer@,
                      "content": "réponse D"
                    }
                  ]
                }
              ]
            }
            """

    Scenario Outline: As a user with a (delegated) referent role I can create a local survey
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/surveys?scope=<scope>" with body:
            """
            {
                "type": "local",
                "name": "test questionnaire local du 92",
                "zone": "e3efe6fd-906e-11eb-a875-0242ac150002",
                "published": true,
                "questions": [
                    {
                        "question": {
                            "type": "simple_field",
                            "content": "Aimez vous Noël ?"
                        }
                    },
                    {
                        "question": {
                            "type": "unique_choice",
                            "content": "la question à choix unique",
                            "choices": [
                                {
                                    "content": "réponse A"
                                },
                                {
                                    "content": "réponse B"
                                }
                            ]
                        }
                    },
                    {
                        "question": {
                            "type": "multiple_choice",
                            "content": "la question à choix multiple",
                            "choices": [
                                {
                                    "content": "réponse A"
                                },
                                {
                                    "content": "réponse B"
                                },
                                {
                                    "content": "réponse C"
                                },
                                {
                                    "content": "réponse D"
                                }
                            ]
                        }
                    }
                ]
            }
            """
        Then the response status code should be 201
        And the JSON should be equal to:
            """
            {
              "type": "local",
              "zone": {
                  "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                  "code": "92",
                  "name": "Hauts-de-Seine"
              },
              "uuid": "@uuid@",
              "name": "test questionnaire local du 92",
              "published": true,
              "creator": {
                "first_name": "Referent",
                "last_name": "Referent",
                "uuid": "@uuid@"
              },
              "questions": [
                {
                  "id": @integer@,
                  "type": "simple_field",
                  "content": "Aimez vous Noël ?",
                  "choices": []
                },
                {
                  "id": @integer@,
                  "type": "unique_choice",
                  "content": "la question à choix unique",
                  "choices": [
                    {
                      "id": @integer@,
                      "content": "réponse A"
                    },
                    {
                      "id": @integer@,
                    "content": "réponse B"
                    }
                  ]
                },
                {
                  "id": @integer@,
                  "type": "multiple_choice",
                  "content": "la question à choix multiple",
                  "choices": [
                    {
                      "id": @integer@,
                      "content": "réponse A"
                    },
                    {
                      "id": @integer@,
                      "content": "réponse B"
                    },
                    {
                      "id": @integer@,
                      "content": "réponse C"
                    },
                    {
                      "id": @integer@,
                      "content": "réponse D"
                    }
                  ]
                }
              ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user with (delegated) referent role I can unpublished a local survey
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/surveys/138140e9-1dd2-11b2-a08e-41ae5b09da7d?scope=<scope>" with body:
            """
            {
                "published": false
            }
            """
        Then the response status code should be 200
        And the JSON node "published" should be false

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user with (delegated) referent role I can update a local survey
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/surveys/138140e9-1dd2-11b2-a08e-41ae5b09da7d?scope=<scope>" with body:
            """
            {
                "name": "5ans à l'écoute",
                "zone": "e3efe5c5-906e-11eb-a875-0242ac150002",
                "published": true,
                "questions": [
                    {
                        "id": 1,
                        "question": {
                            "type": "simple_field",
                            "content": "Qu'est ce qui a changé près de chez vous?",
                            "choices": []
                        }
                    },
                    {
                        "id": 2,
                        "question": {
                            "type": "multiple_choice",
                            "content": "5ans de plus?",
                            "choices": [
                                {
                                    "id": 1,
                                    "content": "Oui"
                                },
                                {
                                    "id": 2,
                                    "content": "Non"
                                },
                                {
                                    "id": null,
                                    "content": "Je ne sais pas"
                                }
                            ]
                        }
                    },
                    {
                        "id": 3,
                        "question": {
                            "type": "unique_choice",
                            "content": "Êtes vous satisfait?",
                            "choices": [
                                {
                                    "id": 3,
                                    "content": "Oui"
                                },
                                {
                                    "id": 4,
                                    "content": "Non"
                                }
                            ]
                        }
                    }
                ]
            }
            """
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
              "type": "local",
              "zone": {
                "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                "code": "77",
                "name": "Seine-et-Marne"
              },
              "uuid": "138140e9-1dd2-11b2-a08e-41ae5b09da7d",
              "name": "5ans à l'écoute",
              "published": true,
              "creator": {
                "first_name": "Referent",
                "last_name": "Referent",
                "uuid": "@uuid@"
              },
              "questions": [
                {
                  "id": 1,
                  "type": "simple_field",
                  "content": "Qu'est ce qui a changé près de chez vous?",
                  "choices": []
                },
                {
                  "id": 2,
                  "type": "multiple_choice",
                  "content": "5ans de plus?",
                  "choices": [
                    {
                      "id": 1,
                      "content": "Oui"
                    },
                    {
                      "id": 2,
                      "content": "Non"
                    },
                    {
                      "id": @integer@,
                      "content": "Je ne sais pas"
                    }
                  ]
                },
                {
                  "id": 3,
                  "type": "unique_choice",
                  "content": "Êtes vous satisfait?",
                  "choices": [
                    {
                      "id": 3,
                      "content": "Oui"
                    },
                    {
                      "id": 4,
                      "content": "Non"
                    }
                  ]
                }
              ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a user with national role I can get a national survey replies
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/surveys/4c3594d4-fb6f-4e25-ac2e-7ef81694ec47/replies?scope=national"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
              "metadata": {
                "total_items": 6,
                "items_per_page": 30,
                "count": 6,
                "current_page": 1,
                "last_page": 1
              },
              "items": [
                {
                  "author": {
                    "gender": "male",
                    "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                    "first_name": "Patrick",
                    "last_name": "Bialès",
                    "age": @integer@
                  },
                  "author_postal_code": "75010",
                  "uuid": "@uuid@",
                  "type": "PAP",
                  "interviewed": {
                    "first_name": "Javier",
                    "last_name": "Latombe",
                    "gender": "other",
                    "age_range": "between_25_39"
                  },
                  "begin_at": "@string@.isDateTime()",
                  "finish_at": "@string@.isDateTime()",
                  "answers": [
                    {
                      "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                      "type": "simple_field",
                      "question_id": @integer@,
                      "answer": "Vie publique, répartition des pouvoirs et démocratie"
                    },
                    {
                      "question": "L'écologie est selon vous, importante pour :",
                      "type": "multiple_choice",
                      "question_id": @integer@,
                      "answer": [
                        "L'héritage laissé aux générations futures",
                        "Le bien-être sanitaire"
                      ]
                    }
                  ]
                },
                {
                  "author": {
                    "gender": "male",
                    "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                    "first_name": "Patrick",
                    "last_name": "Bialès",
                    "age": @integer@
                  },
                  "author_postal_code": "75010",
                  "uuid": "@uuid@",
                  "type": "PAP",
                  "interviewed": {
                    "first_name": null,
                    "last_name": null,
                    "gender": null,
                    "age_range": null
                  },
                  "begin_at": "@string@.isDateTime()",
                  "finish_at": "@string@.isDateTime()",
                  "answers": [
                    {
                      "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                      "type": "simple_field",
                      "question_id": @integer@,
                      "answer": "Les ressources énergétiques"
                    },
                    {
                      "question": "L'écologie est selon vous, importante pour :",
                      "type": "multiple_choice",
                      "question_id": @integer@,
                      "answer": [
                        "L'aspect financier",
                        "La préservation de l'environnement"
                      ]
                    }
                  ]
                },
                {
                  "author": {
                    "gender": "male",
                    "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                    "first_name": "Patrick",
                    "last_name": "Bialès",
                    "age": @integer@
                  },
                  "author_postal_code": "75010",
                  "uuid": "@uuid@",
                  "type": "PAP",
                  "interviewed": {
                    "first_name": null,
                    "last_name": null,
                    "gender": null,
                    "age_range": null
                  },
                  "begin_at": "@string@.isDateTime()",
                  "finish_at": "@string@.isDateTime()",
                  "answers": [
                    {
                      "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                      "type": "simple_field",
                      "question_id": @integer@,
                      "answer": "Nouvelles technologies"
                    },
                    {
                      "question": "L'écologie est selon vous, importante pour :",
                      "type": "multiple_choice",
                      "question_id": @integer@,
                      "answer": [
                        "L'héritage laissé aux générations futures",
                        "Le bien-être sanitaire"
                      ]
                    }
                  ]
                },
                {
                  "author": {
                    "gender": "female",
                    "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                    "first_name": "Lucie",
                    "last_name": "Olivera",
                    "age": @integer@
                  },
                  "author_postal_code": "75009",
                  "uuid": "@uuid@",
                  "type": "Phoning",
                  "interviewed": {
                    "first_name": "Adherent 40",
                    "last_name": "Adherent 40",
                    "gender": "female",
                    "age_range": null
                  },
                  "begin_at": "@string@.isDateTime()",
                  "finish_at": "@string@.isDateTime()",
                  "answers": [
                    {
                      "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                      "type": "simple_field",
                      "question_id": @integer@,
                      "answer": "l'écologie sera le sujet le plus important"
                    },
                    {
                      "question": "L'écologie est selon vous, importante pour :",
                      "type": "multiple_choice",
                      "question_id": @integer@,
                      "answer": [
                        "L'héritage laissé aux générations futures",
                        "Le bien-être sanitaire"
                      ]
                    }
                  ]
                },
                {
                  "author": {
                    "gender": "female",
                    "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                    "first_name": "Lucie",
                    "last_name": "Olivera",
                    "age": @integer@
                  },
                  "author_postal_code": "75009",
                  "uuid": "@uuid@",
                  "type": "Phoning",
                  "interviewed": {
                    "first_name": "Adherent 34",
                    "last_name": "Adherent 34",
                    "gender": "female",
                    "age_range": null
                  },
                  "begin_at": "@string@.isDateTime()",
                  "finish_at": "@string@.isDateTime()",
                  "answers": [
                    {
                      "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                      "type": "simple_field",
                      "question_id": @integer@,
                      "answer": "le pouvoir d'achat"
                    },
                    {
                      "question": "L'écologie est selon vous, importante pour :",
                      "type": "multiple_choice",
                      "question_id": @integer@,
                      "answer": [
                        "L'aspect financier",
                        "La préservation de l'environnement"
                      ]
                    }
                  ]
                },
                {
                  "author": {
                    "gender": "female",
                    "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                    "first_name": "Lucie",
                    "last_name": "Olivera",
                    "age": @integer@
                  },
                  "author_postal_code": "75009",
                  "uuid": "@uuid@",
                  "type": "Phoning",
                  "interviewed": {
                    "first_name": "Adherent 37",
                    "last_name": "Adherent 37",
                    "gender": "male",
                    "age_range": null
                  },
                  "begin_at": "@string@.isDateTime()",
                  "finish_at": "@string@.isDateTime()",
                  "answers": [
                    {
                      "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                      "type": "simple_field",
                      "question_id": @integer@,
                      "answer": "la conquête de l'espace"
                    },
                    {
                      "question": "L'écologie est selon vous, importante pour :",
                      "type": "multiple_choice",
                      "question_id": @integer@,
                      "answer": [
                        "L'héritage laissé aux générations futures",
                        "Le bien-être sanitaire"
                      ]
                    }
                  ]
                }
              ]
            }
            """

    Scenario Outline: As a user with (delegated) referent role I can get a national survey replies of my zones
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/surveys/4c3594d4-fb6f-4e25-ac2e-7ef81694ec47/replies?scope=<scope>"
        Then the response status code should be 200
        And the JSON node items should have 6 element

        Examples:
            | user                            | scope                                          |
            | referent-75-77@en-marche-dev.fr | president_departmental_assembly                |
            | francis.brioul@yahoo.com        | delegated_689757d2-dea5-49d1-95fe-281fc860ff77 |

    Scenario: As a Correspondent user I can get the list of national surveys and my correspondent zone surveys
        Given I am logged with "je-mengage-user-1@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/surveys?scope=correspondent&page_size=10"
        Then the response status code should be 200
        And the JSON node items should have 4 element

    Scenario Outline: As a user with (delegated) referent role I cannot delete all questions on a survey
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/surveys/138140e9-1dd2-11b2-a08e-41ae5b09da7d?scope=<scope>" with body:
            """
            {
                "name": "5ans à l'écoute",
                "zone": "e3efe5c5-906e-11eb-a875-0242ac150002",
                "published": true,
                "questions": []
            }
            """
        Then the response status code should be 400
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "questions",
                        "message": "Le questionnaire doit contenir au moins une question."
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a user with a (delegated) local role I can get surveys KPI
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/surveys/kpi?scope=president_departmental_assembly"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "national_surveys_count": 3,
                "national_surveys_published_count": 3,
                "local_surveys_count": 4,
                "local_surveys_published_count": 4
            }
            """

    Scenario: As a user with a local role I can get the last month jemengage data survey geocode
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/jemarche_data_surveys/kpi?scope=president_departmental_assembly"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "latitude": 48.518219,
                    "longitude": 2.624205,
                    "posted_at": "@string@.isDateTime()",
                    "survey_name": "Questionnaire numéro 1"
                },
                {
                    "latitude": 48.518219,
                    "longitude": 2.624205,
                    "posted_at": "@string@.isDateTime()",
                    "survey_name": "Questionnaire national numéro 1"
                }
            ]
            """
