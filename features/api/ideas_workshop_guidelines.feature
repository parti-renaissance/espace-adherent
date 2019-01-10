@api
Feature:
  In order to see idea guidelines
  As a user
  I should be able to access API idea guidelines

  Background:
    Given the following fixtures are loaded:
      | LoadIdeaGuidelineData   |
      | LoadIdeaQuestionData    |

  Scenario: As a non logged-in user I can see all enabled idea guidelines
    When I send a "GET" request to "/api/guidelines"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
        {
            "questions": [
                {
                    "id": @integer@,
                    "placeholder": "Expliquez, en maximum 1700 caractères (espaces compris) le problème que vous identifiez et espérez pouvoir remédier.",
                    "position": 1,
                    "category": "Constat",
                    "name": "quel problème souhaitez vous résoudre ?",
                    "required": true
                },
                {
                    "id": @integer@,
                    "placeholder": "Expliquez, en maximum 1700 caractères (espaces compris), comment votre proposition répond concrètement au problème.",
                    "position": 2,
                    "category": "Solution",
                    "name": "quelle réponse votre idée apporte-t-elle ? ",
                    "required": true
                },
                {
                    "id": @integer@,
                    "placeholder": "Expliquez, en maximum 1700 caractères (espaces compris), si et comment cette proposition a été étudiée ou mise en oeuvre en France ou à l’étranger.",
                    "position": 3,
                    "category": "Comparaison",
                    "name": "cette proposition a-t-elle déjà été mise en oeuvre ou étudiée ?",
                    "required": false
                },
                {
                    "id": @integer@,
                    "placeholder": "Expliquez, en maximum 1700 caractères (espaces compris), si cette proposition peut porter préjudice à certains publics (individus, entreprises, associations, ou pays) et comment il est possible d’en limiter les effets.",
                    "position": 4,
                    "category": "Impact",
                    "name": "Cette proposition peut elle avoir des effets négatifs pour certains publics ?",
                    "required": false
                }
            ],
            "position": 1,
            "name": "POUR COMMENCER : QUELLES SONT LES PRINCIPALES CARACTÉRISTIQUES DE VOTRE IDÉE ?"
        },
        {
            "questions": [
                {
                    "id": @integer@,
                    "placeholder": "Expliquez, en maximum 1700 caractères (espaces compris), si votre idée nécessite - ou non - de changer le droit en vigueur. Si oui, idéalement, précisez ce qu’il faudrait changer.",
                    "position": 5,
                    "category": "Droit",
                    "name": "votre idée suppose-t-elle de changer le droit ?",
                    "required": false
                },
                {
                    "id": @integer@,
                    "placeholder": "Expliquez, en maximum 1700 caractères (espaces compris), si votre idée entraîne directement des recettes ou des dépenses pour l’État ou les collectivités locales. Si oui, idéalement, donnez des éléments de chiffrage.",
                    "position": 6,
                    "category": "Budget",
                    "name": "votre idée a-t-elle un impact financier ?",
                    "required": false
                },
                {
                    "id": @integer@,
                    "placeholder": "Expliquez, en maximum 1700 caractères (espaces compris), si votre idée a des effets positifs ou négatifs sur l’environnement. Idéalement, précisez des éléments de réponse pour maximiser ou minimiser (selon les cas) ces effets.",
                    "position": 7,
                    "category": "Environnement",
                    "name": "votre idée a-t-elle un impact écologique ?",
                    "required": false
                },
                {
                    "id": @integer@,
                    "placeholder": "Expliquez, en maximum 1700 caractères (espaces compris), si votre idée a des effets positifs ou négatifs sur l’égalité entre les femmes et les hommes. Idéalement, donnez des éléments pour maximiser ou minimiser (selon les cas) ces effets.",
                    "position": 8,
                    "category": "Égalité femmes-hommes",
                    "name": "votre idée a-t-elle un impact sur l’égalité entre les femmes et les hommes ?",
                    "required": false
                }
            ],
            "position": 3,
            "name": "POUR ALLER PLUS LOIN :"
        }
    ]
    """
