@admin
Feature: Manage citizen projects from admin pannel

  Background:
    Given the following fixtures are loaded:
      | LoadAdminData          |
      | LoadAdherentData       |
      | LoadTurnkeyProjectData |
      | LoadCitizenProjectData |
    When I am logged as "superadmin@en-marche-dev.fr" admin

  Scenario: As an administrator I can approve a citizen project
    When I am on "/projets-citoyens/13003-le-projet-citoyen-a-marseille"
    Then the response status code should be 403

    When I am on "/admin/projets-citoyens/2/approve"
    Then I should be on "/admin/app/citizenproject/list"
    And I should see "Le projet citoyen « Le projet citoyen à Marseille » a été approuvé avec succès."
    And "api_sync" should have 1 message
    And "api_sync" should have message below:
      | routing_key             | body                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
      | citizen_project.updated | {"uuid":"552934ed-2ac6-4a3a-a490-ddc8bf959444","status":"APPROVED","membersCount":1,"name":"Le projet citoyen \u00e0 Marseille","slug":"13003-le-projet-citoyen-a-marseille","category":"Education, culture et citoyennet\u00e9","longitude":5.376076,"latitude":43.325543,"country":"FR","address":"26 Boulevard Louis Guichoux","zipCode":"13003","city":"Marseille 3e","tags":["13"],"subtitle":"Le projet citoyen \u00e0 Marseille !","author":"Benjamin D.","thumbnail":"http:\/\/test.enmarche.code\/assets\/images\/citizen_projects\/default.png"} |
    And I should have 1 email
    And I should have 1 email "CitizenProjectApprovalConfirmationMessage" for "benjyd@aol.com" with payload:
    """
    {
      "FromEmail": "projetscitoyens@en-marche.fr",
      "FromName": "En Marche !",
      "Subject": "Votre projet citoyen a \u00e9t\u00e9 publi\u00e9. \u00c0 vous de jouer !",
      "MJ-TemplateID": "244444",
      "MJ-TemplateLanguage": true,
      "Recipients": [
        {
          "Email": "benjyd@aol.com",
            "Name": "Benjamin Duroc",
            "Vars": {
              "citizen_project_name":"Le projet citoyen à Marseille",
              "target_firstname":"Benjamin"
            }
        }
      ]
    }
    """

    When I am on "/projets-citoyens/13003-le-projet-citoyen-a-marseille"
    And the response status code should be 200

  Scenario: As an administrator I can approve a turnkey project
    When I am on "/projets-citoyens/13003-un-stage-pour-tous-1"
    Then the response status code should be 403

    When I am on "/admin/projets-citoyens/13/approve"
    Then I should be on "/admin/app/citizenproject/list"
    And I should see "Le projet citoyen « Un stage pour tous » a été approuvé avec succès."
    And "api_sync" should have 1 message
    And "api_sync" should have message below:
      | routing_key             | body                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
      | citizen_project.updated | {"uuid":"9f78a464-ddce-45cf-9cc1-3303c50842f2","status":"APPROVED","membersCount":1,"name":"Un stage pour tous","slug":"13003-un-stage-pour-tous-1","category":"Education, culture et citoyennet\u00e9","longitude":5.376733,"latitude":43.325534,"country":"FR","address":"32 Boulevard Louis Guichoux","zipCode":"13003","city":"Marseille 3e","tags":["13"],"subtitle":"Aider les coll\u00e9giens \u00e0 trouver un stage m\u00eame sans r\u00e9seau","author":"Laura D.","thumbnail":"http:\/\/test.enmarche.code\/assets\/images\/citizen_projects\/default.png"} |
    And I should have 1 email
    And I should have 1 email "TurnkeyProjectApprovalConfirmationMessage" for "laura@deloche.com" with payload:
    """
    {
      "FromEmail": "projetscitoyens@en-marche.fr",
      "FromName": "En Marche !",
      "Subject": "Votre projet citoyen a \u00e9t\u00e9 publi\u00e9. \u00c0 vous de jouer !",
      "MJ-TemplateID": "538132",
      "MJ-TemplateLanguage": true,
      "Recipients": [
        {
          "Email": "laura@deloche.com",
            "Name": "Laura Deloche",
            "Vars": {
              "citizen_project_name":"Un stage pour tous",
              "kit_url":"http:\/\/test.enmarche.code\/projets-citoyens\/13003-un-stage-pour-tous-1#citizen-project-files",
              "target_firstname":"Laura"
            }
        }
      ]
    }
    """

    When I am on "/projets-citoyens/13003-un-stage-pour-tous-1"
    And the response status code should be 200

  Scenario: As an administrator I can modify a CP and it will be synchronised with API
    Given I am on "/admin/app/citizenproject/list"
    When I follow "Un stage pour tous"
    Then I should see "Mettre à jour"
    When I fill in "Nom" with "Un autre titre"
    And I press "Mettre à jour"
    Then "api_sync" should have message below:
      | routing_key             | body                                         |
      | citizen_project.updated | @string@.contains('"name":"Un autre titre"') |
