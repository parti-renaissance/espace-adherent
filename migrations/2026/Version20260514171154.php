<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260514171154 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_activation_keys
                CHANGE
                  adherent_uuid adherent_uuid CHAR(36) NOT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql('ALTER TABLE adherent_activity CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE adherent_certification_histories CHANGE date date DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_change_email_token
                CHANGE
                  adherent_uuid adherent_uuid CHAR(36) NOT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_declared_mandate_history
                CHANGE
                  added_mandates added_mandates LONGTEXT DEFAULT NULL,
                CHANGE
                  removed_mandates removed_mandates LONGTEXT DEFAULT NULL,
                CHANGE
                  date date DATETIME NOT NULL,
                CHANGE
                  notified_at notified_at DATETIME DEFAULT NULL,
                CHANGE
                  telegram_notified_at telegram_notified_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_email_subscription_histories
                CHANGE
                  adherent_uuid adherent_uuid CHAR(36) NOT NULL,
                CHANGE
                  date date DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE adherent_formation CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE adherent_mandate CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE adherent_message_filters CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_statistics
                CHANGE
                  unique_opens_app_rate unique_opens_app_rate DOUBLE PRECISION DEFAULT '0' NOT NULL,
                CHANGE
                  unique_opens_email_rate unique_opens_email_rate DOUBLE PRECISION DEFAULT '0' NOT NULL,
                CHANGE
                  unique_opens_notification_rate unique_opens_notification_rate DOUBLE PRECISION DEFAULT '0' NOT NULL,
                CHANGE
                  unique_opens_rate unique_opens_rate DOUBLE PRECISION DEFAULT '0' NOT NULL,
                CHANGE
                  unique_clicks_app_rate unique_clicks_app_rate DOUBLE PRECISION DEFAULT '0' NOT NULL,
                CHANGE
                  unique_clicks_email_rate unique_clicks_email_rate DOUBLE PRECISION DEFAULT '0' NOT NULL,
                CHANGE
                  unique_clicks_rate unique_clicks_rate DOUBLE PRECISION DEFAULT '0' NOT NULL,
                CHANGE
                  unsubscribed_rate unsubscribed_rate DOUBLE PRECISION DEFAULT '0' NOT NULL
            SQL);
        $this->addSql('ALTER TABLE adherent_messages CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_request
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  email_hash email_hash CHAR(36) DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE adherent_request_reminder CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_reset_password_tokens
                CHANGE
                  adherent_uuid adherent_uuid CHAR(36) NOT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_segment
                CHANGE
                  member_ids member_ids LONGTEXT NOT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql('ALTER TABLE adherent_zone_based_role CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherents
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL,
                CHANGE
                  interests interests LONGTEXT DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  mandates mandates LONGTEXT DEFAULT NULL,
                CHANGE
                  tags tags LONGTEXT DEFAULT NULL,
                CHANGE
                  finished_adhesion_steps finished_adhesion_steps LONGTEXT DEFAULT NULL,
                CHANGE
                  mailchimp_last_synced_at mailchimp_last_synced_at DATETIME DEFAULT NULL,
                CHANGE
                  mailchimp_last_failed_at mailchimp_last_failed_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  administrator_action_history
                CHANGE
                  telegram_notified_at telegram_notified_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE administrator_role_history CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE agora CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE agora_membership CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE algolia_je_mengage_timeline_feed CHANGE object_id object_id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE app_alert CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_hit
                CHANGE
                  activity_session_uuid activity_session_uuid CHAR(36) NOT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql('ALTER TABLE app_session CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE app_session_push_token_link CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  audience
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  roles roles LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE audience_segment CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  audience_snapshot
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  roles roles LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE banned_adherent CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE campus_registration CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE certification_request CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE chatbot CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE chatbot_message CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE chatbot_run CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE chatbot_thread CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE command_history CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE committee_candidacies_group CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE committee_candidacy CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE committee_candidacy_invitation CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE committee_election CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committees
                CHANGE
                  created_by created_by CHAR(36) DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committees_membership_histories
                CHANGE
                  adherent_uuid adherent_uuid CHAR(36) NOT NULL,
                CHANGE
                  date date DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE committees_memberships CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE consultation CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  contact
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL,
                CHANGE
                  interests interests LONGTEXT DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE contribution CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE contribution_payment CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE contribution_revenue_declaration CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE coordinator_managed_areas CHANGE codes codes LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE department_site CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  designation
                CHANGE
                  global_zones global_zones LONGTEXT DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  pools pools LONGTEXT DEFAULT NULL,
                CHANGE
                  result_schedule_delay result_schedule_delay DOUBLE PRECISION DEFAULT '0' NOT NULL,
                CHANGE
                  election_entity_identifier election_entity_identifier CHAR(36) DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE designation_candidacy_pool CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE designation_candidacy_pool_candidacy CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE designation_poll CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE designation_poll_question CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE designation_poll_question_choice CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE devices CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE document CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  donation_transactions
                CHANGE
                  paybox_date_time paybox_date_time DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  donations
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE donators CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative
                CHANGE
                  contact_phone contact_phone VARCHAR(35) DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql('ALTER TABLE elected_representative_mandate CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE elected_representative_payment CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE elected_representative_revenue_declaration CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  email_templates
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  scopes scopes LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  emails
                CHANGE
                  recipients recipients LONGTEXT DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  events
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  interests interests LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  events_invitations
                CHANGE
                  guests guests LONGTEXT NOT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql('ALTER TABLE events_registrations CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE facebook_profiles CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE failed_login_attempt CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE filesystem_file CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE general_convention CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE general_meeting_report CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_borough
                CHANGE
                  postal_code postal_code LONGTEXT DEFAULT NULL,
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_canton
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_city
                CHANGE
                  postal_code postal_code LONGTEXT DEFAULT NULL,
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_city_community
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_consular_district
                CHANGE
                  cities cities LONGTEXT NOT NULL,
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_country
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_custom_zone
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE geo_data CHANGE geo_shape geo_shape GEOMETRY NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_department
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_district
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_foreign_district
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_region
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_vote_place
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_zone
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  postal_code postal_code LONGTEXT DEFAULT NULL,
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  tags tags LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE image CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE internal_api_application CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE invalid_email_address CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE invitations CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE jecoute_data_survey CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE jecoute_managed_areas CHANGE codes codes LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_news CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE jecoute_region CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE jecoute_resource_link CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE jecoute_riposte CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_survey
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  tags tags LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE jecoute_survey_question CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jemarche_data_survey
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  legislative_newsletter_subscription
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  token token CHAR(36) NOT NULL
            SQL);
        $this->addSql('ALTER TABLE live_stream CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE local_election CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE local_election_candidacy CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE local_election_substitute_candidacy CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_static_segment
                CHANGE
                  built_at built_at DATETIME DEFAULT NULL,
                CHANGE
                  build_started_at build_started_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_static_segment_member
                CHANGE
                  processed_at processed_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE my_team CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  my_team_delegated_access
                CHANGE
                  accesses accesses LONGTEXT DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  scope_features scope_features LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  my_team_member
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  scope_features scope_features LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE national_event CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  ticket_uuid ticket_uuid CHAR(36) NOT NULL,
                CHANGE
                  emergency_contact_phone emergency_contact_phone VARCHAR(35) DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE national_event_inscription_payment CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE national_event_inscription_scan CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  newsletter_subscriptions
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  token token CHAR(36) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  notification
                CHANGE
                  tokens tokens LONGTEXT DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_access_tokens
                CHANGE
                  expires_at expires_at DATETIME NOT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_auth_codes
                CHANGE
                  expires_at expires_at DATETIME NOT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_clients
                CHANGE
                  allowed_grant_types allowed_grant_types LONGTEXT NOT NULL,
                CHANGE
                  supported_scopes supported_scopes LONGTEXT DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  requested_roles requested_roles LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_refresh_tokens
                CHANGE
                  expires_at expires_at DATETIME NOT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql('ALTER TABLE ohme_contact CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_address
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  postal_codes postal_codes LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE pap_building CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE pap_building_block CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE pap_building_block_statistics CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE pap_building_event CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE pap_building_statistics CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE pap_campaign CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE pap_campaign_history CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE pap_floor CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_floor_statistics
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  visited_doors visited_doors LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_vote_place
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql('ALTER TABLE pap_voter CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  petition_signature
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql('ALTER TABLE phoning_campaign CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE phoning_campaign_history CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE poll CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE poll_choice CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE procuration_elections CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE procuration_initial_requests CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxies
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  zone_ids zone_ids LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE procuration_proxy_action CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE procuration_proxy_slot CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE procuration_proxy_slot_action CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE procuration_request_action CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE procuration_request_slot CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE procuration_request_slot_action CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_requests
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  zone_ids zone_ids LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE procuration_rounds CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  projection_managed_users
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL,
                CHANGE
                  interests interests LONGTEXT DEFAULT NULL,
                CHANGE
                  supervisor_tags supervisor_tags LONGTEXT DEFAULT NULL,
                CHANGE
                  subscription_types subscription_types LONGTEXT DEFAULT NULL,
                CHANGE
                  adherent_uuid adherent_uuid CHAR(36) DEFAULT NULL,
                CHANGE
                  committee_uuids committee_uuids LONGTEXT DEFAULT NULL,
                CHANGE
                  committee_uuid committee_uuid CHAR(36) DEFAULT NULL,
                CHANGE
                  tags tags LONGTEXT DEFAULT NULL,
                CHANGE
                  cotisation_dates cotisation_dates LONGTEXT DEFAULT NULL,
                CHANGE
                  elect_mandates elect_mandates LONGTEXT DEFAULT NULL,
                CHANGE
                  declared_mandates declared_mandates LONGTEXT DEFAULT NULL,
                CHANGE
                  agora_uuid agora_uuid CHAR(36) DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE push_notification CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE push_token CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE qr_code CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  referral
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE renaissance_newsletter_source CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  renaissance_newsletter_subscription
                CHANGE
                  token token CHAR(36) NOT NULL,
                CHANGE
                  uuid uuid CHAR(36) NOT NULL
            SQL);
        $this->addSql('ALTER TABLE reports CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  scope
                CHANGE
                  features features LONGTEXT DEFAULT NULL,
                CHANGE
                  apps apps LONGTEXT DEFAULT NULL,
                CHANGE
                  canary_features canary_features LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  sms_opt_out
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  cancelled_at cancelled_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE tally_form CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE tax_receipt CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE team CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE team_member CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE team_member_history CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE timeline_item_private_message CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE transactional_email_template CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  unregistrations
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  reasons reasons JSON DEFAULT NULL,
                CHANGE
                  email_hash email_hash CHAR(36) NOT NULL,
                CHANGE
                  tags tags LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  uploadable_file
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  file_dimensions file_dimensions LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  user_action_history
                CHANGE
                  telegram_notified_at telegram_notified_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE user_authorizations CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE user_documents CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_candidate CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_candidate_group CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_candidate_group_result CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_election CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_election_pool_result CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_election_result CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_election_round CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_election_round_result CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  vox_action
                CHANGE
                  uuid uuid CHAR(36) NOT NULL,
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL,
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE vox_action_participant CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  messenger_messages
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  available_at available_at DATETIME NOT NULL,
                CHANGE
                  delivered_at delivered_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE rememberme_token CHANGE lastUsed lastUsed DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_activation_keys
                CHANGE
                  adherent_uuid adherent_uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE adherent_activity CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_certification_histories
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_change_email_token
                CHANGE
                  adherent_uuid adherent_uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_declared_mandate_history
                CHANGE
                  added_mandates added_mandates LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  removed_mandates removed_mandates LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  notified_at notified_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  telegram_notified_at telegram_notified_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_email_subscription_histories
                CHANGE
                  adherent_uuid adherent_uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql('ALTER TABLE adherent_formation CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE adherent_mandate CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_filters
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_statistics
                CHANGE
                  unique_opens_app_rate unique_opens_app_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                CHANGE
                  unique_opens_email_rate unique_opens_email_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                CHANGE
                  unique_opens_notification_rate unique_opens_notification_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                CHANGE
                  unique_opens_rate unique_opens_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                CHANGE
                  unique_clicks_app_rate unique_clicks_app_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                CHANGE
                  unique_clicks_email_rate unique_clicks_email_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                CHANGE
                  unique_clicks_rate unique_clicks_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                CHANGE
                  unsubscribed_rate unsubscribed_rate DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL
            SQL);
        $this->addSql('ALTER TABLE adherent_messages CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_request
                CHANGE
                  email_hash email_hash CHAR(36) DEFAULT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_request_reminder
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_reset_password_tokens
                CHANGE
                  adherent_uuid adherent_uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_segment
                CHANGE
                  member_ids member_ids LONGTEXT NOT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_zone_based_role
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherents
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL COMMENT '(DC2Type:phone_number)',
                CHANGE
                  interests interests LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  mandates mandates LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  mailchimp_last_synced_at mailchimp_last_synced_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  mailchimp_last_failed_at mailchimp_last_failed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  tags tags LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  finished_adhesion_steps finished_adhesion_steps LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  administrator_action_history
                CHANGE
                  telegram_notified_at telegram_notified_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  administrator_role_history
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql('ALTER TABLE agora CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE agora_membership CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  algolia_je_mengage_timeline_feed
                CHANGE
                  object_id object_id CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE app_alert CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_hit
                CHANGE
                  activity_session_uuid activity_session_uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE app_session CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_session_push_token_link
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  audience
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  roles roles LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'
            SQL);
        $this->addSql('ALTER TABLE audience_segment CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  audience_snapshot
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  roles roles LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'
            SQL);
        $this->addSql('ALTER TABLE banned_adherent CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE campus_registration CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE certification_request CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE chatbot CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE chatbot_message CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE chatbot_run CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE chatbot_thread CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  command_history
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committee_candidacies_group
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE committee_candidacy CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committee_candidacy_invitation
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE committee_election CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committees
                CHANGE
                  created_by created_by CHAR(36) DEFAULT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL COMMENT '(DC2Type:phone_number)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committees_membership_histories
                CHANGE
                  adherent_uuid adherent_uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql('ALTER TABLE committees_memberships CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE consultation CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  contact
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL COMMENT '(DC2Type:phone_number)',
                CHANGE
                  interests interests LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql('ALTER TABLE contribution CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE contribution_payment CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  contribution_revenue_declaration
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  coordinator_managed_areas
                CHANGE
                  codes codes LONGTEXT NOT NULL COMMENT '(DC2Type:simple_array)'
            SQL);
        $this->addSql('ALTER TABLE department_site CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  designation
                CHANGE
                  global_zones global_zones LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  result_schedule_delay result_schedule_delay DOUBLE PRECISION UNSIGNED DEFAULT '0' NOT NULL,
                CHANGE
                  pools pools LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  election_entity_identifier election_entity_identifier CHAR(36) DEFAULT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  designation_candidacy_pool
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  designation_candidacy_pool_candidacy
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE designation_poll CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  designation_poll_question
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  designation_poll_question_choice
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE devices CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE document CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  donation_transactions
                CHANGE
                  paybox_date_time paybox_date_time DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  donations
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL COMMENT '(DC2Type:phone_number)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql('ALTER TABLE donators CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative
                CHANGE
                  contact_phone contact_phone VARCHAR(35) DEFAULT NULL COMMENT '(DC2Type:phone_number)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_mandate
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_payment
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_revenue_declaration
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  email_templates
                CHANGE
                  scopes scopes LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  emails
                CHANGE
                  recipients recipients LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  `events`
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  interests interests LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  events_invitations
                CHANGE
                  guests guests LONGTEXT NOT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE events_registrations CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE facebook_profiles CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE failed_login_attempt CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE filesystem_file CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE general_convention CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE general_meeting_report CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_borough
                CHANGE
                  postal_code postal_code LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_canton
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_city
                CHANGE
                  postal_code postal_code LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_city_community
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_consular_district
                CHANGE
                  cities cities LONGTEXT NOT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_country
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_custom_zone
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql('ALTER TABLE geo_data CHANGE geo_shape geo_shape GEOMETRY NOT NULL COMMENT \'(DC2Type:geometry)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_department
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_district
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_foreign_district
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_region
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_vote_place
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_zone
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  postal_code postal_code LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  tags tags LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql('ALTER TABLE image CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  internal_api_application
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE invalid_email_address CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE invitations CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE jecoute_data_survey CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_managed_areas
                CHANGE
                  codes codes LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'
            SQL);
        $this->addSql('ALTER TABLE jecoute_news CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE jecoute_region CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE jecoute_resource_link CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE jecoute_riposte CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_survey
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  tags tags LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_survey_question
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jemarche_data_survey
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  legislative_newsletter_subscription
                CHANGE
                  token token CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE live_stream CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE local_election CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  local_election_candidacy
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  local_election_substitute_candidacy
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_static_segment
                CHANGE
                  build_started_at build_started_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  built_at built_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_static_segment_member
                CHANGE
                  processed_at processed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  messenger_messages
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  available_at available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  delivered_at delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql('ALTER TABLE my_team CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  my_team_delegated_access
                CHANGE
                  accesses accesses LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  scope_features scope_features LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  my_team_member
                CHANGE
                  scope_features scope_features LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE national_event CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL COMMENT '(DC2Type:phone_number)',
                CHANGE
                  ticket_uuid ticket_uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  emergency_contact_phone emergency_contact_phone VARCHAR(35) DEFAULT NULL COMMENT '(DC2Type:phone_number)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_scan
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  newsletter_subscriptions
                CHANGE
                  token token CHAR(36) DEFAULT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  notification
                CHANGE
                  tokens tokens LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_access_tokens
                CHANGE
                  expires_at expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_auth_codes
                CHANGE
                  expires_at expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_clients
                CHANGE
                  allowed_grant_types allowed_grant_types LONGTEXT NOT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  supported_scopes supported_scopes LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  requested_roles requested_roles LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_refresh_tokens
                CHANGE
                  expires_at expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE ohme_contact CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_address
                CHANGE
                  postal_codes postal_codes LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE pap_building CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE pap_building_block CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_building_block_statistics
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE pap_building_event CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_building_statistics
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE pap_campaign CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE pap_campaign_history CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE pap_floor CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_floor_statistics
                CHANGE
                  visited_doors visited_doors LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_vote_place
                CHANGE
                  latitude latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  longitude longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE pap_voter CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  petition_signature
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL COMMENT '(DC2Type:phone_number)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE phoning_campaign CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  phoning_campaign_history
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE poll CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE poll_choice CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE procuration_elections CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_initial_requests
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxies
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL COMMENT '(DC2Type:phone_number)',
                CHANGE
                  zone_ids zone_ids LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxy_action
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE procuration_proxy_slot CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxy_slot_action
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_action
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_slot
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_slot_action
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_requests
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL COMMENT '(DC2Type:phone_number)',
                CHANGE
                  zone_ids zone_ids LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql('ALTER TABLE procuration_rounds CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  projection_managed_users
                CHANGE
                  adherent_uuid adherent_uuid CHAR(36) DEFAULT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL COMMENT '(DC2Type:phone_number)',
                CHANGE
                  committee_uuids committee_uuids LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  tags tags LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  interests interests LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  supervisor_tags supervisor_tags LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  subscription_types subscription_types LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  committee_uuid committee_uuid CHAR(36) DEFAULT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  agora_uuid agora_uuid CHAR(36) DEFAULT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  declared_mandates declared_mandates LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  elect_mandates elect_mandates LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  cotisation_dates cotisation_dates LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'
            SQL);
        $this->addSql('ALTER TABLE push_notification CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE push_token CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE qr_code CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  referral
                CHANGE
                  phone phone VARCHAR(35) DEFAULT NULL COMMENT '(DC2Type:phone_number)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  rememberme_token
                CHANGE
                  lastUsed lastUsed DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  renaissance_newsletter_source
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  renaissance_newsletter_subscription
                CHANGE
                  token token CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql('ALTER TABLE reports CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  scope
                CHANGE
                  features features LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  canary_features canary_features LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  apps apps LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  sms_opt_out
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  cancelled_at cancelled_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql('ALTER TABLE tally_form CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE tax_receipt CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE team CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE team_member CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  team_member_history
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  timeline_item_private_message
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  transactional_email_template
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  unregistrations
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  email_hash email_hash CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  tags tags LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                CHANGE
                  reasons reasons JSON DEFAULT NULL COMMENT '(DC2Type:json)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  uploadable_file
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  file_dimensions file_dimensions LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  user_action_history
                CHANGE
                  telegram_notified_at telegram_notified_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql('ALTER TABLE user_authorizations CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_documents CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  voting_platform_candidate
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  voting_platform_candidate_group
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  voting_platform_candidate_group_result
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  voting_platform_election
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  voting_platform_election_pool_result
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  voting_platform_election_result
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  voting_platform_election_round
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  voting_platform_election_round_result
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  vox_action
                CHANGE
                  uuid uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                CHANGE
                  address_latitude address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
                CHANGE
                  address_longitude address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT '(DC2Type:geo_point)'
            SQL);
        $this->addSql('ALTER TABLE vox_action_participant CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }
}
