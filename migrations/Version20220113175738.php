<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220113175738 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F9F9FAFBD17F50A6 ON adherent_activation_keys (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6F8B4B5AD17F50A6 ON adherent_change_email_token (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_376DBA0D17F50A6 ON adherent_email_subscribe_token (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D63B17FAD17F50A6 ON adherent_instance_quality (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9C0C3D60D17F50A6 ON adherent_mandate (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D187C183D17F50A6 ON adherent_messages (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_66D163EAD17F50A6 ON adherent_reset_password_tokens (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9DF0C7EBD17F50A6 ON adherent_segment (uuid)');
        $this->addSql('ALTER TABLE adherent_tags RENAME INDEX adherent_tag_name_unique TO UNIQ_D34384A45E237E06');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_390E4D38D17F50A6 ON adherent_zone_based_role (uuid)');
        $this->addSql('ALTER TABLE adherents RENAME INDEX adherents_email_address_unique TO UNIQ_562C7DA3B08E074E');
        $this->addSql('ALTER TABLE adherents RENAME INDEX adherents_uuid_unique TO UNIQ_562C7DA3D17F50A6');
        $this->addSql('ALTER TABLE
          administrators RENAME INDEX administrators_email_address_unique TO UNIQ_73A716FB08E074E');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D1D60956D17F50A6 ON application_request_running_mate (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_11396570D17F50A6 ON application_request_volunteer (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_26BC800D17F50A6 ON assessor_requests (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FDCD9418D17F50A6 ON audience (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C5C2F52FD17F50A6 ON audience_segment (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BA99FEBBD17F50A6 ON audience_snapshot (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B85ACFECD17F50A6 ON banned_adherent (uuid)');
        $this->addSql('ALTER TABLE
          biography_executive_office_member RENAME INDEX executive_office_member_slug_unique TO UNIQ_44A61059989D9B62');
        $this->addSql('ALTER TABLE
          biography_executive_office_member RENAME INDEX executive_office_member_uuid_unique TO UNIQ_44A61059D17F50A6');
        $this->addSql('ALTER TABLE cause RENAME INDEX cause_name_unique TO UNIQ_F0DA7FBF5E237E06');
        $this->addSql('ALTER TABLE cause RENAME INDEX cause_uuid_unique TO UNIQ_F0DA7FBFD17F50A6');
        $this->addSql('ALTER TABLE cause_follower RENAME INDEX cause_follower_uuid_unique TO UNIQ_6F9A8544D17F50A6');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6E7481A9D17F50A6 ON certification_request (uuid)');
        $this->addSql('ALTER TABLE coalition RENAME INDEX coalition_name_unique TO UNIQ_A7CD7AC75E237E06');
        $this->addSql('ALTER TABLE coalition RENAME INDEX coalition_uuid_unique TO UNIQ_A7CD7AC7D17F50A6');
        $this->addSql('ALTER TABLE
          coalition_follower RENAME INDEX coalition_follower_uuid_unique TO UNIQ_DFF370E2D17F50A6');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_368B0161D17F50A6 ON committee_candidacy_invitation (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2CA406E5D17F50A6 ON committee_election (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4F1CDC80D17F50A6 ON committee_feed_item (uuid)');
        $this->addSql('ALTER TABLE committees RENAME INDEX committee_uuid_unique TO UNIQ_A36198C6D17F50A6');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E7A6490ED17F50A6 ON committees_memberships (uuid)');
        $this->addSql('ALTER TABLE
          consular_district RENAME INDEX consular_district_code_unique TO UNIQ_77152B8877153098');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5AC419DDD17F50A6 ON deputy_managed_users_message (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8947610DD17F50A6 ON designation (uuid)');
        $this->addSql('ALTER TABLE devices RENAME INDEX devices_uuid_unique TO UNIQ_11074E9AD17F50A6');
        $this->addSql('ALTER TABLE devices RENAME INDEX devices_device_uuid_unique TO UNIQ_11074E9A5846859C');
        $this->addSql('ALTER TABLE districts RENAME INDEX district_code_unique TO UNIQ_68E318DC77153098');
        $this->addSql('ALTER TABLE districts RENAME INDEX district_referent_tag_unique TO UNIQ_68E318DC9C262DB3');
        $this->addSql('ALTER TABLE donation_tags RENAME INDEX donation_tag_label_unique TO UNIQ_7E2FBF0CEA750E8');
        $this->addSql('ALTER TABLE
          donations
        DROP
          INDEX donation_uuid_idx,
        ADD
          UNIQUE INDEX UNIQ_CDE98962D17F50A6 (uuid)');
        $this->addSql('ALTER TABLE donator_tags RENAME INDEX donator_tag_label_unique TO UNIQ_F02E4E4EEA750E8');
        $this->addSql('ALTER TABLE donators RENAME INDEX donator_identifier_unique TO UNIQ_A902FDD7772E836A');
        $this->addSql('ALTER TABLE
          elected_representative_zone_category RENAME INDEX elected_representative_zone_category_name_unique TO UNIQ_2E753C3B5E237E06');
        $this->addSql('ALTER TABLE election_city_card RENAME INDEX city_card_city_unique TO UNIQ_EB01E8D18BAC62AF');
        $this->addSql('ALTER TABLE email_templates RENAME INDEX email_template_uuid_unique TO UNIQ_6023E2A5D17F50A6');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4C81E852D17F50A6 ON emails (uuid)');
        $this->addSql('ALTER TABLE
          event_group_category RENAME INDEX event_group_category_name_unique TO UNIQ_D038E3CD5E237E06');
        $this->addSql('ALTER TABLE
          event_group_category RENAME INDEX event_group_category_slug_unique TO UNIQ_D038E3CD989D9B62');
        $this->addSql('ALTER TABLE events RENAME INDEX event_uuid_unique TO UNIQ_5387574AD17F50A6');
        $this->addSql('ALTER TABLE events RENAME INDEX event_slug_unique TO UNIQ_5387574A989D9B62');
        $this->addSql('ALTER TABLE events_categories RENAME INDEX event_category_name_unique TO UNIQ_EF0AF3E95E237E06');
        $this->addSql('ALTER TABLE events_categories RENAME INDEX event_category_slug_unique TO UNIQ_EF0AF3E9989D9B62');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B94D5AADD17F50A6 ON events_invitations (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EEFA30C0D17F50A6 ON events_registrations (uuid)');
        $this->addSql('ALTER TABLE
          facebook_profiles RENAME INDEX facebook_profile_facebook_id TO UNIQ_4C9116989BE8FD98');
        $this->addSql('ALTER TABLE facebook_profiles RENAME INDEX facebook_profile_uuid TO UNIQ_4C911698D17F50A6');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1CD95620D17F50A6 ON failed_login_attempt (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_47F0AE28D17F50A6 ON filesystem_file (uuid)');
        $this->addSql('ALTER TABLE filesystem_file RENAME INDEX filesystem_file_slug_unique TO UNIQ_47F0AE28989D9B62');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A4CCEF07D17F50A6 ON geo_zone (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BB26C6D3D17F50A6 ON instance_quality (uuid)');
        $this->addSql('ALTER TABLE
          institutional_events_categories RENAME INDEX institutional_event_category_name_unique TO UNIQ_18A3A4175E237E06');
        $this->addSql('ALTER TABLE
          institutional_events_categories RENAME INDEX institutional_event_slug_unique TO UNIQ_18A3A417989D9B62');
        $this->addSql('ALTER TABLE
          interactive_choices RENAME INDEX interactive_choices_content_key_unique TO UNIQ_3C6695A73F7BFD5C');
        $this->addSql('ALTER TABLE
          interactive_choices RENAME INDEX interactive_choices_uuid_unique TO UNIQ_3C6695A7D17F50A6');
        $this->addSql('ALTER TABLE
          interactive_invitations RENAME INDEX interactive_invitations_uuid_unique TO UNIQ_45258689D17F50A6');
        $this->addSql('ALTER TABLE
          internal_api_application RENAME INDEX internal_application_uuid_unique TO UNIQ_D0E72FCDD17F50A6');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_232710AED17F50A6 ON invitations (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6579E8E7D17F50A6 ON jecoute_data_survey (uuid)');
        $this->addSql('ALTER TABLE jecoute_news RENAME INDEX jecoute_news_uuid_unique TO UNIQ_3436209D17F50A6');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E74226FD17F50A6 ON jecoute_region (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_17E1064BD17F50A6 ON jecoute_riposte (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EC4948E5D17F50A6 ON jecoute_survey (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A2FBFA81D17F50A6 ON jecoute_survey_question (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8DF5D818D17F50A6 ON jemarche_data_survey (uuid)');
        $this->addSql('ALTER TABLE
          legislative_candidates RENAME INDEX legislative_candidates_slug_unique TO UNIQ_AE55AF9B989D9B62');
        $this->addSql('ALTER TABLE
          legislative_district_zones RENAME INDEX legislative_district_zones_area_code_unique TO UNIQ_5853B7FAB5501F87');
        $this->addSql('ALTER TABLE mooc RENAME INDEX mooc_slug TO UNIQ_9D5D3B55989D9B62');
        $this->addSql('ALTER TABLE mooc_chapter RENAME INDEX mooc_chapter_slug TO UNIQ_A3EDA0D1989D9B62');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_421C13B9D17F50A6 ON my_team_delegated_access (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F3809347D17F50A6 ON national_council_election (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_15C94F61D17F50A6 ON newsletter_invitations (uuid)');
        $this->addSql('ALTER TABLE
          oauth_access_tokens RENAME INDEX oauth_access_tokens_identifier_unique TO UNIQ_CA42527C772E836A');
        $this->addSql('ALTER TABLE
          oauth_access_tokens RENAME INDEX oauth_access_tokens_uuid_unique TO UNIQ_CA42527CD17F50A6');
        $this->addSql('ALTER TABLE
          oauth_auth_codes RENAME INDEX oauth_auth_codes_identifier_unique TO UNIQ_BB493F83772E836A');
        $this->addSql('ALTER TABLE
          oauth_auth_codes RENAME INDEX oauth_auth_codes_uuid_unique TO UNIQ_BB493F83D17F50A6');
        $this->addSql('ALTER TABLE oauth_clients RENAME INDEX oauth_clients_uuid_unique TO UNIQ_13CE8101D17F50A6');
        $this->addSql('ALTER TABLE
          oauth_refresh_tokens RENAME INDEX oauth_refresh_tokens_identifier_unique TO UNIQ_5AB687772E836A');
        $this->addSql('ALTER TABLE
          oauth_refresh_tokens RENAME INDEX oauth_refresh_tokens_uuid_unique TO UNIQ_5AB687D17F50A6');
        $this->addSql('ALTER TABLE
          pap_address
        DROP
          INDEX IDX_47071E11D17F50A6,
        ADD
          UNIQUE INDEX UNIQ_47071E11D17F50A6 (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_112ABBE1D17F50A6 ON pap_building (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_61470C81D17F50A6 ON pap_building_block (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8B79BF60D17F50A6 ON pap_building_block_statistics (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D9F29104D17F50A6 ON pap_building_event (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6FB4E7BD17F50A6 ON pap_building_statistics (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EF50C8E8D17F50A6 ON pap_campaign (uuid)');
        $this->addSql('ALTER TABLE
          pap_campaign_history RENAME INDEX pap_campaign_history_uuid_unique TO UNIQ_5A3F26F7D17F50A6');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_633C3C64D17F50A6 ON pap_floor (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_853B68C8D17F50A6 ON pap_floor_statistics (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FBF5A013D17F50A6 ON pap_voter (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EC191198D17F50A6 ON phoning_campaign_history (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_39FAEE95D17F50A6 ON political_committee (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_54369E83D17F50A6 ON political_committee_feed_item (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FD85437BD17F50A6 ON political_committee_membership (uuid)');
        $this->addSql('ALTER TABLE poll RENAME INDEX poll_uuid_unique TO UNIQ_84BCFA45D17F50A6');
        $this->addSql('ALTER TABLE poll_choice RENAME INDEX poll_choice_uuid_unique TO UNIQ_2DAE19C9D17F50A6');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8B785227D17F50A6 ON programmatic_foundation_approach (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_213A5F1ED17F50A6 ON programmatic_foundation_measure (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8E8E96D5D17F50A6 ON programmatic_foundation_project (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_735C1D01D17F50A6 ON programmatic_foundation_sub_approach (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_51BC1381D17F50A6 ON push_token (uuid)');
        $this->addSql('ALTER TABLE qr_code RENAME INDEX qr_code_name TO UNIQ_7D8B1FB55E237E06');
        $this->addSql('ALTER TABLE qr_code RENAME INDEX qr_code_uuid TO UNIQ_7D8B1FB5D17F50A6');
        $this->addSql('ALTER TABLE referent RENAME INDEX referent_slug_unique TO UNIQ_FE9AAC6C989D9B62');
        $this->addSql('ALTER TABLE referent_area RENAME INDEX referent_area_area_code_unique TO UNIQ_AB758097B5501F87');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1E41AC61D17F50A6 ON referent_managed_users_message (uuid)');
        $this->addSql('ALTER TABLE referent_tags RENAME INDEX referent_tag_name_unique TO UNIQ_135D29D95E237E06');
        $this->addSql('ALTER TABLE referent_tags RENAME INDEX referent_tag_code_unique TO UNIQ_135D29D977153098');
        $this->addSql('ALTER TABLE reports RENAME INDEX report_uuid_unique TO UNIQ_F11FA745D17F50A6');
        $this->addSql('ALTER TABLE roles RENAME INDEX board_member_role_code_unique TO UNIQ_B63E2EC777153098');
        $this->addSql('ALTER TABLE roles RENAME INDEX board_member_role_name_unique TO UNIQ_B63E2EC75E237E06');
        $this->addSql('ALTER TABLE scope RENAME INDEX scope_code_unique TO UNIQ_AF55D377153098');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_79E333DCD17F50A6 ON sms_campaign (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E761AF89D17F50A6 ON sms_stop_history (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C4E0A61FD17F50A6 ON team (uuid)');
        $this->addSql('ALTER TABLE team RENAME INDEX team_name_unique TO UNIQ_C4E0A61F5E237E06');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6FFBDA1D17F50A6 ON team_member (uuid)');
        $this->addSql('ALTER TABLE
          territorial_council RENAME INDEX territorial_council_name_unique TO UNIQ_B6DCA2A55E237E06');
        $this->addSql('ALTER TABLE
          territorial_council RENAME INDEX territorial_council_codes_unique TO UNIQ_B6DCA2A5E5ADC14D');
        $this->addSql('ALTER TABLE
          territorial_council RENAME INDEX territorial_council_uuid_unique TO UNIQ_B6DCA2A5D17F50A6');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DA86009AD17F50A6 ON territorial_council_candidacy_invitation (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A9919BF0D17F50A6 ON territorial_council_convocation (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_14CBC36BD17F50A6 ON territorial_council_election (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E0D7231ED17F50A6 ON territorial_council_election_poll (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_63EBCF6BD17F50A6 ON territorial_council_election_poll_choice (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_45241D62D17F50A6 ON territorial_council_feed_item (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A998316D17F50A6 ON territorial_council_membership (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D80D385D17F50A6 ON territorial_council_official_report (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6F22A458D17F50A6 ON thematic_community (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C0B5CEAD17F50A6 ON thematic_community_contact (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_22B6AC05D17F50A6 ON thematic_community_membership (uuid)');
        $this->addSql('ALTER TABLE
          ton_macron_choices RENAME INDEX ton_macron_choices_content_key_unique TO UNIQ_6247B0DE3F7BFD5C');
        $this->addSql('ALTER TABLE
          ton_macron_choices RENAME INDEX ton_macron_choices_uuid_unique TO UNIQ_6247B0DED17F50A6');
        $this->addSql('ALTER TABLE
          ton_macron_friend_invitations RENAME INDEX ton_macron_friend_invitations_uuid_unique TO UNIQ_78714946D17F50A6');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_40448230D17F50A6 ON user_authorizations (uuid)');
        $this->addSql('ALTER TABLE user_documents RENAME INDEX document_uuid_unique TO UNIQ_A250FF6CD17F50A6');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3F426D6DD17F50A6 ON voting_platform_candidate (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2C1A353AD17F50A6 ON voting_platform_candidate_group (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7249D537D17F50A6 ON voting_platform_candidate_group_result (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E144C94D17F50A6 ON voting_platform_election (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_13C1C73FD17F50A6 ON voting_platform_election_pool_result (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_67EFA0E4D17F50A6 ON voting_platform_election_result (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F15D87B7D17F50A6 ON voting_platform_election_round (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F2670966D17F50A6 ON voting_platform_election_round_result (uuid)');
        $this->addSql('ALTER TABLE web_hooks RENAME INDEX web_hook_uuid_unique TO UNIQ_CDB836ADD17F50A6');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_F9F9FAFBD17F50A6 ON adherent_activation_keys');
        $this->addSql('DROP INDEX UNIQ_6F8B4B5AD17F50A6 ON adherent_change_email_token');
        $this->addSql('DROP INDEX UNIQ_376DBA0D17F50A6 ON adherent_email_subscribe_token');
        $this->addSql('DROP INDEX UNIQ_D63B17FAD17F50A6 ON adherent_instance_quality');
        $this->addSql('DROP INDEX UNIQ_9C0C3D60D17F50A6 ON adherent_mandate');
        $this->addSql('DROP INDEX UNIQ_D187C183D17F50A6 ON adherent_messages');
        $this->addSql('DROP INDEX UNIQ_66D163EAD17F50A6 ON adherent_reset_password_tokens');
        $this->addSql('DROP INDEX UNIQ_9DF0C7EBD17F50A6 ON adherent_segment');
        $this->addSql('ALTER TABLE adherent_tags RENAME INDEX uniq_d34384a45e237e06 TO adherent_tag_name_unique');
        $this->addSql('DROP INDEX UNIQ_390E4D38D17F50A6 ON adherent_zone_based_role');
        $this->addSql('ALTER TABLE adherents RENAME INDEX uniq_562c7da3b08e074e TO adherents_email_address_unique');
        $this->addSql('ALTER TABLE adherents RENAME INDEX uniq_562c7da3d17f50a6 TO adherents_uuid_unique');
        $this->addSql('ALTER TABLE
          administrators RENAME INDEX uniq_73a716fb08e074e TO administrators_email_address_unique');
        $this->addSql('DROP INDEX UNIQ_D1D60956D17F50A6 ON application_request_running_mate');
        $this->addSql('DROP INDEX UNIQ_11396570D17F50A6 ON application_request_volunteer');
        $this->addSql('DROP INDEX UNIQ_26BC800D17F50A6 ON assessor_requests');
        $this->addSql('DROP INDEX UNIQ_FDCD9418D17F50A6 ON audience');
        $this->addSql('DROP INDEX UNIQ_C5C2F52FD17F50A6 ON audience_segment');
        $this->addSql('DROP INDEX UNIQ_BA99FEBBD17F50A6 ON audience_snapshot');
        $this->addSql('DROP INDEX UNIQ_B85ACFECD17F50A6 ON banned_adherent');
        $this->addSql('ALTER TABLE
          biography_executive_office_member RENAME INDEX uniq_44a61059989d9b62 TO executive_office_member_slug_unique');
        $this->addSql('ALTER TABLE
          biography_executive_office_member RENAME INDEX uniq_44a61059d17f50a6 TO executive_office_member_uuid_unique');
        $this->addSql('ALTER TABLE cause RENAME INDEX uniq_f0da7fbf5e237e06 TO cause_name_unique');
        $this->addSql('ALTER TABLE cause RENAME INDEX uniq_f0da7fbfd17f50a6 TO cause_uuid_unique');
        $this->addSql('ALTER TABLE cause_follower RENAME INDEX uniq_6f9a8544d17f50a6 TO cause_follower_uuid_unique');
        $this->addSql('DROP INDEX UNIQ_6E7481A9D17F50A6 ON certification_request');
        $this->addSql('ALTER TABLE coalition RENAME INDEX uniq_a7cd7ac75e237e06 TO coalition_name_unique');
        $this->addSql('ALTER TABLE coalition RENAME INDEX uniq_a7cd7ac7d17f50a6 TO coalition_uuid_unique');
        $this->addSql('ALTER TABLE
          coalition_follower RENAME INDEX uniq_dff370e2d17f50a6 TO coalition_follower_uuid_unique');
        $this->addSql('DROP INDEX UNIQ_368B0161D17F50A6 ON committee_candidacy_invitation');
        $this->addSql('DROP INDEX UNIQ_2CA406E5D17F50A6 ON committee_election');
        $this->addSql('DROP INDEX UNIQ_4F1CDC80D17F50A6 ON committee_feed_item');
        $this->addSql('ALTER TABLE committees RENAME INDEX uniq_a36198c6d17f50a6 TO committee_uuid_unique');
        $this->addSql('DROP INDEX UNIQ_E7A6490ED17F50A6 ON committees_memberships');
        $this->addSql('ALTER TABLE
          consular_district RENAME INDEX uniq_77152b8877153098 TO consular_district_code_unique');
        $this->addSql('DROP INDEX UNIQ_5AC419DDD17F50A6 ON deputy_managed_users_message');
        $this->addSql('DROP INDEX UNIQ_8947610DD17F50A6 ON designation');
        $this->addSql('ALTER TABLE devices RENAME INDEX uniq_11074e9a5846859c TO devices_device_uuid_unique');
        $this->addSql('ALTER TABLE devices RENAME INDEX uniq_11074e9ad17f50a6 TO devices_uuid_unique');
        $this->addSql('ALTER TABLE districts RENAME INDEX uniq_68e318dc77153098 TO district_code_unique');
        $this->addSql('ALTER TABLE districts RENAME INDEX uniq_68e318dc9c262db3 TO district_referent_tag_unique');
        $this->addSql('ALTER TABLE donation_tags RENAME INDEX uniq_7e2fbf0cea750e8 TO donation_tag_label_unique');
        $this->addSql('ALTER TABLE donations DROP INDEX UNIQ_CDE98962D17F50A6, ADD INDEX donation_uuid_idx (uuid)');
        $this->addSql('ALTER TABLE donator_tags RENAME INDEX uniq_f02e4e4eea750e8 TO donator_tag_label_unique');
        $this->addSql('ALTER TABLE donators RENAME INDEX uniq_a902fdd7772e836a TO donator_identifier_unique');
        $this->addSql('ALTER TABLE
          elected_representative_zone_category RENAME INDEX uniq_2e753c3b5e237e06 TO elected_representative_zone_category_name_unique');
        $this->addSql('ALTER TABLE election_city_card RENAME INDEX uniq_eb01e8d18bac62af TO city_card_city_unique');
        $this->addSql('ALTER TABLE email_templates RENAME INDEX uniq_6023e2a5d17f50a6 TO email_template_uuid_unique');
        $this->addSql('DROP INDEX UNIQ_4C81E852D17F50A6 ON emails');
        $this->addSql('ALTER TABLE
          event_group_category RENAME INDEX uniq_d038e3cd5e237e06 TO event_group_category_name_unique');
        $this->addSql('ALTER TABLE
          event_group_category RENAME INDEX uniq_d038e3cd989d9b62 TO event_group_category_slug_unique');
        $this->addSql('ALTER TABLE events RENAME INDEX uniq_5387574a989d9b62 TO event_slug_unique');
        $this->addSql('ALTER TABLE events RENAME INDEX uniq_5387574ad17f50a6 TO event_uuid_unique');
        $this->addSql('ALTER TABLE events_categories RENAME INDEX uniq_ef0af3e95e237e06 TO event_category_name_unique');
        $this->addSql('ALTER TABLE events_categories RENAME INDEX uniq_ef0af3e9989d9b62 TO event_category_slug_unique');
        $this->addSql('DROP INDEX UNIQ_B94D5AADD17F50A6 ON events_invitations');
        $this->addSql('DROP INDEX UNIQ_EEFA30C0D17F50A6 ON events_registrations');
        $this->addSql('ALTER TABLE
          facebook_profiles RENAME INDEX uniq_4c9116989be8fd98 TO facebook_profile_facebook_id');
        $this->addSql('ALTER TABLE facebook_profiles RENAME INDEX uniq_4c911698d17f50a6 TO facebook_profile_uuid');
        $this->addSql('DROP INDEX UNIQ_1CD95620D17F50A6 ON failed_login_attempt');
        $this->addSql('DROP INDEX UNIQ_47F0AE28D17F50A6 ON filesystem_file');
        $this->addSql('ALTER TABLE filesystem_file RENAME INDEX uniq_47f0ae28989d9b62 TO filesystem_file_slug_unique');
        $this->addSql('DROP INDEX UNIQ_A4CCEF07D17F50A6 ON geo_zone');
        $this->addSql('DROP INDEX UNIQ_BB26C6D3D17F50A6 ON instance_quality');
        $this->addSql('ALTER TABLE
          institutional_events_categories RENAME INDEX uniq_18a3a4175e237e06 TO institutional_event_category_name_unique');
        $this->addSql('ALTER TABLE
          institutional_events_categories RENAME INDEX uniq_18a3a417989d9b62 TO institutional_event_slug_unique');
        $this->addSql('ALTER TABLE
          interactive_choices RENAME INDEX uniq_3c6695a73f7bfd5c TO interactive_choices_content_key_unique');
        $this->addSql('ALTER TABLE
          interactive_choices RENAME INDEX uniq_3c6695a7d17f50a6 TO interactive_choices_uuid_unique');
        $this->addSql('ALTER TABLE
          interactive_invitations RENAME INDEX uniq_45258689d17f50a6 TO interactive_invitations_uuid_unique');
        $this->addSql('ALTER TABLE
          internal_api_application RENAME INDEX uniq_d0e72fcdd17f50a6 TO internal_application_uuid_unique');
        $this->addSql('DROP INDEX UNIQ_232710AED17F50A6 ON invitations');
        $this->addSql('DROP INDEX UNIQ_6579E8E7D17F50A6 ON jecoute_data_survey');
        $this->addSql('ALTER TABLE jecoute_news RENAME INDEX uniq_3436209d17f50a6 TO jecoute_news_uuid_unique');
        $this->addSql('DROP INDEX UNIQ_4E74226FD17F50A6 ON jecoute_region');
        $this->addSql('DROP INDEX UNIQ_17E1064BD17F50A6 ON jecoute_riposte');
        $this->addSql('DROP INDEX UNIQ_EC4948E5D17F50A6 ON jecoute_survey');
        $this->addSql('DROP INDEX UNIQ_A2FBFA81D17F50A6 ON jecoute_survey_question');
        $this->addSql('DROP INDEX UNIQ_8DF5D818D17F50A6 ON jemarche_data_survey');
        $this->addSql('ALTER TABLE
          legislative_candidates RENAME INDEX uniq_ae55af9b989d9b62 TO legislative_candidates_slug_unique');
        $this->addSql('ALTER TABLE
          legislative_district_zones RENAME INDEX uniq_5853b7fab5501f87 TO legislative_district_zones_area_code_unique');
        $this->addSql('ALTER TABLE mooc RENAME INDEX uniq_9d5d3b55989d9b62 TO mooc_slug');
        $this->addSql('ALTER TABLE mooc_chapter RENAME INDEX uniq_a3eda0d1989d9b62 TO mooc_chapter_slug');
        $this->addSql('DROP INDEX UNIQ_421C13B9D17F50A6 ON my_team_delegated_access');
        $this->addSql('DROP INDEX UNIQ_F3809347D17F50A6 ON national_council_election');
        $this->addSql('DROP INDEX UNIQ_15C94F61D17F50A6 ON newsletter_invitations');
        $this->addSql('ALTER TABLE
          oauth_access_tokens RENAME INDEX uniq_ca42527c772e836a TO oauth_access_tokens_identifier_unique');
        $this->addSql('ALTER TABLE
          oauth_access_tokens RENAME INDEX uniq_ca42527cd17f50a6 TO oauth_access_tokens_uuid_unique');
        $this->addSql('ALTER TABLE
          oauth_auth_codes RENAME INDEX uniq_bb493f83772e836a TO oauth_auth_codes_identifier_unique');
        $this->addSql('ALTER TABLE
          oauth_auth_codes RENAME INDEX uniq_bb493f83d17f50a6 TO oauth_auth_codes_uuid_unique');
        $this->addSql('ALTER TABLE oauth_clients RENAME INDEX uniq_13ce8101d17f50a6 TO oauth_clients_uuid_unique');
        $this->addSql('ALTER TABLE
          oauth_refresh_tokens RENAME INDEX uniq_5ab687772e836a TO oauth_refresh_tokens_identifier_unique');
        $this->addSql('ALTER TABLE
          oauth_refresh_tokens RENAME INDEX uniq_5ab687d17f50a6 TO oauth_refresh_tokens_uuid_unique');
        $this->addSql('ALTER TABLE
          pap_address
        DROP
          INDEX UNIQ_47071E11D17F50A6,
        ADD
          INDEX IDX_47071E11D17F50A6 (uuid)');
        $this->addSql('DROP INDEX UNIQ_112ABBE1D17F50A6 ON pap_building');
        $this->addSql('DROP INDEX UNIQ_61470C81D17F50A6 ON pap_building_block');
        $this->addSql('DROP INDEX UNIQ_8B79BF60D17F50A6 ON pap_building_block_statistics');
        $this->addSql('DROP INDEX UNIQ_D9F29104D17F50A6 ON pap_building_event');
        $this->addSql('DROP INDEX UNIQ_B6FB4E7BD17F50A6 ON pap_building_statistics');
        $this->addSql('DROP INDEX UNIQ_EF50C8E8D17F50A6 ON pap_campaign');
        $this->addSql('ALTER TABLE
          pap_campaign_history RENAME INDEX uniq_5a3f26f7d17f50a6 TO pap_campaign_history_uuid_unique');
        $this->addSql('DROP INDEX UNIQ_633C3C64D17F50A6 ON pap_floor');
        $this->addSql('DROP INDEX UNIQ_853B68C8D17F50A6 ON pap_floor_statistics');
        $this->addSql('DROP INDEX UNIQ_FBF5A013D17F50A6 ON pap_voter');
        $this->addSql('DROP INDEX UNIQ_EC191198D17F50A6 ON phoning_campaign_history');
        $this->addSql('DROP INDEX UNIQ_39FAEE95D17F50A6 ON political_committee');
        $this->addSql('DROP INDEX UNIQ_54369E83D17F50A6 ON political_committee_feed_item');
        $this->addSql('DROP INDEX UNIQ_FD85437BD17F50A6 ON political_committee_membership');
        $this->addSql('ALTER TABLE poll RENAME INDEX uniq_84bcfa45d17f50a6 TO poll_uuid_unique');
        $this->addSql('ALTER TABLE poll_choice RENAME INDEX uniq_2dae19c9d17f50a6 TO poll_choice_uuid_unique');
        $this->addSql('DROP INDEX UNIQ_8B785227D17F50A6 ON programmatic_foundation_approach');
        $this->addSql('DROP INDEX UNIQ_213A5F1ED17F50A6 ON programmatic_foundation_measure');
        $this->addSql('DROP INDEX UNIQ_8E8E96D5D17F50A6 ON programmatic_foundation_project');
        $this->addSql('DROP INDEX UNIQ_735C1D01D17F50A6 ON programmatic_foundation_sub_approach');
        $this->addSql('DROP INDEX UNIQ_51BC1381D17F50A6 ON push_token');
        $this->addSql('ALTER TABLE qr_code RENAME INDEX uniq_7d8b1fb55e237e06 TO qr_code_name');
        $this->addSql('ALTER TABLE qr_code RENAME INDEX uniq_7d8b1fb5d17f50a6 TO qr_code_uuid');
        $this->addSql('ALTER TABLE referent RENAME INDEX uniq_fe9aac6c989d9b62 TO referent_slug_unique');
        $this->addSql('ALTER TABLE referent_area RENAME INDEX uniq_ab758097b5501f87 TO referent_area_area_code_unique');
        $this->addSql('DROP INDEX UNIQ_1E41AC61D17F50A6 ON referent_managed_users_message');
        $this->addSql('ALTER TABLE referent_tags RENAME INDEX uniq_135d29d977153098 TO referent_tag_code_unique');
        $this->addSql('ALTER TABLE referent_tags RENAME INDEX uniq_135d29d95e237e06 TO referent_tag_name_unique');
        $this->addSql('ALTER TABLE reports RENAME INDEX uniq_f11fa745d17f50a6 TO report_uuid_unique');
        $this->addSql('ALTER TABLE roles RENAME INDEX uniq_b63e2ec777153098 TO board_member_role_code_unique');
        $this->addSql('ALTER TABLE roles RENAME INDEX uniq_b63e2ec75e237e06 TO board_member_role_name_unique');
        $this->addSql('ALTER TABLE scope RENAME INDEX uniq_af55d377153098 TO scope_code_unique');
        $this->addSql('DROP INDEX UNIQ_79E333DCD17F50A6 ON sms_campaign');
        $this->addSql('DROP INDEX UNIQ_E761AF89D17F50A6 ON sms_stop_history');
        $this->addSql('DROP INDEX UNIQ_C4E0A61FD17F50A6 ON team');
        $this->addSql('ALTER TABLE team RENAME INDEX uniq_c4e0a61f5e237e06 TO team_name_unique');
        $this->addSql('DROP INDEX UNIQ_6FFBDA1D17F50A6 ON team_member');
        $this->addSql('ALTER TABLE
          territorial_council RENAME INDEX uniq_b6dca2a5e5adc14d TO territorial_council_codes_unique');
        $this->addSql('ALTER TABLE
          territorial_council RENAME INDEX uniq_b6dca2a55e237e06 TO territorial_council_name_unique');
        $this->addSql('ALTER TABLE
          territorial_council RENAME INDEX uniq_b6dca2a5d17f50a6 TO territorial_council_uuid_unique');
        $this->addSql('DROP INDEX UNIQ_DA86009AD17F50A6 ON territorial_council_candidacy_invitation');
        $this->addSql('DROP INDEX UNIQ_A9919BF0D17F50A6 ON territorial_council_convocation');
        $this->addSql('DROP INDEX UNIQ_14CBC36BD17F50A6 ON territorial_council_election');
        $this->addSql('DROP INDEX UNIQ_E0D7231ED17F50A6 ON territorial_council_election_poll');
        $this->addSql('DROP INDEX UNIQ_63EBCF6BD17F50A6 ON territorial_council_election_poll_choice');
        $this->addSql('DROP INDEX UNIQ_45241D62D17F50A6 ON territorial_council_feed_item');
        $this->addSql('DROP INDEX UNIQ_2A998316D17F50A6 ON territorial_council_membership');
        $this->addSql('DROP INDEX UNIQ_8D80D385D17F50A6 ON territorial_council_official_report');
        $this->addSql('DROP INDEX UNIQ_6F22A458D17F50A6 ON thematic_community');
        $this->addSql('DROP INDEX UNIQ_5C0B5CEAD17F50A6 ON thematic_community_contact');
        $this->addSql('DROP INDEX UNIQ_22B6AC05D17F50A6 ON thematic_community_membership');
        $this->addSql('ALTER TABLE
          ton_macron_choices RENAME INDEX uniq_6247b0de3f7bfd5c TO ton_macron_choices_content_key_unique');
        $this->addSql('ALTER TABLE
          ton_macron_choices RENAME INDEX uniq_6247b0ded17f50a6 TO ton_macron_choices_uuid_unique');
        $this->addSql('ALTER TABLE
          ton_macron_friend_invitations RENAME INDEX uniq_78714946d17f50a6 TO ton_macron_friend_invitations_uuid_unique');
        $this->addSql('DROP INDEX UNIQ_40448230D17F50A6 ON user_authorizations');
        $this->addSql('ALTER TABLE user_documents RENAME INDEX uniq_a250ff6cd17f50a6 TO document_uuid_unique');
        $this->addSql('DROP INDEX UNIQ_3F426D6DD17F50A6 ON voting_platform_candidate');
        $this->addSql('DROP INDEX UNIQ_2C1A353AD17F50A6 ON voting_platform_candidate_group');
        $this->addSql('DROP INDEX UNIQ_7249D537D17F50A6 ON voting_platform_candidate_group_result');
        $this->addSql('DROP INDEX UNIQ_4E144C94D17F50A6 ON voting_platform_election');
        $this->addSql('DROP INDEX UNIQ_13C1C73FD17F50A6 ON voting_platform_election_pool_result');
        $this->addSql('DROP INDEX UNIQ_67EFA0E4D17F50A6 ON voting_platform_election_result');
        $this->addSql('DROP INDEX UNIQ_F15D87B7D17F50A6 ON voting_platform_election_round');
        $this->addSql('DROP INDEX UNIQ_F2670966D17F50A6 ON voting_platform_election_round_result');
        $this->addSql('ALTER TABLE web_hooks RENAME INDEX uniq_cdb836add17f50a6 TO web_hook_uuid_unique');
    }
}
