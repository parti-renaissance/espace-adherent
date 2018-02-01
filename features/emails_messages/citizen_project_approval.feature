Feature:
  As an admin
  When I approve a citizen project
  The host should receive an email from the application

  Background:
    Given the following fixtures are loaded:
      | LoadAdminData          |
      | LoadCitizenProjectData |

  Scenario: Approve a citizen project
    Given I am logged as "admin@en-marche-dev.fr" admin
    And I am on "/admin/app/citizenproject/list"
    And I follow "Approuver"
    Then I should have 1 email "CitizenProjectApprovalConfirmationMessage" for "martine.lindt@gmail.com" with payload:
    """
    {
      "FromEmail": "projetscitoyens@en-marche.fr",
      "FromName": "En Marche !",
      "Subject": "Votre projet citoyen a \u00e9t\u00e9 publi\u00e9. \u00c0 vous de jouer !",
      "MJ-TemplateID": "244444",
      "MJ-TemplateLanguage": true,
      "Recipients": [
        {
          "Email": "martine.lindt@gmail.com",
          "Name": "Martine Lindt",
          "Vars": {
            "citizen_project_name": "Projet citoyen \u00e0 Berlin",
            "target_firstname": "Martine"
          }
        }
      ]
    }
    """
