@api
@renaissance
Feature:
  In order to see, create, edit and delete adherent messages
  As a logged-in user
  I should be able to access API adherent messages

  Scenario: As a logged-in user I can not update adherent message filter with not my segment
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "PUT" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f/filter" with body:
    """
    {
      "segment": "f6c36dd7-0517-4caf-ba6f-ec6822f2ec12"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "type":"https://symfony.com/errors/validation",
       "title":"Validation Failed",
       "detail":"segment: Le segment n'est pas autorisé",
       "violations":[
          {
             "propertyPath":"segment",
             "title":"Le segment n'est pas autorisé",
             "parameters":[]
          }
       ]
    }
    """

  Scenario: As a logged-in user I can update adherent message filter with segment
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "PUT" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f/filter" with body:
    """
    {
      "segment": "830d230f-67fb-4217-9986-1a3ed7d3d5e7"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      "OK"
    """

  Scenario: As a delegated user I can update adherent message filter with segment
    Given I am logged with "senateur@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "PUT" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f/filter?scope=delegated_08f40730-d807-4975-8773-69d8fae1da74" with body:
    """
    {
      "segment": "830d230f-67fb-4217-9986-1a3ed7d3d5e7"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      "OK"
    """

  Scenario Outline: As a logged-in (delegated) referent I can retrieve my messages
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/adherent_messages?scope=<scope>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 102,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 51
        },
        "items": [
            {
                "uuid": "@uuid@",
                "label": "@string@",
                "subject": "@string@",
                "status": "draft",
                "recipient_count": 0,
                "source": "platform",
                "synchronized": false,
                "from_name": "Referent Referent | Renaissance",
                "created_at": "@string@.isDateTime()",
                "sent_at": null,
                "author": {
                    "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                    "first_name": "Referent",
                    "last_name": "Referent"
                },
                "preview_link": null,
                "statistics": {
                    "click_rate": 0,
                    "clicks": 0,
                    "open_rate": 0,
                    "opens": 0,
                    "sent": 0,
                    "unsubscribe": 0,
                    "unsubscribe_rate": 0
                }
            },
            {
                "uuid": "@uuid@",
                "label": "@string@",
                "subject": "@string@",
                "status": "sent",
                "recipient_count": 0,
                "source": "platform",
                "synchronized": false,
                "preview_link": null,
                "from_name": "Referent Referent | Renaissance",
                "created_at": "@string@.isDateTime()",
                "sent_at": "@string@.isDateTime()",
                "author": {
                    "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                    "first_name": "Referent",
                    "last_name": "Referent"
                },
                "statistics": {
                    "click_rate": 0,
                    "clicks": 0,
                    "open_rate": 0,
                    "opens": 0,
                    "sent": 0,
                    "unsubscribe": 0,
                    "unsubscribe_rate": 0
                }
            }
        ]
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      # senateur@en-marche-dev.fr has a delegated access from referent@en-marche-dev.fr and should see the same messages
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a (delegated) referent I can get a content of a message
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f/content?scope=<scope>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "uuid": "969b1f08-53ec-4a7d-8d6e-7654a001b13f",
        "subject": "@string@",
        "content": "<html><head><title>@string@</body></html>\n",
        "json_content": null
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario: As a DC referent I cannot delete a message already sent
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "DELETE" request to "/api/v3/adherent_messages/65f6cdbf-0707-4940-86d8-cc1755aab17e?scope=referent"
    Then the response status code should be 403

  Scenario Outline: As a (delegated) referent I can delete a draft message
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "DELETE" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f?scope=<scope>"
    Then the response status code should be 204
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a (delegated) Correspondent I can create a message
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "POST" request to "/api/v3/adherent_messages?scope=<scope>" with body:
    """
    {
      "type": "correspondent",
      "label": "Label du message qui permet de le retrouver dans la liste des messages envoyés",
      "subject": "L'objet du mail",
      "content": "<table>...</table>",
      "json_content": "{\"foo\": \"bar\", \"items\": [1, 2, true, \"hello world\"]}"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "uuid": "@uuid@",
      "label": "Label du message qui permet de le retrouver dans la liste des messages envoyés",
      "subject": "L'objet du mail",
      "status": "draft",
      "recipient_count": 0,
      "source": "api",
      "synchronized": false
    }
    """
    Examples:
      | user                                | scope                                          |
      | je-mengage-user-1@en-marche-dev.fr  | correspondent                                  |
      | laura@deloche.com                   | delegated_2c6134f7-4312-45c4-9ab7-89f2b0731f86 |

  Scenario: As a delegated referent I can create a message
    Given I am logged with "senateur@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "POST" request to "/api/v3/adherent_messages?scope=delegated_08f40730-d807-4975-8773-69d8fae1da74" with body:
    """
    {
      "type": "referent",
      "label": "Message d'un référent délégué",
      "subject": "L'objet du mail",
      "content": "<table>...</table>",
      "json_content": "{\"foo\": \"bar\", \"items\": [1, 2, true, \"hello world\"]}"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "uuid": "@uuid@",
        "label": "Message d'un référent délégué",
        "subject": "L'objet du mail",
        "status": "draft",
        "recipient_count": 0,
        "source": "api",
        "synchronized": false
    }
    """

  Scenario Outline: As a (delegated) referent I can update a draft message
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "PUT" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f?scope=<scope>" with body:
    """
    {
      "label": "Mon nouveau titre",
      "subject": "Mon nouveau objet du mail",
      "content": "<table>nouveau</table>",
      "json_content": "{\"items\": [\"nouveau\"]}"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "uuid": "969b1f08-53ec-4a7d-8d6e-7654a001b13f",
        "label": "Mon nouveau titre",
        "subject": "Mon nouveau objet du mail",
        "status": "draft",
        "recipient_count": 0,
        "source": "api",
        "synchronized": false
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario: As a delegated referent I can access to sending a referent message
    Given I am logged with "senateur@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "POST" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f/send?scope=delegated_08f40730-d807-4975-8773-69d8fae1da74"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "detail" should be equal to "The message is not yet ready to send."

  Scenario: As a regional coordinator I can create a message
    Given I am logged with "coordinateur@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "POST" request to "/api/v3/adherent_messages?scope=regional_coordinator" with body:
    """
    {
      "type": "regional_coordinator",
      "label": "Label du message qui permet de le retrouver dans la liste des messages envoyés",
      "subject": "L'objet du mail",
      "content": "<table>...</table>",
      "json_content": "{\"foo\": \"bar\", \"items\": [1, 2, true, \"hello world\"]}"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "uuid": "@uuid@",
      "label": "Label du message qui permet de le retrouver dans la liste des messages envoyés",
      "subject": "L'objet du mail",
      "status": "draft",
      "recipient_count": 0,
      "source": "api",
      "synchronized": false
    }
    """

    Scenario Outline: As a user with (delegated) referent role I can get filters list for message feature
      Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
      When I send a "GET" request to "/api/v3/filters?scope=<scope>&feature=messages"
      Then the response status code should be 200
      And the JSON should be equal to:
      """
      [
          {
              "label": "Informations personnelles",
              "color": "#0E7490",
              "filters": [
                  {
                      "code": "gender",
                      "label": "Genre",
                      "options": {
                          "choices": {
                              "female": "Femme",
                              "male": "Homme",
                              "other": "Autre"
                          }
                      },
                      "type": "select"
                  },
                  {
                      "code": "firstName",
                      "label": "Prénom",
                      "options": null,
                      "type": "text"
                  },
                  {
                      "code": "lastName",
                      "label": "Nom",
                      "options": null,
                      "type": "text"
                  },
                  {
                      "code": "age",
                      "label": "Âge",
                      "options": {
                          "first": {
                              "min": 1,
                              "max": 200
                          },
                          "second": {
                              "min": 1,
                              "max": 200
                          }
                      },
                      "type": "integer_interval"
                  },
                  {
                      "code": "isCertified",
                      "label": "Certifié",
                      "options": {
                          "choices": [
                              "Non",
                              "Oui"
                          ]
                      },
                      "type": "select"
                  },
                  {
                      "code": "zone",
                      "label": "Zone géographique",
                      "options": {
                          "url": "/api/v3/zone/autocomplete?types%5B0%5D=borough&types%5B1%5D=canton&types%5B2%5D=city&types%5B3%5D=department&types%5B4%5D=region&types%5B5%5D=country&types%5B6%5D=district&types%5B7%5D=foreign_district&types%5B8%5D=custom",
                          "query_param": "q",
                          "value_param": "uuid",
                          "label_param": "name",
                          "multiple": false,
                          "required": true
                      },
                      "type": "zone_autocomplete"
                  }
              ]
          },
          {
              "label": "Militant",
              "color": "#0F766E",
              "filters": [
                  {
                      "code": "committee",
                      "label": "Comités",
                      "options": {
                          "choices": {
                              "5e00c264-1d4b-43b8-862e-29edc38389b3": "Comité des 3 communes",
                              "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3": "Second Comité des 3 communes"
                          },
                          "multiple": false,
                          "required": false
                      },
                      "type": "select"
                  },
                  {
                      "code": "isCommitteeMember",
                      "label": "Membre d'un comité",
                      "options": {
                          "choices": [
                              "Non",
                              "Oui"
                          ]
                      },
                      "type": "select"
                  },
                  {
                      "code": "lastMembership",
                      "label": "Dernière cotisation",
                      "options": null,
                      "type": "date_interval"
                  },
                  {
                      "code": "registered",
                      "label": "Inscrit",
                      "options": null,
                      "type": "date_interval"
                  },
                  {
                      "code": "renaissance_membership",
                      "label": "Renaissance",
                      "options": {
                          "choices": {
                              "adherent_or_sympathizer_re": "Adhérent RE ou sympathisant RE",
                              "adherent_re": "Adhérent RE seulement",
                              "sympathizer_re": "Sympathisant RE seulement",
                              "others_adherent": "Ni adhérent RE ni sympathisant RE"
                          }
                      },
                      "type": "select"
                  },
                  {
                      "code": "isCampusRegistered",
                      "label": "Inscrit au campus",
                      "options": {
                          "choices": [
                              "Non",
                              "Oui"
                          ]
                      },
                      "type": "select"
                  }
              ]
          },
          {
              "label": "Élu",
              "color": "#2563EB",
              "filters": [
                  {
                      "code": "declaredMandate",
                      "label": "Déclaration de mandat",
                      "options": {
                          "choices": {
                              "conseiller_arrondissement": "Conseiller d'arrondissement",
                              "conseiller_communautaire": "Conseiller communautaire",
                              "conseiller_departemental": "Conseiller départemental",
                              "conseiller_fde": "Conseiller FDE",
                              "conseiller_municipal": "Conseiller municipal",
                              "conseiller_regional": "Conseiller régional",
                              "conseiller_territorial": "Conseiller territorial",
                              "delegue_consulaire": "Délégué consulaire",
                              "depute": "Député",
                              "depute_europeen": "Député européen",
                              "maire": "Maire",
                              "membre_assemblee_fde": "Membre de l'Assemblée des Français de l'étranger",
                              "president_conseil_communautaire": "Président du Conseil communautaire",
                              "president_conseil_departemental": "Président du Conseil départemental",
                              "president_conseil_regional": "Président du Conseil régional",
                              "senateur": "Sénateur"
                          },
                          "multiple": false
                      },
                      "type": "select"
                  },
                  {
                      "code": "mandateType",
                      "label": "Type de mandat",
                      "options": {
                          "choices": {
                              "conseiller_arrondissement": "Conseiller d'arrondissement",
                              "conseiller_communautaire": "Conseiller communautaire",
                              "conseiller_departemental": "Conseiller départemental",
                              "conseiller_fde": "Conseiller FDE",
                              "conseiller_municipal": "Conseiller municipal",
                              "conseiller_regional": "Conseiller régional",
                              "conseiller_territorial": "Conseiller territorial",
                              "delegue_consulaire": "Délégué consulaire",
                              "depute": "Député",
                              "depute_europeen": "Député européen",
                              "maire": "Maire",
                              "membre_assemblee_fde": "Membre de l'Assemblée des Français de l'étranger",
                              "president_conseil_communautaire": "Président du Conseil communautaire",
                              "president_conseil_departemental": "Président du Conseil départemental",
                              "president_conseil_regional": "Président du Conseil régional",
                              "senateur": "Sénateur"
                          },
                          "multiple": false
                      },
                      "type": "select"
                  }
              ]
          }
      ]
      """
      Examples:
          | user                      | scope                                          |
          | referent@en-marche-dev.fr | referent                                       |
          | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
