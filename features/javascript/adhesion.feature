@renaissance_user
@javascript
@javascript2
Feature:

    Scenario: I can become adherent
        Given the following fixtures are loaded:
            | LoadSubscriptionTypeData |
            | LoadCommitteeV2Data      |
        When I am on "/adhesion"
        And I fill in the following:
            | membership_request[email] | test@test123.com |
        And I click the "membership_request_consentDataCollect" element
        Then I wait 15 seconds until I see "Nous ne sommes pas parvenus à vérifier l'existence de cette adresse. Vérifiez votre saisie, elle peut contenir une erreur. Si elle est correcte, ignorez cette alerte."
        When I fill in the following:
            | membership_request[email] | test@test.com |
        And I wait 2 seconds
        And I press "J'adhère"
        And I wait 3 seconds
        And I click the "membership_request_civility_0_label" element
        And I click the ".aucomplete-fields-toggle" selector
        And I click the "membership_request_nationality_select_widget" element
        And I click the "#membership_request_nationality_select_widget .re-input-option--selected" selector
        And I fill in the following:
            | membership_request[firstName]                  | Marine         |
            | membership_request[lastName]                   | Dupont         |
            | membership_request[address][address]           | 9 rue du lycée |
            | membership_request[address][additionalAddress] | app 9          |
            | membership_request[address][postalCode]        | 06000          |
            | membership_request[address][cityName]          | Nice           |
        And I click the "membership_request_address_country_select_widget" element
        And I click the "#membership_request_address_country_select_widget .re-input-option--selected" selector
        And I press "Suivant"
        And I wait 1 second
        And I click the "membership_request_exclusiveMembership_1" element
        Then I wait 3 seconds until I see "J’appartiens à un autre parti politique"
        When I click the "membership_request_exclusiveMembership_0" element
        Then I should not see "J’appartiens à un autre parti politique"
        When I click the "membership_request_isPhysicalPerson" element
        Then I click the "#step_3 .re-button" selector
        And I wait 5 seconds
        And I scroll element "#step_4 #amount_3_label" into view
        And I click the "#step_4 #amount_4_label" selector
        And I should see "Je confirme être étudiant, une personne bénéficiant des minima sociaux ou sans emploi"
        And I click the "#step_4 #amount_5_label" selector
        And I should not see "Je confirme être étudiant, une personne bénéficiant des minima sociaux ou sans emploi"
        And I press "Je cotise pour 50 €"

        # Step 5 : payment
        Then I should be on "https://preprod-tpeweb.paybox.com/cgi/FramepagepaiementRWD.cgi" wait otherwise
        And I wait until I see "50.00 EUR"
        When I fill in the following:
            | CVVX         | 123              |
            | NUMERO_CARTE | 1111222233334444 |
        And I wait 2 seconds
        And I click the "#pbx-card-button-choice1" selector
        And I select "12" from "MOIS_VALIDITE"
        And I select "35" from "AN_VALIDITE"
        And I press "Valider"
        Then I should be on "https://preprod-tpeweb.paybox.com/cgi/MYtraitetransaction.cgi" wait otherwise
        And I wait 5 second until I see "PAIEMENT ACCEPTÉ"
        And I wait 2 seconds
        And I click the ".textCenter:last-child a" selector
        And I should be on "/paiement" wait otherwise
        When I simulate IPN call with "00000" code for the last donation of "test@test.com"
        Then I should be on "/adhesion/confirmation-email" wait otherwise
        And I should see "Votre paiement a bien été validé !"
        And I should see "Confirmer votre adresse email"
        And I should see "test@test.com"
        And I should have 1 email "AdhesionCodeValidationMessage" for "test@test.com" with payload:
            """
            {
                "template_name": "adhesion-code-validation",
                "template_content": [],
                "message": {
                    "subject": "Confirmez votre adresse email",
                    "from_email": "ne-pas-repondre@parti-renaissance.fr",
                    "html": null,
                    "global_merge_vars": [
                        {
                            "name": "first_name",
                            "content": "Marine"
                        },
                        {
                            "name": "code",
                            "content": "@string@"
                        },
                        {
                            "name": "magic_link",
                            "content": "http://test.renaissance.code/connexion-avec-un-lien-magique?user=test@test.com&expires=@number@&hash=@string@&_failure_path=%2Fconnexion"
                        }
                    ],
                    "from_name": "Renaissance",
                    "to": [
                        {
                            "email": "test@test.com",
                            "type": "to",
                            "name": "Marine Dupont"
                        }
                    ]
                }
            }
            """

        # Step 6 : email confirmation
        Given I am on "/adhesion/confirmation-email"
        Then I should see "Confirmer votre adresse email"
        And I should see "test@test.com"
        When I fill in the following:
            | code | 1234 |
        And I press "Valider"
        Then I should see "Le code d'activation est erroné."
        When I fill activation code from email
        And I press "Valider"
        And I wait 3 seconds
        Then I should be on "/adhesion/creation-mot-de-passe" wait otherwise
        And I should see "Votre adresse email a bien été validée !"

        # Step 7 : password creation
        And I should see "Nouveau mot de passe"
        When I fill in the following:
            | adherent_reset_password[password][first]  | test1234 |
            | adherent_reset_password[password][second] | 1234test |
        And I press "Valider"
        Then I should see "Les deux mots de passe doivent correspondre."
        When I fill in the following:
            | adherent_reset_password[password][first]  | test123 |
            | adherent_reset_password[password][second] | test123 |
        And I press "Valider"
        Then I should see "Votre mot de passe doit comporter au moins 8 caractères."
        When I fill in the following:
            | adherent_reset_password[password][first]  | test1234 |
            | adherent_reset_password[password][second] | test1234 |
        And I press "Valider"
        Then I should see "Votre mot de passe a bien été sauvegardé !"

        # Step 8 : further information
        And I should not see "En déclarant vos mandats ici, nous préviendrons votre Président d’Assemblée départementale qui pourra, le cas échéant, vous inclure dans les élus de Renaissance."
        When I click the "#is-elu" selector
        Then I should see "En déclarant vos mandats ici, nous préviendrons votre Président d’Assemblée départementale qui pourra, le cas échéant, vous inclure dans les élus de Renaissance."
        And I should see "Député"
        And I should see "Sénateur"
        And I should see "Maire"
        When I click the "#is-elu" selector
        Then I should not see "Député"
        And I should not see "Sénateur"
        And I should not see "Maire"
        When I press "Continuer"
        Then I should see "Ce champ est requis" 3 times
        And I click the "adhesion_further_information_birthdate_day_select_widget" element
        And I click the "#adhesion_further_information_birthdate_day_select_widget .re-input-option" selector
        And I click the "adhesion_further_information_birthdate_month_select_widget" element
        And I click the "#adhesion_further_information_birthdate_month_select_widget .re-input-option" selector
        And I click the "adhesion_further_information_birthdate_year_select_widget" element
        And I click the "#adhesion_further_information_birthdate_year_select_widget .re-input-option" selector
        Then I should see "Vous avez moins de 35 ans"
        When I click the "input[name='adhesion_further_information[acceptSmsNotification]']" selector
        And I press "Continuer"
        Then I should see "Vous avez accepté de recevoir des informations du parti par SMS ou téléphone, cependant, vous n'avez pas précisé votre numéro de téléphone."
        When I fill in the following:
            | adhesion_further_information[phone][number] | 0123456789 |
        And I press "Continuer"

        # Step 9 : member card
        Then I should be on "/adhesion/carte-adherent" wait otherwise
        And I should see "Carte d'adhérant"
        When I click the ".aucomplete-fields-toggle" selector
        And I fill in the following:
            | member_card[address][address]           | 92 bld Victor Hugo |
            | member_card[address][additionalAddress] |                    |
            | member_card[address][postalCode]        | 92110              |
            | member_card[address][cityName]          | Clichy             |
        When I press "Recevoir ma carte"

        # Step 10 : communication
        Then I should be on "/adhesion/rappel-communication"
        And I should see "Attention, vous ne recevrez jamais aucune communication par email de notre part (hors email statutaire)"
        When I click the "input[name='adhesion_communication[acceptEmail]']" selector
        And I press "Continuer"

        # Step 11 : committee
        Then I should be on "/adhesion/comite-local" wait otherwise
        And I should see "Comité local"
        And I should see "Comité : Second Comité des 3 communes"
        And I should see "Responsable : Adherent 56 Fa56ke"
        When I press "Changer de comité"
        Then I should see "Choisissez un nouveau comité près de chez vous"
        And I should see "Comité : Comité des 3 communes"
        And I should see "Responsable : Adherent 55 Fa55ke"
        When I press "Rejoindre"
        Then I should not see "Choisissez un nouveau comité près de chez vous"
        And I should see "Comité : Comité des 3 communes"
        And I should see "Responsable : Adherent 55 Fa55ke"
        When I press "Continuer"

        # Finish step
        Then I should be on "/adhesion/felicitations" wait otherwise
        And I should see "Vous êtes désormais adhérent, félicitations !"
        When I go to "/espace-adherent"
        Then I should see "Adhérent"

    Scenario: I can pay for new year as adherent RE
        Given the following fixtures are loaded:
            | LoadSubscriptionTypeData |
            | LoadDonationData         |
            | LoadAdherentData         |
        When I am on "/adhesion"
        And I fill in the following:
            | membership_request[email] | renaissance-user-4@en-marche-dev.fr |
        And I click the "membership_request_consentDataCollect" element
        And I wait 15 seconds until I see "Nous ne sommes pas parvenus à vérifier l'existence de cette adresse. Vérifiez votre saisie, elle peut contenir une erreur. Si elle est correcte, ignorez cette alerte."
        And I press "J'adhère"
        Then I wait 5 seconds until I see "Un email de confirmation vient d’être envoyé à votre adresse email. Cliquez sur le lien de validation qu’il contient pour continuer votre adhésion."
        And I should have 1 email "AdhesionAlreadyAdherentMessage" for "renaissance-user-4@en-marche-dev.fr" with payload:
            """
            {
                "template_name": "adhesion-already-adherent",
                "template_content": [],
                "message": {
                    "subject": "Vous êtes déjà adhérent",
                    "from_email": "ne-pas-repondre@parti-renaissance.fr",
                    "html": null,
                    "global_merge_vars": [
                        {
                            "name": "first_name",
                            "content": "Louis"
                        },
                        {
                            "name": "this_year",
                            "content": "@number@"
                        },
                        {
                            "name": "magic_link",
                            "content": "http://test.renaissance.code/connexion-avec-un-lien-magique?user=renaissance-user-4@en-marche-dev.fr&expires=@number@&hash=@string@"
                        },
                        {
                            "name": "forgot_password_link",
                            "content": "http://test.renaissance.code/mot-de-passe-oublie"
                        },
                        {
                            "name": "cotisation_link",
                            "content": "http://test.renaissance.code/connexion-avec-un-lien-magique?user=renaissance-user-4@en-marche-dev.fr&expires=@number@&hash=@string@&_target_path=/adhesion"
                        }
                    ],
                    "from_name": "Renaissance",
                    "to": [
                        {
                            "email": "renaissance-user-4@en-marche-dev.fr",
                            "type": "to",
                            "name": "Louis Roche"
                        }
                    ]
                }
            }
            """
        When I click on the email link "cotisation_link"
        Then I should be on "/adhesion" wait otherwise
        When I click the ".aucomplete-fields-toggle" selector
        And I fill in the following:
            | membership_request[address][address]    | 9 rue du lycée |
            | membership_request[address][postalCode] | 06000          |
            | membership_request[address][cityName]   | Nice           |
        When I press "Suivant"
        And I wait 3 seconds
        And I should see "Cotisation pour l’année 2022"
        And I should see "Cotisation pour l’année 2023"
        And I should see "Cotisation pour l’année 2024"
        When I press "Je cotise pour 120 €"

        # Step 5 : payment
        Then I should be on "https://preprod-tpeweb.paybox.com/cgi/FramepagepaiementRWD.cgi" wait otherwise
        And I wait until I see "120.00 EUR"
        When I fill in the following:
            | CVVX         | 123              |
            | NUMERO_CARTE | 1111222233334444 |
        And I wait 2 seconds
        And I click the "#pbx-card-button-choice1" selector
        And I select "12" from "MOIS_VALIDITE"
        And I select "35" from "AN_VALIDITE"
        And I press "Valider"
        Then I should be on "https://preprod-tpeweb.paybox.com/cgi/MYtraitetransaction.cgi" wait otherwise
        And I wait 5 second until I see "PAIEMENT ACCEPTÉ"
        And I wait 2 seconds
        And I click the ".textCenter:last-child a" selector
        And I should be on "/paiement" wait otherwise
        When I simulate IPN call with "00000" code for the last donation of "renaissance-user-4@en-marche-dev.fr"
        Then I should be on "/adhesion/felicitations" wait otherwise
        And I should see "Vous êtes à jour de cotisations, félicitations !"
        When I go to "/espace-adherent"
        Then I should see "Adhérent"

    Scenario: I can become adherent from EM account
        Given the following fixtures are loaded:
            | LoadSubscriptionTypeData |
            | LoadAdherentData         |
            | LoadCommitteeV2Data      |
        When I am on "/adhesion"
        And I fill in the following:
            | membership_request[email] | carl999@example.fr |
        And I click the "membership_request_consentDataCollect" element
        And I wait 5 seconds
        And I press "J'adhère"
        Then I wait 5 seconds until I see "Un email de confirmation vient d’être envoyé à votre adresse email. Cliquez sur le lien de validation qu’il contient pour continuer votre adhésion."
        And I should have 1 email "AdhesionAlreadySympathizerMessage" for "carl999@example.fr" with payload:
            """
            {
                "template_name": "adhesion-already-sympathizer",
                "template_content": [],
                "message": {
                    "subject": "Confirmez votre adresse email",
                    "from_email": "ne-pas-repondre@parti-renaissance.fr",
                    "html": null,
                    "global_merge_vars": [
                        {
                            "name": "first_name",
                            "content": "Carl"
                        },
                        {
                            "name": "created_at",
                            "content": "@string@"
                        },
                        {
                            "name": "magic_link",
                            "content": "http://test.renaissance.code/connexion-avec-un-lien-magique?user=carl999@example.fr&expires=@number@&hash=@string@"
                        }
                    ],
                    "from_name": "Renaissance",
                    "to": [
                        {
                            "email": "carl999@example.fr",
                            "type": "to",
                            "name": "Carl Mirabeau"
                        }
                    ]
                }
            }
            """
        When I click on the email link "magic_link"
        Then I should be on "/adhesion" wait otherwise
        When I click the ".aucomplete-fields-toggle" selector
        And I fill in the following:
            | membership_request[address][address]    | 9 rue du lycée |
            | membership_request[address][postalCode] | 06000          |
            | membership_request[address][cityName]   | Nice           |
        And I press "Suivant"
        And I wait 3 seconds
        And I scroll element "#step_4 #amount_3_label" into view
        And I click the "#step_4 #amount_4_label" selector
        And I should see "Je confirme être étudiant, une personne bénéficiant des minima sociaux ou sans emploi"
        And I click the "#step_4 #amount_5_label" selector
        And I should not see "Je confirme être étudiant, une personne bénéficiant des minima sociaux ou sans emploi"
        And I press "Je cotise pour 50 €"

        # Step 5 : payment
        Then I should be on "https://preprod-tpeweb.paybox.com/cgi/FramepagepaiementRWD.cgi" wait otherwise
        And I wait until I see "50.00 EUR"
        When I fill in the following:
            | CVVX         | 123              |
            | NUMERO_CARTE | 1111222233334444 |
        And I wait 2 seconds
        And I click the "#pbx-card-button-choice1" selector
        And I select "12" from "MOIS_VALIDITE"
        And I select "35" from "AN_VALIDITE"
        And I press "Valider"
        Then I should be on "https://preprod-tpeweb.paybox.com/cgi/MYtraitetransaction.cgi" wait otherwise
        And I wait 5 second until I see "PAIEMENT ACCEPTÉ"
        And I wait 2 seconds
        And I click the ".textCenter:last-child a" selector
        And I should be on "/paiement" wait otherwise
        When I simulate IPN call with "00000" code for the last donation of "carl999@example.fr"
        Then I should be on "/adhesion/felicitations" wait otherwise
        And I should see "Vous êtes désormais adhérent, félicitations !"
        When I go to "/espace-adherent"
        Then I should see "Adhérent"
