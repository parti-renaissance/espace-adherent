@renaissance_user
@javascript
@javascript1
Feature:

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
        When I click the ".autocomplete-fields-toggle" selector
        And I fill in the following:
            | membership_request[address][address]    | 44 rue des courcelles |
            | membership_request[address][postalCode] | 75008                 |
            | membership_request[address][cityName]   | Paris                 |
        When I press "Suivant"
        And I wait 3 seconds
        Then User "renaissance-user-4@en-marche-dev.fr" should have 8 subscription types
        And I should see "Cotisation pour l’année 2025"
        When I press "Je cotise pour 60 €"

        # Step 5 : payment
        Then I should be on "https://preprod-tpeweb.paybox.com/cgi/FramepagepaiementRWD.cgi" wait otherwise
        And I wait until I see "60.00 EUR"
        When I simulate IPN call with "00000" code for the last donation of "renaissance-user-4@en-marche-dev.fr"
        And I am on payment status page for the last donation of "renaissance-user-4@en-marche-dev.fr"
        Then I should be on "/adhesion/felicitations" wait otherwise
        And User "renaissance-user-4@en-marche-dev.fr" should have tag "adherent:a_jour_2025:recotisation"
        And I should have 1 email "RenaissanceReAdhesionConfirmationMessage" for "renaissance-user-4@en-marche-dev.fr" with payload:
            """
            {
                "template_name": "renaissance-re-adhesion-confirmation",
                "template_content": [],
                "message": {
                    "subject": "Et une année de plus !",
                    "from_email": "contact@parti-renaissance.fr",
                    "html": null,
                    "global_merge_vars": [
                        {
                            "name": "first_name",
                            "content": "Louis"
                        }
                    ],
                    "from_name": "Gabriel Attal",
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
        And I should see "Vous êtes à jour de cotisations, félicitations !"
        And User "renaissance-user-4@en-marche-dev.fr" should have zones "borough_75108, district_75-4"
