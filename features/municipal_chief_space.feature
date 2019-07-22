Feature:
  As a municipal chief
  In order to see application request of my managed area
  I should be able to access my municipal chief space

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                             |
      | LoadApplicationRequestRunningMateRequestData |
      | LoadApplicationRequestVolunteerRequestData   |

  @javascript
  Scenario: I can see running mate request for the zones I manage, I can see the detail and I can add tags
    Given I am logged as "municipal-chief@en-marche-dev.fr"
    When I am on "/espace-municipales-2020/candidature-colistiers"
    Then I should see "Vous gérez : Lille, Oignies, Seclin"
    And I should see 4 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "Camphin-en-Carembault, Lille"
    And I should see "Camphin-en-Pévèle, Lille, Mons-en-Baroeul"
    And I should see "Mons-en-Pévèle, Seclin"
    And I should see "Seclin"

    When I hover "table.datagrid__table-manager tbody tr td div.action-menu-oval"
    And I follow "Plus d'infos"
    Then I should see "⟵ Retour"
    And I should see "Quelle est votre profession ?"
    And I should see "Êtes-vous engagé(e) dans une/des association(s) locale(s) ?"
    And I should see "Détails"
    And I should see "Avez-vous déjà eu un engagement militant ?"
    And I should see "Détails"
    And I should see "Avez-vous déjà exercé un mandat ?"
    And I should see "Détails"
    And I should see "Quel projet pour votre commune souhaiteriez-vous contribuer à porter ?"
    And I should see "Quel sont les atouts de votre parcours professionnel ?"

    When I follow "⟵ Retour"
    Then I should be on "/espace-municipales-2020/candidature-colistiers"

    When I wait 5 seconds until I see "TAGS DE CANDIDATURE"
    And I hover "table.datagrid__table-manager tbody tr td div.action-menu-oval"
    And I follow "Taguer"
    Then I should see "Tags de candidature"

    When I select "4" from "application_request_tags_tags"
    And I press "Enregistrer"
    Then I wait 5 seconds until I see "TAGS DE CANDIDATURE"
    And I should see "Tag 4" in the "table.datagrid__table-manager tbody tr td.municipal-candidate-tags" element

  @javascript
  Scenario Outline: I can see running mate request for the zones I manage, I can see the detail and I can add tags
    Given I am logged as "<user>"
    When I am on "/espace-municipales-2020/candidature-colistiers"
    Then I should see "<managed-cities>"
    And I should see 2 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "<cities-tr-1>"
    And I should see "<cities-tr-2>"
    And I should not see "<missing-city>"
    And I should not see "<missing-tag>"

    When I am on "/espace-municipales-2020/candidature-colistiers/<forbidden-uuid>"
    Then I should see "403"

    When I am on "/espace-municipales-2020/candidature-colistiers/<forbidden-uuid>/editer-tags"
    Then I should see "403"

    Examples:
      | user                               | managed-cities                                        | cities-tr-1                               | cities-tr-2                               | missing-city           | missing-tag | forbidden-uuid                       |
      | municipal-chief-2@en-marche-dev.fr | Vous gérez : Camphin-en-Carembault, Camphin-en-Pévèle | Camphin-en-Carembault, Lille              | Camphin-en-Pévèle, Lille, Mons-en-Baroeul | Seclin                 | Tag 4       | b1f336d8-5a33-4e79-bf02-ae03d1101093 |
      | municipal-chief-3@en-marche-dev.fr | Vous gérez : Mons-en-Baroeul, Mons-en-Pévèle          | Camphin-en-Pévèle, Lille, Mons-en-Baroeul | Mons-en-Pévèle, Seclin                    | Camphin-en-Carembault  | Tag 1       | 23db4b50-dbe3-4b7f-9bd8-f3eaba8367de |

  @javascript
  Scenario: I can see volunteer request for the zones I manage, I can see the detail and I can add tags
    Given I am logged as "municipal-chief@en-marche-dev.fr"
    When I am on "/espace-municipales-2020/candidature-benevoles"
    Then I should see "Vous gérez : Lille, Oignies, Seclin"
    And I should see 4 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "Camphin-en-Carembault, Lille"
    And I should see "Mons-en-Pévèle, Seclin"
    And I should see "Seclin"

    When I hover "table.datagrid__table-manager tbody tr td div.action-menu-oval"
    And I follow "Plus d'infos"
    Then I should see "⟵ Retour"
    And I should see "Vos thématique(s) de prédilection Sécurité Environnement"
    And I should see "Détails thématiques “Autres” Thanos destruction"
    And I should see "Disposez-vous de compétences techniques spécifiques ? Communication Management Animation Autre"
    And I should see "Avez-vous déjà participé à une campagne ? Non"
    And I should see "Détails"
    And I should see "Souhaitez-vous nous faire part de vos engagements associatifs et/ou militants ? Non"
    And I should see "Détails"

    When I follow "⟵ Retour"
    Then I should be on "/espace-municipales-2020/candidature-benevoles"

    When I wait 5 seconds until I see "TAGS DE CANDIDATURE"
    And I hover "table.datagrid__table-manager tbody tr td div.action-menu-oval"
    And I follow "Taguer"
    Then I should see "Tags de candidature"

    When I select "4" from "application_request_tags_tags"
    And I press "Enregistrer"
    Then I wait 5 seconds until I see "TAGS DE CANDIDATURE"
    And I should see "Tag 4" in the "table.datagrid__table-manager tbody tr td.municipal-candidate-tags" element

  @javascript
  Scenario Outline: I can see volunteer request for the zones I manage, I can see the detail and I can add tags
    Given I am logged as "<user>"
    When I am on "/espace-municipales-2020/candidature-benevoles"
    Then I should see "<managed-cities>"
    And I should see 2 "tr" in the 1st "table.datagrid__table-manager tbody"
    And I should see "<cities-tr-1>"
    And I should see "<cities-tr-2>"
    And I should not see "<missing-city>"
    And I should not see "<missing-tag>"

    When I am on "/espace-municipales-2020/candidature-benevoles/<forbidden-uuid>"
    Then I should see "403"

    When I am on "/espace-municipales-2020/candidature-benevoles/<forbidden-uuid>/editer-tags"
    Then I should see "403"

    Examples:
      | user                               | managed-cities                                        | cities-tr-1                               | cities-tr-2                               | missing-city           | missing-tag | forbidden-uuid                       |
      | municipal-chief-2@en-marche-dev.fr | Vous gérez : Camphin-en-Carembault, Camphin-en-Pévèle | Camphin-en-Carembault, Lille              | Camphin-en-Pévèle, Lille, Mons-en-Baroeul | Seclin                 | Tag 4       | 5ca5fc5c-b6f4-4edf-bb8e-111aa9222696 |
      | municipal-chief-3@en-marche-dev.fr | Vous gérez : Mons-en-Baroeul, Mons-en-Pévèle          | Camphin-en-Pévèle, Lille, Mons-en-Baroeul | Mons-en-Pévèle, Seclin                    | Camphin-en-Carembault  | Tag 1       | 06d61c85-929a-4152-b46c-b94b6883b8d6 |

  @javascript
  Scenario Outline: I can define application request as added to my team
    Given I am logged as "municipal-chief-3@en-marche-dev.fr"
    When I am on "<url>"
    And I wait 5 seconds until I see "TAGS DE CANDIDATURE"
    And I scroll element "table.datagrid__table-manager" into view
    And I hover "table.datagrid__table-manager tbody tr td div.action-menu-oval"
    And I follow "Ajouter à mon équipe"
    Then I wait 5 seconds until I see "TAGS DE CANDIDATURE"
    And I hover "table.datagrid__table-manager tbody tr td div.action-menu-oval"
    And I should see "Retirer de mon équipe"

    When I am on "/deconnexion"
    And I am logged as "municipal-chief@en-marche-dev.fr"
    And I am on "<url>"
    And I wait 5 seconds until I see "TAGS DE CANDIDATURE"
    And I press "OK"
    And I hover "table.datagrid__table-manager tbody tr td div.action-menu-oval"
    Then I should see "Déjà pris dans une autre ville" in the "table.datagrid__table-manager tbody tr" element

    Examples:
      | url                                           |
      | /espace-municipales-2020/candidature-benevoles  |
      | /espace-municipales-2020/candidature-colistiers |

  Scenario Outline: I list adherent living in the cities I manage
    Given I am logged as "<user>"
    And I am on "/espace-municipales-2020/adherents"
    And I wait 10 seconds until I see "Identité"
    Then I should see "<shouldSee>"
    And I should not see "<shouldNotSee>"

    Examples:
      | user                               | shouldSee         | shouldNotSee      |
      | municipal-chief@en-marche-dev.fr   | Dusse Jean-Claude | Morin Bernard     |
      | municipal-chief-3@en-marche-dev.fr | Morin Bernard     | Dusse Jean-Claude |
