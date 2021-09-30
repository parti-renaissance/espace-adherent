@api
Feature:
  In order to see phoning campaigns
  As a non logged-in user
  I should be able to access API phoning campaigns

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                |
      | LoadClientData                  |
      | LoadTeamData                    |
      | LoadJecouteSurveyData           |
      | LoadPhoningCampaignData         |
      | LoadPhoningCampaignHistoryData  |

  Scenario Outline: As a non logged-in user I cannot get and manage phoning campaigns
    Given I send a "<method>" request to "<url>"
    Then the response status code should be 401
    Examples:
      | method  | url                                                                                     |
      | POST    | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/start                    |
      | GET     | /api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055/survey-config   |
      | PUT     | /api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055                 |
      | GET     | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/survey                   |
      | GET     | /api/v3/phoning_campaigns/tutorial                                                      |

  Scenario Outline: As a logged-in user with no correct rights I cannot get regular phoning campaigns (only permanent)
    Given I am logged with "benjyd@aol.com" via OAuth client "JeMarche App"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 403
    Examples:
      | method  | url                                                                                   |
      | GET     | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/scores                 |
      | GET     | /api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055/survey-config |
      | POST    | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/start                  |

  Scenario: As a logged-in user I can get my phoning campaigns
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/phoning_campaigns/scores"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
      {
        "title": "Campagne pour les femmes",
        "brief": "### Campagne pour les femmes",
        "goal": 500,
        "finish_at": "@string@.isDateTime()",
        "uuid": "4d91b94c-4b39-43c7-9c88-f4be7e2fe0bc",
        "nb_calls": 0,
        "nb_surveys": 0,
        "permanent": false,
        "scoreboard": [
          {
            "firstName": "Jacques",
            "nb_calls": "4",
            "nb_surveys": "3",
            "position": 1,
            "caller": false
          },
          {
            "firstName": "Pierre",
            "nb_calls": "1",
            "nb_surveys": "1",
            "position": 2,
            "caller": false
          },
          {
            "firstName": "Député",
            "nb_calls": "1",
            "nb_surveys": "1",
            "position": 3,
            "caller": false
          },
          {
            "firstName": "Lucie",
            "nb_calls": "0",
            "nb_surveys": "0",
            "position": 4,
            "caller": true
          }
        ]
      },
      {
        "title": "Campagne avec l'audience contenant tous les paramètres",
        "brief": "**Campagne** avec l'audience contenant tous les paramètres",
        "goal": 10,
        "finish_at": "@string@.isDateTime()",
        "uuid": "cc8f32ce-176c-42c8-a7e9-b854cc8fc61e",
        "nb_calls": 0,
        "nb_surveys": 0,
        "permanent": false,
        "scoreboard": [
          {
            "firstName": "Jacques",
            "nb_calls": "0",
            "nb_surveys": "0",
            "position": "@integer@",
            "caller": false
          },
          {
            "firstName": "Lucie",
            "nb_calls": "0",
            "nb_surveys": "0",
            "position": "@integer@",
            "caller": true
          },
          {
            "firstName": "Pierre",
            "nb_calls": "0",
            "nb_surveys": "0",
            "position": "@integer@",
            "caller": false
          },
          {
            "firstName": "Député",
            "nb_calls": "0",
            "nb_surveys": "0",
            "position": "@integer@",
            "caller": false
          }
        ]
      },
      {
        "brief": "# Campagne permanente !\n**Campagne** pour passer des appels à ses contacts",
        "finish_at": null,
        "goal": 42,
        "nb_calls": 0,
        "nb_surveys": 0,
        "permanent": true,
        "scoreboard": [],
        "title": "Campagne permanente",
        "uuid": "b48af58c-51e8-4f1b-a432-deace2969fda"
     }
    ]
    """

  Scenario: As a logged-in user I can get one of my phoning campaigns
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/phoning_campaigns/4d91b94c-4b39-43c7-9c88-f4be7e2fe0bc/scores"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
      {
        "title": "Campagne pour les femmes",
        "brief": "### Campagne pour les femmes",
        "goal": 500,
        "finish_at": "@string@.isDateTime()",
        "uuid": "4d91b94c-4b39-43c7-9c88-f4be7e2fe0bc",
        "nb_calls": 4,
        "nb_surveys": 3,
        "permanent": false,
        "scoreboard": [
          {
            "firstName": "Jacques",
            "nb_calls": "4",
            "nb_surveys": "3",
            "position": 1,
            "caller": true
          },
          {
            "firstName": "Pierre",
            "nb_calls": "1",
            "nb_surveys": "1",
            "position": 2,
            "caller": false
          },
          {
            "firstName": "Député",
            "nb_calls": "1",
            "nb_surveys": "1",
            "position": 3,
            "caller": false
          },
          {
            "firstName": "Lucie",
            "nb_calls": "0",
            "nb_surveys": "0",
            "position": 4,
            "caller": false
          }
        ]
      }
    """

  Scenario: As a logged-in user with correct rights I can get a phone number to call
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "POST" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/start"
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
     "adherent": {
       "info": "Député, @integer@ ans, habitant Paris 2e (75002). N’a encore jamais été appelé.",
       "gender": "male",
       "phone": {
         "country": "FR",
         "number": "01 87 65 67 81"
       },
       "uuid": "160cdf45-80c4-4663-aa21-0ae23091a381"
     },
     "uuid": "@uuid@"
    }
    """

  Scenario: As a logged-in user with correct rights I cannot get a phone number to call if no available number
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "POST" request to "/api/v3/phoning_campaigns/b5e1b850-faec-4da7-8da6-d64b94494668/start"
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
      "code": "no_available_number",
      "message": "Aucun numéro à appeler disponible"
    }
    """

  Scenario: As a logged-in user with correct rights I cannot get a phone number to call if the campaign is finished
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "POST" request to "/api/v3/phoning_campaigns/fdc99fb4-0492-4488-a53d-b7aa02888ffe/start"
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
      "code": "finished_campaign",
      "message": "Cette campagne est terminée"
    }
    """

  Scenario: As a logged-in user, a caller of the phoning campaign history, I can get a phoning campaign history configuration
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055/survey-config"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "call_status": {
            "finished": [
                {
                    "code": "answered",
                    "label": "Il accepte de répondre aux questions"
                },
                {
                    "code": "to-unsubscribe",
                    "label": "Ne souhaite plus être rappelé"
                },
                {
                    "code": "to-unjoin",
                    "label": "Souhaite désadhérer"
                },
                {
                    "code": "not-respond",
                    "label": "N'a pa répondu au téléphone"
                },
                {
                    "code": "to-remind",
                    "label": "Souhaite être rappelé plus tard"
                },
                {
                    "code": "failed",
                    "label": "L'appel a échoué"
                }
            ],
            "interrupted": [
                {
                    "code": "interrupted-dont-remind",
                    "label": "Appel interrompu, ne pas rappeler"
                },
                {
                    "code": "interrupted",
                    "label": "Appel interrompu"
                }
            ]
        },
        "satisfaction_questions": [
            {
                "code": "need_sms_renewal",
                "label": "Souhaitez-vous vous réabonner à nos SMS ?",
                "type": "boolean"
            },
            {
                "code": "postal_code_checked",
                "label": "Habitez-vous toujours à Melun (77000) ?",
                "type": "boolean"
            },
            {
                "code": "profession",
                "label": "Quel est votre métier ?",
                "type": "text"
            },
            {
                "code": "engagement",
                "label": "Souhaitez-vous vous (re)engager sur le terrain ?",
                "type": "choice",
                "choices": {
                  "active": "Déjà actif",
                  "want_to_engage": "Souhaite se mobiliser",
                  "dont_want_to_engage": "Ne le souhaite pas"
                }
            },
            {
                "code": "note",
                "label": "Comment s'est passé cet appel ?",
                "type": "note",
                "values": [
                    1,
                    2,
                    3,
                    4,
                    5
                ]
            }
        ]
    }
    """

  Scenario: As a logged-in user I cannot change not my phoning campaign history
    Given I am logged with "kiroule.p@blabla.tld" via OAuth client "JeMarche App"
    When I send a "PUT" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055"
    Then the response status code should be 403

  Scenario: As a logged-in user I cannot change my phoning campaign history with wrong data
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055" with body:
    """
    {
        "status": "send"
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
       "type":"https://tools.ietf.org/html/rfc2616#section-10",
       "title":"An error occurred",
       "detail":"status: Le statut n'est pas valide.",
       "violations":[
          {
             "propertyPath":"status",
             "message":"Le statut n'est pas valide."
          }
       ]
    }
    """

  Scenario: As a logged-in user I can change only status of my phoning campaign history
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055" with body:
    """
    {
        "status": "not-respond"
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "status": "not-respond",
        "uuid": "47bf09fb-db03-40c3-b951-6fe6bbe1f055"
    }
    """

  Scenario: As a logged-in user I can change my phoning campaign history
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055" with body:
    """
    {
        "status": "completed",
        "postal_code_checked": true,
        "need_email_renewal": false,
        "need_sms_renewal": false,
        "engagement": "want_to_engage",
        "profession": "student",
        "note": 4
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "status": "completed",
        "uuid": "47bf09fb-db03-40c3-b951-6fe6bbe1f055"
    }
    """

  Scenario: As a logged-in user with no correct rights I cannot get a campaign survey
    Given I am logged with "benjyd@aol.com" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/survey"
    Then the response status code should be 403

  Scenario: As a logged-in user I can get a campaign survey
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    And I send a "GET" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/survey"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "id":1,
      "uuid":"@uuid@",
      "type": "national",
      "questions":[
        {
          "id":6,
          "type":"simple_field",
          "content":"Une première question du 1er questionnaire national ?",
          "choices":[]
        },
        {
          "id":7,
          "type":"multiple_choice",
          "content":"Une deuxième question du 1er questionnaire national ?",
          "choices":[
            {
              "id":5,
              "content":"Réponse nationale A"
            },
            {
              "id":6,
              "content":"Réponse nationale B"
            },
            {
              "id":7,
              "content":"Réponse nationale C"
            },
            {
              "id":8,
              "content":"Réponse nationale D"
            }
          ]
        }
      ],
      "name":"Questionnaire national numéro 1"
    }
    """

  Scenario: As a logged-in user I can get a campaign survey
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    And I send a "GET" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/survey"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "id":1,
      "uuid":"@uuid@",
      "type": "national",
      "questions":[
        {
          "id":6,
          "type":"simple_field",
          "content":"Une première question du 1er questionnaire national ?",
          "choices":[]
        },
        {
          "id":7,
          "type":"multiple_choice",
          "content":"Une deuxième question du 1er questionnaire national ?",
          "choices":[
            {
              "id":5,
              "content":"Réponse nationale A"
            },
            {
              "id":6,
              "content":"Réponse nationale B"
            },
            {
              "id":7,
              "content":"Réponse nationale C"
            },
            {
              "id":8,
              "content":"Réponse nationale D"
            }
          ]
        }
      ],
      "name":"Questionnaire national numéro 1"
    }
    """

  Scenario: As a logged-in user I can get a campaign survey
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    And I send a "GET" request to "/api/v3/phoning_campaigns/tutorial"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "content": "# Conseils pour réussir ses appels\n\nToujours rester courtois et souriant\n\nPeu importe le comportement de la personne à l’autre bout du téléphone, en tant que membre de l’équipe de La République En Marche, soyez polis et écourter l’échange pour éviter l’invective.\n\n## Ne pas insister\n\nInsister avec une personne qui émet plusieurs objections ou entre dans un débat contradictoire est contre-productif, mieux vaut finir cordialement d’un commun accord que perdre le temps précieux qui vous permettra de mobiliser d’autres personnes.\n\n## Bien assurer son suivi d’appel (fichier individuel)\n\nPour éviter d’être plusieurs à appeler par erreur les mêmes numéros, ou rappeler quelqu’un qui a déjà été contacté, mieux vaut s’assurer d’être en possession d’une liste qui n’a pas encore été prise en charge.\n\nChoisissez un endroit où les conditions de phoning seront bonnes ; évitez les endroits bruyants ou les dérangements récurrents. Ne pas se laisser distraire par son environnement.\n\nÉcoute active : indispensable à l’accompagnement de l’interlocuteur.\n\nTéléphoner plutôt sur un créneau horaire entre 9h et 19h, du lundi au samedi.\n\nNe pas parler trop lentement (sensation de perte de temps), ou trop vite (risque de faire répéter), s’adapter au débit de l’interlocuteur et adapter le ton de la voix.\n\nNe pas parler trop fort, ne pas couper la parole\n\nToujours rester courtois et souriant\n\nPeu importe le comportement de la personne à l’autre bout du téléphone, en tant que membre de l’équipe de La République En Marche, soyez polis et écourter l’échange pour éviter l’invective.\n\nNe pas insister\n\nInsister avec une personne qui émet plusieurs objections ou entre dans un débat contradictoire est contre-productif, mieux vaut finir cordialement d’un commun accord que perdre le temps précieux qui vous permettra de mobiliser d’autres personnes.\n\n**CONCLUSION :** Remerciements, salutations, toujours raccrocher après l’interlocuteur\n\n# Les situations que vous pourriez rencontrer\n\n## Je n’ai pas le temps !\n\nPuis-je vous rappeler plus tard ?\n\n## Je suis déçu(e) par LaREM\/Macron\n\nSi vous sentez que la personne est ouverte à la discussion, vous pouvez éventuellement poser la question « Y a-t-il une raison particulière à cette déception, une mesure du Gouvernement en particulier ? »\n\nEn tant qu’animateur local êtes-vous en lien avec vos parlementaires, leur faites-vous part de votre mécontentement ?\n\nSinon, n’insistez pas ! « Je comprends, je ne vous dérange pas plus et vous souhaite une bonne journée »\n\n## Je souhaite quitter LaREM\n\nSi la personne ne veut plus être AL et vous demande de désadhérer, elle doit d’abord démissionner de ses fonctions d’animateur\/animatrice local(e) en écrivant à [jemarche@en-marche.fr](mailto:jemarche@en-marche.fr)\n\n## Je n’ai pas\/plus le temps de m’engager\n\nSi la personne souhaite ne plus être Als, elle doit démissionner de ses fonctions d’animateur\/animatrice local(e) en écrivant à [jemarche@en-marche.fr](mailto:jemarche@en-marche.fr%E2%80%AF)\n\n## La personne au bout du fil est visiblement dans une grande détresse psychologique, que puis-je faire ?\n\nVous pouvez jouer le rôle de veille et d’alerte mais vous n’êtes pas psychologue, ce n’est ni votre rôle ni votre responsabilité.\n\nN’hésitez pas à indiquer à votre interlocuteur les contacts appropriés pour être aidé. Ils sont tous disponibles ici : [numéros utiles](https:\/\/storage.googleapis.com\/en-marche-fr\/COMMUNICATION\/LaREM-Numeros-utiles-COVID19.pdf). Si besoin, discutez-en avec le référent ou le coordinateur régional pour voir comment nous pourrions aider la personne.\n\nEnfin, gardez à l’esprit qu’un échange sympa est déjà̀ une manière d’aider et d’être présent !\n\n## La discussion m’a complétement échappée et je ne sais pas comment reprendre la main, comment faire ?\n\nSentez-vous libre de reprendre le contrôle de la discussion quand vous l’estimez nécessaire.\n\nFaites-le toujours avec empathie et sans sauter complètement du coq à l’âne mais fermement grâce à des transitions douces du type : « je comprends très bien \/ je note ce que vous me dites \/ nous avons eu d’autres témoignages qui allaient dans ce sens mais on remarque également que ou que pensez-vous d’ailleurs de... » A noter que les gens prennent moins ombrage d’une parole coupée si on la leur redonne ensuite.\n\nSi vraiment la situation s’éternise vous pouvez également recentrer l’appel clairement « l’enjeu de l’appel c’est avant tout de... ».\n\n## La personne au bout du fil multiplie des questions auxquelles je n’ai pas de réponses, que puis-je faire ?\n\nIl faut d’emblée sortir de cette attente : tenez-vous en aux informations et aux sources officielles dont vous pourrez rappeler les liens (c’est également l’occasion de prévenir la désinformation) et recentrer là aussi l’objectif de l’appel : « Je comprends vos attentes en la matière, nous sommes tous dans le même cas mais je n’en sais pas plus que vous et l’objet de cet appel c’est avant tout de ... ».\n\n## La personne au bout du fil est très énervée et je deviens la cible de ses attaques, comment faire ?\n\nBien distinguer votre rôle du rôle officiel du gouvernement ou du Président de la République.\n\nComme pour la question précédente, n’hésitez pas à recentrer la mission de l’appel, sur un enjeu de solidarité́ par exemple : « Je comprends bien, la situation est difficile pour tout le monde mais cela nous paraissait important de prendre de vos nouvelles, de vous  apporter peut-être des éléments de réponse pour agir concrètement » etc.\n\nSi la personne est vraiment virulente, soyez ferme, protégez-vou  et si c’est insoutenable, terminez la discussion : « Je suis navré, l’enjeu de l’appel n’est pas là et je ne suis pas le bon interlocuteur, je vous souhaite bon courage »."
    }
    """
