Feature:
  As an adherent I should be able to vote/unvote in followed committees

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData           |
      | LoadCommitteeCandidacyData |

  @javascript
  Scenario: I can show a list of followed committees, then I can remove my candidacy and change the vote committee
    Given I am logged as "assesseur@en-marche-dev.fr"
    And I am on "/espace-adherent/mes-comites"
    And I press "OK"
    Then I should see 4 ".adherent__activity--committee" elements
    And I should see "RETIRER LA CANDIDATURE" 1 times

    When I click the ".btn.btn--ghosting--pink.b__nudge--right-small" selector
    And I wait 3 second until I see "Êtes-vous sûr de vouloir retirer votre candidature ?"
    And I click the ".modal-content .btn.btn--blue" selector
    Then I should be on "/espace-adherent/mes-comites"
    And I should see 0 ".btn.btn--ghosting--pink.b__nudge--right-small" elements
    And I should have 2 emails
    And I should have 1 email "CommitteeCandidacyRemovedConfirmationMessage" for "assesseur@en-marche-dev.fr" with payload:
    """
    {
        "template_name": "committee-candidacy-removed-confirmation",
        "template_content": [],
        "message": {
            "subject": "[Désignations] Votre candidature a été annulée",
            "from_email": "contact@en-marche.fr",
            "global_merge_vars": [
                {
                    "name": "first_name",
                    "content": "Bob"
                },
                {
                    "name": "committee_name",
                    "content": "En Marche - Comit\u00e9 de Rouen"
                },
                {
                    "name": "candidacy_end_date",
                    "content": "lundi 22 juin 2020, 00h00"
                },
                {
                    "name": "vote_start_date",
                    "content": "lundi 22 juin 2020, 08h00"
                },
                {
                    "name": "vote_end_date",
                    "content": "dimanche 5 juillet 2020, 20h00"
                },
                {
                    "name": "committee_url",
                    "content": "http:\/\/test.enmarche.code\/comites\/en-marche-comite-de-rouen?anonymous_authentication_intention=\/connexion#committee-toggle-candidacy"
                }
            ],
            "from_name": "La R\u00e9publique En Marche !",
            "to": [
                {
                    "email": "assesseur@en-marche-dev.fr",
                    "type": "to",
                    "name": "Bob Assesseur"
                }
            ]
        }
    }
    """
    And I should have 1 email "CommitteeRemovedCandidacyNotificationMessage" for "laura@deloche.com" with payload:
    """
    {
        "template_name": "committee-removed-candidacy-notification",
        "template_content": [],
        "message": {
            "subject": "[Désignations] Une candidature a été retirée",
            "from_email": "contact@en-marche.fr",
            "global_merge_vars": [
                {
                    "name": "supervisor_first_name",
                    "content": "Laura"
                },
                {
                    "name": "candidate_civility",
                    "content": "M."
                },
                {
                    "name": "candidate_first_name",
                    "content": "Bob"
                },
                {
                    "name": "candidate_last_name",
                    "content": "Assesseur"
                },
                {
                    "name": "vote_start_date",
                    "content": "lundi 22 juin 2020, 08h00"
                },
                {
                    "name": "vote_end_date",
                    "content": "dimanche 5 juillet 2020, 20h00"
                },
                {
                    "name": "committee_url",
                    "content": "http:\/\/test.enmarche.code\/comites\/en-marche-comite-de-rouen"
                }
            ],
            "from_name": "La R\u00e9publique En Marche !",
            "to": [
                {
                    "email": "laura@deloche.com",
                    "type": "to",
                    "name": "Laura Deloche"
                }
            ]
        }
    }
    """

    When I am on "/comites/en-marche-comite-de-rouen"
    Then I should see "JE CANDIDATE"

    When I am on "/comites/en-marche-comite-de-evry"
    Then I should see "Vous ne pouvez candidater que dans le comité où vous avez choisi de voter."

    When I am on "/espace-adherent/mes-comites"
    And I click the ".adherent__activity--committee .switch" selector
    Then I should see "Changement du comité de vote"
    And I should see "Vous êtes sur le point de changer votre comité de vote. Vous ne pourrez plus voter dans le comité En Marche - Comité de Rouen, êtes-vous sûr de vouloir maintenant voter dans le comité En Marche - Comité de Évry ?"
    And I should see "CONFIRMER"

    When I click the "button.btn.btn--blue" selector
    Then I wait 3 second until I see "En Marche - Comité de Évry"

    When I am on "/comites/en-marche-comite-de-rouen"
    Then I should see "Vous ne pouvez candidater que dans le comité où vous avez choisi de voter."

    Given I am on "/comites/en-marche-comite-de-evry"
    Then I should see "JE CANDIDATE"
    When I follow "committee-toggle-candidacy"
    Then I should be on "/comites/en-marche-comite-de-evry/candidater"
    When I press "Passer cette étape"
    Then I should be on "/comites/en-marche-comite-de-evry"
    And I should see "Votre candidature a bien été enregistrée"
    And I should have 1 email "CommitteeCandidacyCreatedConfirmationMessage" for "assesseur@en-marche-dev.fr" with payload:
    """
    {
        "template_name": "committee-candidacy-created-confirmation",
        "template_content": [],
        "message": {
            "subject": "[Désignations] Vous êtes maintenant candidat(e) !",
            "from_email": "contact@en-marche.fr",
            "global_merge_vars": [
                {
                    "name": "first_name",
                    "content": "Bob"
                },
                {
                    "name": "committee_name",
                    "content": "En Marche - Comité de Évry"
                },
                {
                    "name": "candidacy_end_date",
                    "content": "lundi 22 juin 2020, 00h00"
                },
                {
                    "name": "vote_start_date",
                    "content": "lundi 22 juin 2020, 08h00"
                },
                {
                    "name": "vote_end_date",
                    "content": "dimanche 5 juillet 2020, 20h00"
                },
                {
                    "name": "cancel_candidacy_url",
                    "content": "http:\/\/test.enmarche.code\/comites\/en-marche-comite-de-evry?remove-candidacy=1&anonymous_authentication_intention=\/connexion"
                }
            ],
            "from_name": "La R\u00e9publique En Marche !",
            "to": [
                {
                    "email": "assesseur@en-marche-dev.fr",
                    "type": "to",
                    "name": "Bob Assesseur"
                }
            ]
        }
    }
    """
    And I should have 1 email "CommitteeNewCandidacyNotificationMessage" for "francis.brioul@yahoo.com" with payload:
    """
    {
        "template_name": "committee-new-candidacy-notification",
        "template_content": [],
        "message": {
            "subject": "[Désignations] Une nouvelle candidature a été déposée",
            "from_email": "contact@en-marche.fr",
            "global_merge_vars": [
                {
                    "name": "supervisor_first_name",
                    "content": "Francis"
                },
                {
                    "name": "candidate_civility",
                    "content": "M."
                },
                {
                    "name": "candidate_first_name",
                    "content": "Bob"
                },
                {
                    "name": "candidate_last_name",
                    "content": "Assesseur"
                },
                {
                    "name": "vote_start_date",
                    "content": "lundi 22 juin 2020, 08h00"
                },
                {
                    "name": "vote_end_date",
                    "content": "dimanche 5 juillet 2020, 20h00"
                },
                {
                    "name": "committee_url",
                    "content": "http:\/\/test.enmarche.code\/comites\/en-marche-comite-de-evry"
                }
            ],
            "from_name": "La R\u00e9publique En Marche !",
            "to": [
                {
                    "email": "francis.brioul@yahoo.com",
                    "type": "to",
                    "name": "Francis Brioul"
                }
            ]
        }
    }
    """

  @javascript
  Scenario: As member of the committee, I can see its candidacies modal
    Given I am logged as "assesseur@en-marche-dev.fr"
    When I am on "/comites/en-marche-comite-de-rouen"
    And I press "OK"
    Then I should see "Retirer ma candidature"
    And I should see "Consulter la liste des candidats"

    When I click the "candidacies-list-modal--trigger" element
    Then I wait 5 second until I see "Liste des candidat(e)s :"
    And I should see "Bob Assesseur"
