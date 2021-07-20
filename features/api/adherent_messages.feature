@api
Feature:
  In order to manage adherent messages
  As a non logged-in user
  I should be able to access API adherent messages

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                |
      | LoadClientData                  |
      | LoadGeoZoneData                 |
      | LoadAudienceData                |
      | LoadReferentTagData             |
      | LoadReferentTagsZonesLinksData  |

  Scenario: As a logged-in referent I can create an adherent message with audience
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/adherent_messages" with body:
    """
    {
       "content":"<table style=\"border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 320px;Margin: 0 auto;background-color: #e7e7e7;width:100%\" cellpadding=\"0\" cellspacing=\"0\">\n  <tbody>\n  <tr style=\"vertical-align: top\">\n    <td style=\"word-break: break-word;border-collapse: collapse !important;vertical-align: top\">\n    <!--[if (mso)|(IE)]><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td align=\"center\" style=\"background-color: #e7e7e7;\"><![endif]-->\n    \n\n<div id=\"u_row_1\" class=\"u-row-container v-row-padding--vertical v-row-background-image--outer v-row-background-color\" style=\"padding: 0px;background-color: transparent\">\n  <div class=\"u-row v-row-columns-background-color-background-color\" style=\"Margin: 0 auto;min-width: 320px;max-width: 500px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;\">\n    <div class=\"v-row-background-image--inner\" style=\"border-collapse: collapse;display: table;width: 100%;background-color: transparent;\">\n      <!--[if (mso)|(IE)]><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"v-row-padding v-row-background-image--outer v-row-background-color\" style=\"padding: 0px;background-color: transparent;\" align=\"center\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:500px;\"><tr class=\"v-row-background-image--inner v-row-columns-background-color-background-color\" style=\"background-color: transparent;\"><![endif]-->\n      \n<!--[if (mso)|(IE)]><td align=\"center\" width=\"500\" class=\"v-col-padding v-col-background-color v-col-border\" style=\"width: 500px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;\" valign=\"top\"><![endif]-->\n<div id=\"u_column_1\" class=\"u-col u-col-100\" style=\"max-width: 320px;min-width: 500px;display: table-cell;vertical-align: top;\">\n  <div class=\"v-col-background-color\" style=\"width: 100% !important;\">\n  <!--[if (!mso)&(!IE)]><!--><div class=\"v-col-padding v-col-border\" style=\"padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;\"><!--<![endif]-->\n  \n  <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->\n  </div>\n</div>\n<!--[if (mso)|(IE)]></td><![endif]-->\n      <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->\n    </div>\n  </div>\n</div>\n\n\n    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->\n    </td>\n  </tr>\n  </tbody>\n  </table>",
       "label":"Test by referent",
       "subject":"Test referent",
       "type":"referent",
       "audience": "4562e1ca-09ee-4500-96b6-a73431e40bf1"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "uuid": "@uuid@",
      "label": "Test by referent",
      "subject": "Test referent",
      "status": "draft",
      "recipient_count": 0,
      "synchronized": false,
      "from_name": "Referent Referent | La République En Marche !"
    }
    """
