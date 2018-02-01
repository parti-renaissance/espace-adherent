Feature:
  As a citizen project host
  When someone follows my citizen project
  I should receive an email from the application

  Background:
    Given the following fixtures are loaded:
      | LoadCitizenProjectData |
@huehuehue
  Scenario: Follow a citizen project
    Given I am logged as "michel.vasseur@example.ch"
    When I send a "POST" request to "/projets-citoyens/le-projet-citoyen-a-paris-8/rejoindre" with "citizen_project.follow" token
    Then print last response
    Then I should have 1 email "CitizenProjectNewFollowerMessage" for "jacques.picard@en-marche.fr" with payload:
    """
    {
      "FromEmail": "contact@en-marche.fr",
      "FromName": "En Marche !",
      "Subject": "Un nouveau membre a rejoint votre projet citoyen !",
      "MJ-TemplateID": "274966",
      "MJ-TemplateLanguage": true,
      "Recipients": [
        {
          "Email": "jacques.picard@en-marche.fr",
          "Name": "Jacques Picard",
          "Vars": {
            "citizen_project_name": "Le projet citoyen \u00e0 Paris 8",
            "follower_firstname": "Michel",
            "follower_lastname": "V.",
            "follower_age": 30,
            "follower_city": "Kilchberg"
          }
        },
        {
          "Email": "gisele-berthoux@caramail.com",
          "Name": "Gisele Berthoux",
          "Vars": {
            "citizen_project_name": "Le projet citoyen \u00e0 Paris 8",
            "follower_firstname": "Michel",
            "follower_lastname": "V.",
            "follower_age": 30,
            "follower_city": "Kilchberg"
          }
        }
      ],
      "Headers": {
        "Reply-To": "michel.vasseur@example.ch"
      }
    }
    """
