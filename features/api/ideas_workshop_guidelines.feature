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
    When I send a "GET" request to "/api/ideas-workshop/guidelines"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
        {
            "questions": [
                {
                    "id": @integer@,
                    "placeholder": "Expliquer précisément le problème que vous avez identifié et auquel vous souhaitez répondre. N'hésitez pas à étayer votre constat par des chiffres ou des exemples.",
                    "position": 1,
                    "category": "Constat",
                    "name": "quel problème souhaitez-vous résoudre ?",
                    "required": true
                },
                {
                    "id": @integer@,
                    "placeholder": "Expliquez comment votre proposition répond au problème en étant le plus concret possible. Chaque proposition ne doit comporter qu'une seule solution.",
                    "position": 2,
                    "category": "Solution",
                    "name": "quelle réponse votre idée apporte-t-elle ? ",
                    "required": true
                },
                {
                    "id": @integer@,
                    "placeholder": "Précisez si cette proposition a été mise en œuvre en France ou à l'étranger, s'il s'agissait d'une expérimentation et quels en ont été les résultats.",
                    "position": 3,
                    "category": "Comparaison",
                    "name": "cette proposition a-t-elle déjà été mise en oeuvre ou étudiée ?",
                    "required": false
                },
                {
                    "id": @integer@,
                    "placeholder": "Expliquez si cette proposition peut porter préjudice à certains acteurs (individus, professions, territoires, institutions, entreprises, associations, etc) et comment il est possible d'en limiter les effets.",
                    "position": 4,
                    "category": "Impact",
                    "name": "Cette proposition peut elle avoir des effets négatifs pour certains publics ?",
                    "required": false
                }
            ],
            "position": 1,
            "name": "POUR COMMENCER"
        },
        {
            "questions": [
                {
                    "id": @integer@,
                    "placeholder": "Expliquez si votre proposition nécessite - ou non - de changer le droit en vigueur. Si oui, idéalement, précisez ce qu'il faudrait changer.",
                    "position": 5,
                    "category": "Droit",
                    "name": "votre idée suppose-t-elle de changer le droit ?",
                    "required": false
                },
                {
                    "id": @integer@,
                    "placeholder": "Expliquez si votre proposition entraîne directement des recettes ou des dépenses pour l’État ou les collectivités locales. Si oui, donnez si possible des éléments de chiffrage.",
                    "position": 6,
                    "category": "Budget",
                    "name": "votre idée a-t-elle un impact financier ?",
                    "required": false
                },
                {
                    "id": @integer@,
                    "placeholder": "Expliquez si votre idée a des effets positifs ou négatifs sur l'environnement. Idéalement, précisez comment maximiser ou minimiser ces effets.",
                    "position": 7,
                    "category": "Environnement",
                    "name": "votre idée a-t-elle un impact écologique ?",
                    "required": false
                },
                {
                    "id": @integer@,
                    "placeholder": "L'égalité femmes-hommes est la grande cause du quiquennat. Expliquez si votre proposition a des effets positifs ou négatifs sur ce sujet",
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
