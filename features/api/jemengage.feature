@api
Feature:
  In order to see some Jemengage endpoints
  As a user
  I should be able to access Jemengage API

  Scenario: As a non logged-in user I can get a RGPD text
    Given I send a "GET" request to "/api/je-mengage/je-mengage-rgpd"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "content": "Les données recueillies sur ce formulaire sont traitées par LaREM afin de gérer les informations relatives aux inscriptions aux évènements de LaREM et de permettre à LaREM de vous envoyer des communications politiques. \nSi vous êtes élu(e) ou ancien(ne) élu(e), nous traitons également vos données dans le cadre de l’animation de notre réseau d’élu(e)s et vos données peuvent être transférer à La République Ensemble ou à l’institut de formation Tous Politiques, conformément à la politique de protection des données des élu(e)s. Toutes les informations sont obligatoires, sauf celles marquées \"Optionnel\". L’absence de réponse dans ces champs ne permettra pas à LaREM de traiter votre demande. \nConformément à la règlementation, vous disposez d’un droit d’opposition et d’un droit à la limitation du traitement de données vous concernant, ainsi que d’un droit d’accès, de rectification, de portabilité et d’effacement de vos données. \nVous disposez également de la faculté de donner des directives sur le sort de vos données après votre décès. \nVous pouvez consulter notre Politique de protection des données (si vous êtes élu(e)s, la Politique de protection des données des élu(e)s) et exercer vos droits en nous adressant votre demande accompagnée d’une copie de votre pièce d’identité à l’adresse postale : La République En Marche, 68 rue du Rocher, 75008 Paris, France ou électronique suivante : **mes-donnees@en-marche.fr **ou encore en contactant notre DPO à l’adresse : **dpo@en-marche.fr**."
    }
    """

  Scenario: As a non logged-in user I can get legalities texts
    Given I send a "GET" request to "/api/je-mengage/je-mengage-mentions-legales"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
      {
        "content": "@string@"
      }
    """

  Scenario: As a non logged-in user I can get data protection policy text
    Given I send a "GET" request to "/api/je-mengage/je-mengage-politique-protection-donnees"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
      {
        "content": "@string@"
      }
    """

  Scenario: As a non logged-in user I cannot get a non-existent mentions text
    Given I send a "GET" request to "/api/je-mengage/je-mengage-mentions"
    Then the response status code should be 404
