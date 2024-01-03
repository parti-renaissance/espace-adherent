@renaissance
@javascript
@javascript2
Feature:
    Background:
        Given the following fixtures are loaded:
            | LoadSubscriptionTypeData |

    Scenario: I can become adherent
        When I am on "/adhesion"
        Then the element "#step_1 .re-button" should be disabled
        When I fill in the following:
            | membership_request[email] | test@test123.com |
        And I click the "membership_request_consentDataCollect" element
        Then I wait 15 seconds until I see "Nous ne sommes pas parvenus à vérifier l'existence de l'adresse « test@test123.com ». Vérifiez votre saisie avant de continuer."
        And the element "#step_1 .re-button" should be enabled
        When I fill in the following:
            | membership_request[email] | test@test.com |
        And I press "J'adhère"
        And I wait 3 seconds
        Then the element "#step_2 .re-button" should be disabled
        When I click the "membership_request_civility_0_label" element
        And I click the ".aucomplete-fields-toggle" selector
        And I click the "membership_request_nationality_select_widget" element
        And I click the "#membership_request_nationality_select_widget .re-input-option--selected" selector
        And I fill in the following:
            | membership_request[firstName]             | Marine |
            | membership_request[lastName]              | Dupont |
            | membership_request[address][address]      | 92 bld Victor Hugo |
            | membership_request[address][postalCode]   | 92110 |
            | membership_request[address][cityName]     | Clichy |
        And I click the "membership_request_address_country_select_widget" element
        And I click the "#membership_request_address_country_select_widget .re-input-option--selected" selector
        Then the element "#step_2 .re-button" should be enabled
        When I press "Suivant"
        Then the element "#step_3 .re-button" should be disabled
        And I wait 1 second
        When I click the "membership_request_exclusiveMembership_1" element
        Then I wait 3 seconds until I see "J’appartiens à un autre parti politique"
        When I click the "membership_request_exclusiveMembership_0" element
        Then I should not see "J’appartiens à un autre parti politique"
        When I click the "membership_request_allowNotifications" element
        Then the element "#step_3 .re-button" should be disabled
        When I click the "membership_request_isPhysicalPerson" element
        Then the element "#step_3 .re-button" should be enabled
        And I click the "#step_3 .re-button" selector
        And I wait 5 seconds
        And I click the "#step_4 #amount_4_label" selector
        And I should see "Je confirme être étudiant, une personne bénéficiant des minima sociaux ou sans emploi"
        And I click the "#step_4 #amount_5_label" selector
        And I should not see "Je confirme être étudiant, une personne bénéficiant des minima sociaux ou sans emploi"
        And I press "J'adhère pour 60€"

        # Step 5 : payment
        Then I should be on "https://preprod-tpeweb.paybox.com/cgi/FramepagepaiementRWD.cgi" wait otherwise
        And I should see "Numéro de carte"
        When I fill in the following:
            | NUMERO_CARTE | 1111222233334444 |
            | CVVX         | 123              |
        And I wait 2 seconds
        And I click the "#pbx-card-button-choice1" selector
        And I select "12" from "MOIS_VALIDITE"
        And I select "35" from "AN_VALIDITE"
        And I press "Valider"
        Then I should be on "https://preprod-tpeweb.paybox.com/cgi/MYtraitetransaction.cgi" wait otherwise
        And I wait 5 second until I see "PAIEMENT ACCEPTÉ"
        And I click the ".textCenter:last-child a" selector
        And I should be on "/paiement" wait otherwise
        When I simulate IPN call with "00000" code for the last donation of "test@test.com"
        And I should be on "/adhesion/confirmation-email" wait otherwise
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
                "from_email": "no-reply@parti-renaissance.fr",
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
                        "content": "http://test.renaissance.code/connexion-avec-un-lien-magique?user=test@test.com&expires=@number@&hash=@string@"
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
        When I click on the email link "magic_link"
        Then I should be on "/adhesion/confirmation-email" wait otherwise
        And I should see "Confirmer votre adresse email"
        And I should see "test@test.com"
        When I fill in the following:
            | code | 1234 |
        And I press "Valider"
        Then I should see "Le code d'activation est erroné."
        When I fill in the following:
            | code | 4321 |
        And I press "Valider"
        Then I should see "Le code d'activation est erroné."
        When I fill in the following:
            | code | 1234 |
        And I press "Valider"
        Then I should see "Le code d'activation est erroné."
        When I fill in the following:
            | code | 2345 |
        And I press "Valider"
        Then I should see "Veuillez patienter quelques minutes avant de retenter."
        When I clean the session cookie
        And I click on the email link "magic_link"
        Then I should be on "/adhesion/creation-mot-de-passe" wait otherwise

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
        When I click the "input[name='adhesion_further_information[subscriptionTypes][]']" selector
        And I press "Continuer"
        Then I should see "Vous avez accepté de recevoir des informations du parti par SMS ou téléphone, cependant, vous n'avez pas précisé votre numéro de téléphone."
        When I fill in the following:
            | adhesion_further_information[phone][number] | 0123456789 |
        And I press "Continuer"
        Then I should be on "/adhesion/felicitations" wait otherwise
        And I should see "Félicitations, Marine !"
