@renaissance_user
@javascript
@javascript1
Feature: The goal is to donate one time or multiple time with a subscription
    In order to donate
    As an anonymous user or connected user
    I should be able to donate punctually or subscribe foreach month

    Scenario: An anonymous user can donate successfully
        Given the following fixtures are loaded:
            | LoadDonatorIdentifierData |
        And I am on "/don"
        Then wait 2 second until I see "Unique"
        When I click the "amount_500_label" element
        Then I should see "170.00 €"
        When I click the "amount_60_label" element
        Then I should see "20.40 €"
        When I click the "donation_request_localDestination" element
        And I press "Suivant"
        And I click the "donation_request_gender_0_label" element
        And I click the ".autocomplete-fields-toggle" selector
        And I click the "donation_request_nationality_select_widget" element
        And I click the "#donation_request_nationality_select_widget .re-input-option--selected" selector
        And I fill in the following:
            | donation_request[firstName]                  | Marine                 |
            | donation_request[lastName]                   | Dupont                 |
            | donation_request[emailAddress]               | marine.dupont@parti.re |
            | donation_request[address][address]           | 9 rue du lycée         |
            | donation_request[address][additionalAddress] | app 9                  |
            | donation_request[address][postalCode]        | 06000                  |
            | donation_request[address][cityName]          | Nice                   |
        And I click the "donation_request_address_country_select_widget" element
        And I click the "#donation_request_address_country_select_widget .re-input-option--selected" selector
        And I wait 2 seconds
        And I click the "#step_2 button" selector
        And I click the "donation_request_autorisations" element
        And I click the "#step_3 button" selector
        Then I should be on "https://preprod-tpeweb.paybox.com/cgi/FramepagepaiementRWD.cgi" wait otherwise
        And I wait until I see "60.00 EUR"
        When I simulate IPN call with "00000" code for the last donation of "marine.dupont@parti.re"
        And I am on payment status page for the last donation of "marine.dupont@parti.re"
        Then I should be on "/don/merci" wait otherwise
        And I should see "Merci Marine pour votre don !"

    Scenario: The user can subscribe to donate each month successfully but can't have a second subscription
        Given the following fixtures are loaded:
            | LoadDonatorIdentifierData |
        And I am on "/don"
        Then wait 2 second until I see "Unique"
        When I click the "duration_monthly_label" element
        And I click the "amount_60_label" element
        Then I should see "20.40 € / mois"
        When I click the "donation_request_localDestination" element
        And I press "Suivant"
        And I click the "donation_request_gender_0_label" element
        And I click the ".autocomplete-fields-toggle" selector
        And I click the "donation_request_nationality_select_widget" element
        And I click the "#donation_request_nationality_select_widget .re-input-option--selected" selector
        And I fill in the following:
            | donation_request[firstName]                  | Marine                 |
            | donation_request[lastName]                   | Dupont                 |
            | donation_request[emailAddress]               | marine.dupont@parti.re |
            | donation_request[address][address]           | 9 rue du lycée         |
            | donation_request[address][additionalAddress] | app 9                  |
            | donation_request[address][postalCode]        | 06000                  |
            | donation_request[address][cityName]          | Nice                   |
        And I click the "donation_request_address_country_select_widget" element
        And I click the "#donation_request_address_country_select_widget .re-input-option--selected" selector
        And I wait 2 seconds
        And I click the "#step_2 button" selector
        And I click the "donation_request_autorisations" element
        And I click the "#step_3 button" selector
        Then I should be on "https://preprod-tpeweb.paybox.com/cgi/FramepagepaiementRWD.cgi" wait otherwise
        And I wait until I see "60.00 EUR"
        When I simulate IPN call with "00000" code for the last donation of "marine.dupont@parti.re"
        And I am on payment status page for the last donation of "marine.dupont@parti.re"
        Then I should be on "/don/merci" wait otherwise
        And I should see "Merci Marine pour votre don !"

        Given I am on "/don"
        Then wait 2 second until I see "Unique"
        When I click the "duration_monthly_label" element
        And I click the "donation_request_localDestination" element
        And I press "Suivant"
        And I click the "donation_request_gender_0_label" element
        And I click the ".autocomplete-fields-toggle" selector
        And I click the "donation_request_nationality_select_widget" element
        And I click the "#donation_request_nationality_select_widget .re-input-option--selected" selector
        And I fill in the following:
            | donation_request[firstName]                  | Marine                 |
            | donation_request[lastName]                   | Dupont                 |
            | donation_request[emailAddress]               | marine.dupont@parti.re |
            | donation_request[address][address]           | 9 rue du lycée         |
            | donation_request[address][additionalAddress] | app 9                  |
            | donation_request[address][postalCode]        | 06000                  |
            | donation_request[address][cityName]          | Nice                   |
        And I click the "donation_request_address_country_select_widget" element
        And I click the "#donation_request_address_country_select_widget .re-input-option--selected" selector
        And I wait 2 seconds
        And I click the "#step_2 button" selector
        And I click the "donation_request_autorisations" element
        And I click the "#step_3 button" selector
        Then I should see "Vous faites déjà un don mensuel au parti Renaissance"

        When I click the "duration_punctual_label" element
        And I click the "amount_60_label" element
        Then I should see "20.40 €"
        When I click the "donation_request_localDestination" element
        And I press "Suivant"
        And I click the "donation_request_gender_0_label" element
        And I click the ".autocomplete-fields-toggle" selector
        And I click the "donation_request_nationality_select_widget" element
        And I click the "#donation_request_nationality_select_widget .re-input-option--selected" selector
        And I fill in the following:
            | donation_request[firstName]                  | Marine                 |
            | donation_request[lastName]                   | Dupont                 |
            | donation_request[emailAddress]               | marine.dupont@parti.re |
            | donation_request[address][address]           | 9 rue du lycée         |
            | donation_request[address][additionalAddress] | app 9                  |
            | donation_request[address][postalCode]        | 06000                  |
            | donation_request[address][cityName]          | Nice                   |
        And I click the "donation_request_address_country_select_widget" element
        And I click the "#donation_request_address_country_select_widget .re-input-option--selected" selector
        And I wait 2 seconds
        And I click the "#step_2 button" selector
        And I click the "#step_3 button" selector
        Then I should be on "https://preprod-tpeweb.paybox.com/cgi/FramepagepaiementRWD.cgi" wait otherwise
        And I wait until I see "60.00 EUR"
        When I simulate IPN call with "00000" code for the last donation of "marine.dupont@parti.re"
        And I am on payment status page for the last donation of "marine.dupont@parti.re"
        Then I should be on "/don/merci" wait otherwise
        And I should see "Merci Marine pour votre don !"

    Scenario: An anonymous user can donate as major donor successfully
        Given the following fixtures are loaded:
            | LoadDonatorIdentifierData |
        And I am on "/grands-donateurs"
        Then wait 2 second until I see "1500"
        When I click the "amount_1500_label" element
        Then I should see "510.00 €"
        When I press "Suivant"
        And I click the "donation_request_gender_0_label" element
        And I click the ".autocomplete-fields-toggle" selector
        And I click the "donation_request_nationality_select_widget" element
        And I click the "#donation_request_nationality_select_widget .re-input-option--selected" selector
        And I fill in the following:
            | donation_request[firstName]                  | Marine                 |
            | donation_request[lastName]                   | Dupont                 |
            | donation_request[emailAddress]               | marine.dupont@parti.re |
            | donation_request[address][address]           | 9 rue du lycée         |
            | donation_request[address][additionalAddress] | app 9                  |
            | donation_request[address][postalCode]        | 06000                  |
            | donation_request[address][cityName]          | Nice                   |
        And I click the "donation_request_address_country_select_widget" element
        And I click the "#donation_request_address_country_select_widget .re-input-option--selected" selector
        And I wait 2 seconds
        And I click the "#step_2 button" selector
        And I click the "donation_request_autorisations" element
        And I click the "#step_3 button" selector
        Then I should be on "https://preprod-tpeweb.paybox.com/cgi/FramepagepaiementRWD.cgi" wait otherwise
        And I wait until I see "1500.00 EUR"
        When I simulate IPN call with "00000" code for the last donation of "marine.dupont@parti.re"
        And I am on payment status page for the last donation of "marine.dupont@parti.re"
        Then I should be on "/don/merci" wait otherwise
        And I should see "Merci Marine pour votre don !"
