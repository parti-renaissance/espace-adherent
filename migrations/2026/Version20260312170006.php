<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312170006 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_activation_code
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  expired_at expired_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  used_at used_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  revoked_at revoked_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_activation_keys
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  expired_at expired_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  used_at used_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_change_email_token
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  expired_at expired_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  used_at used_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_charter
                CHANGE
                  accepted_at accepted_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_formation
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_mandate
                CHANGE
                  begin_at begin_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  finish_at finish_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_filters
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_reach
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  sent_at sent_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_request
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  account_created_at account_created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_request_reminder
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_reset_password_tokens
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  expired_at expired_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  used_at used_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_zone_based_role
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherents
                CHANGE
                  registered_at registered_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  activated_at activated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  last_logged_at last_logged_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  email_unsubscribed_at email_unsubscribed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  certified_at certified_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  membership_reminded_at membership_reminded_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  phone_verified_at phone_verified_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  contributed_at contributed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  resubscribe_email_sent_at resubscribe_email_sent_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  unsubscribe_requested_at unsubscribe_requested_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  administrator_action_history
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  administrator_export_history
                CHANGE
                  exported_at exported_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  agora
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  agora_membership
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_alert
                CHANGE
                  begin_at begin_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  end_at end_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_hit
                CHANGE
                  app_date app_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_session
                CHANGE
                  last_activity_date last_activity_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  unsubscribed_at unsubscribed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_session_push_token_link
                CHANGE
                  last_activity_date last_activity_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  unsubscribed_at unsubscribed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  audience
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  audience_snapshot
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  banned_adherent
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  campus_registration
                CHANGE
                  registered_at registered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  certification_request
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  processed_at processed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot_message
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot_run
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot_thread
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  cms_block
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committee_candidacies_group
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committee_candidacy
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committee_candidacy_invitation
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committees
                CHANGE
                  approved_at approved_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  refused_at refused_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  closed_at closed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committees_memberships
                CHANGE
                  joined_at joined_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  consultation
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  contact
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  processed_at processed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  interests_updated_at interests_updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  contribution
                CHANGE
                  start_date start_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  end_date end_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  contribution_payment
                CHANGE
                  date date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  contribution_revenue_declaration
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  custom_search_results
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  department_site
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  designation
                CHANGE
                  candidacy_start_date candidacy_start_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  candidacy_end_date candidacy_end_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  vote_start_date vote_start_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  vote_end_date vote_end_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  election_creation_date election_creation_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  alert_begin_at alert_begin_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  account_creation_deadline account_creation_deadline DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  membership_deadline membership_deadline DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  designation_candidacy_pool_candidacies_group
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  designation_candidacy_pool_candidacy
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  designation_poll
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  devices
                CHANGE
                  last_logged_at last_logged_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  document
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  donations
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  subscription_ended_at subscription_ended_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  donated_at donated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  last_success_date last_success_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  donators
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative
                CHANGE
                  email_unsubscribed_at email_unsubscribed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  contributed_at contributed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_contribution
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  start_date start_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  end_date end_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_payment
                CHANGE
                  date date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_revenue_declaration
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  email_templates
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  emails
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  delivered_at delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  events
                CHANGE
                  begin_at begin_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  finish_at finish_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  push_sent_at push_sent_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  events_invitations
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  events_registrations
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  confirmed_at confirmed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  facebook_profiles
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  facebook_videos
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  failed_login_attempt
                CHANGE
                  at at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  filesystem_file
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  general_convention
                CHANGE
                  reported_at reported_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  general_meeting_report
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_borough
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_canton
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_city
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_city_community
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_consular_district
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_country
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_custom_zone
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_department
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_district
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_foreign_district
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_region
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_vote_place
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_zone
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  hub_item
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  image
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  invalid_email_address
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  invitations
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_data_survey
                CHANGE
                  posted_at posted_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_news
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_region
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_resource_link
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_riposte
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_survey
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jemarche_data_survey
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jemengage_header_blocks
                CHANGE
                  deadline_date deadline_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  legislative_newsletter_subscription
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  confirmed_at confirmed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  live_stream
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  local_election_candidacies_group
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  local_election_candidacy
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  local_election_substitute_candidacy
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_campaign
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_campaign_report
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  medias
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mooc
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mooc_attachment_link
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mooc_chapter
                CHANGE
                  published_at published_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mooc_elements
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  moodle_user
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  moodle_user_job
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  my_team
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  my_team_delegated_access
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  my_team_member
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event
                CHANGE
                  start_date start_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  end_date end_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  ticket_start_date ticket_start_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  ticket_end_date ticket_end_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  inscription_edit_deadline inscription_edit_deadline DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                CHANGE
                  birthdate birthdate DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  ticket_sent_at ticket_sent_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  confirmed_at confirmed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  push_sent_at push_sent_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  canceled_at canceled_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  confirmation_sent_at confirmation_sent_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  expired_checked_at expired_checked_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment_status
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_reminder
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_scan
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  newsletter_subscriptions
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  deleted_at deleted_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  confirmed_at confirmed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  notification
                CHANGE
                  delivered_at delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_access_tokens
                CHANGE
                  revoked_at revoked_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_auth_codes
                CHANGE
                  revoked_at revoked_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_clients
                CHANGE
                  deleted_at deleted_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_refresh_tokens
                CHANGE
                  revoked_at revoked_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  ohme_contact
                CHANGE
                  ohme_created_at ohme_created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  ohme_updated_at ohme_updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  last_payment_date last_payment_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_building_block
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_building_block_statistics
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  closed_at closed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_building_event
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_building_statistics
                CHANGE
                  last_passage last_passage DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  closed_at closed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_campaign
                CHANGE
                  begin_at begin_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  finish_at finish_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_campaign_history
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  finish_at finish_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  begin_at begin_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_floor
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_floor_statistics
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  closed_at closed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  petition_signature
                CHANGE
                  validated_at validated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  reminded_at reminded_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  phoning_campaign
                CHANGE
                  finish_at finish_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  phoning_campaign_history
                CHANGE
                  begin_at begin_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  finish_at finish_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  poll
                CHANGE
                  finish_at finish_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  poll_choice
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  poll_vote
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_elections
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_initial_requests
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  reminded_at reminded_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_matching_history
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxies
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxy_action
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxy_slot
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxy_slot_action
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_action
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_slot
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  match_reminded_at match_reminded_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_slot_action
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_requests
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_rounds
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  projection_managed_users
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  certified_at certified_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  activated_at activated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  last_membership_donation last_membership_donation DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  campus_registered_at campus_registered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  resubscribe_email_sent_at resubscribe_email_sent_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  first_membership_donation first_membership_donation DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  last_logged_at last_logged_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  proposals
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  deleted_at deleted_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  push_token
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  last_activity_date last_activity_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  unsubscribed_at unsubscribed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  qr_code
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  redirections
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  referral
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  reported_at reported_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  renaissance_newsletter_subscription
                CHANGE
                  confirmed_at confirmed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  reports
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  resolved_at resolved_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  republican_silence
                CHANGE
                  begin_at begin_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  finish_at finish_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  social_shares
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  tax_receipt
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  team
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  team_member
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  timeline_item_private_message
                CHANGE
                  notification_sent_at notification_sent_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  transactional_email_template
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  unregistrations
                CHANGE
                  registered_at registered_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  unregistered_at unregistered_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  uploadable_file
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  user_action_history
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  user_documents
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  voting_platform_election
                CHANGE
                  closed_at closed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  second_round_end_date second_round_end_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  canceled_at canceled_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  voting_platform_vote
                CHANGE
                  voted_at voted_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  voting_platform_vote_result
                CHANGE
                  voted_at voted_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  voting_platform_voter
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  vox_action
                CHANGE
                  date date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  canceled_at canceled_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  notified_at_first_notification notified_at_first_notification DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  notified_at_second_notification notified_at_second_notification DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  vox_action_participant
                CHANGE
                  created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE
                  updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_activation_code
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  expired_at expired_at DATETIME NOT NULL,
                CHANGE
                  used_at used_at DATETIME DEFAULT NULL,
                CHANGE
                  revoked_at revoked_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_activation_keys
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  expired_at expired_at DATETIME NOT NULL,
                CHANGE
                  used_at used_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_change_email_token
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  expired_at expired_at DATETIME NOT NULL,
                CHANGE
                  used_at used_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE adherent_charter CHANGE accepted_at accepted_at DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_formation
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_mandate
                CHANGE
                  begin_at begin_at DATETIME NOT NULL,
                CHANGE
                  finish_at finish_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_filters
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE adherent_message_reach CHANGE date date DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                CHANGE
                  sent_at sent_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_request
                CHANGE
                  account_created_at account_created_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_request_reminder
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_reset_password_tokens
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  expired_at expired_at DATETIME NOT NULL,
                CHANGE
                  used_at used_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_zone_based_role
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherents
                CHANGE
                  phone_verified_at phone_verified_at DATETIME DEFAULT NULL,
                CHANGE
                  registered_at registered_at DATETIME NOT NULL,
                CHANGE
                  activated_at activated_at DATETIME DEFAULT NULL,
                CHANGE
                  membership_reminded_at membership_reminded_at DATETIME DEFAULT NULL,
                CHANGE
                  updated_at updated_at DATETIME DEFAULT NULL,
                CHANGE
                  last_logged_at last_logged_at DATETIME DEFAULT NULL,
                CHANGE
                  resubscribe_email_sent_at resubscribe_email_sent_at DATETIME DEFAULT NULL,
                CHANGE
                  email_unsubscribed_at email_unsubscribed_at DATETIME DEFAULT NULL,
                CHANGE
                  unsubscribe_requested_at unsubscribe_requested_at DATETIME DEFAULT NULL,
                CHANGE
                  certified_at certified_at DATETIME DEFAULT NULL,
                CHANGE
                  contributed_at contributed_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE administrator_action_history CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE administrator_export_history CHANGE exported_at exported_at DATETIME DEFAULT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  agora
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  agora_membership
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_alert
                CHANGE
                  begin_at begin_at DATETIME NOT NULL,
                CHANGE
                  end_at end_at DATETIME NOT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_hit
                CHANGE
                  app_date app_date DATETIME NOT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_session
                CHANGE
                  last_activity_date last_activity_date DATETIME DEFAULT NULL,
                CHANGE
                  unsubscribed_at unsubscribed_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_session_push_token_link
                CHANGE
                  last_activity_date last_activity_date DATETIME DEFAULT NULL,
                CHANGE
                  unsubscribed_at unsubscribed_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  audience
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  audience_snapshot
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE banned_adherent CHANGE date date DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  campus_registration
                CHANGE
                  registered_at registered_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  certification_request
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  processed_at processed_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot_message
                CHANGE
                  date date DATETIME NOT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot_run
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot_thread
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  cms_block
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committee_candidacies_group
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committee_candidacy
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committee_candidacy_invitation
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committees
                CHANGE
                  approved_at approved_at DATETIME DEFAULT NULL,
                CHANGE
                  refused_at refused_at DATETIME DEFAULT NULL,
                CHANGE
                  closed_at closed_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE committees_memberships CHANGE joined_at joined_at DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  consultation
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  contact
                CHANGE
                  interests_updated_at interests_updated_at DATETIME DEFAULT NULL,
                CHANGE
                  processed_at processed_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  contribution
                CHANGE
                  start_date start_date DATETIME DEFAULT NULL,
                CHANGE
                  end_date end_date DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  contribution_payment
                CHANGE
                  date date DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  contribution_revenue_declaration
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  custom_search_results
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  department_site
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  designation
                CHANGE
                  candidacy_start_date candidacy_start_date DATETIME DEFAULT NULL,
                CHANGE
                  candidacy_end_date candidacy_end_date DATETIME DEFAULT NULL,
                CHANGE
                  election_creation_date election_creation_date DATETIME DEFAULT NULL,
                CHANGE
                  account_creation_deadline account_creation_deadline DATETIME DEFAULT NULL,
                CHANGE
                  membership_deadline membership_deadline DATETIME DEFAULT NULL,
                CHANGE
                  vote_start_date vote_start_date DATETIME DEFAULT NULL,
                CHANGE
                  vote_end_date vote_end_date DATETIME DEFAULT NULL,
                CHANGE
                  alert_begin_at alert_begin_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  designation_candidacy_pool_candidacies_group
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  designation_candidacy_pool_candidacy
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  designation_poll
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  devices
                CHANGE
                  last_logged_at last_logged_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  document
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  donations
                CHANGE
                  donated_at donated_at DATETIME NOT NULL,
                CHANGE
                  last_success_date last_success_date DATETIME DEFAULT NULL,
                CHANGE
                  subscription_ended_at subscription_ended_at DATETIME DEFAULT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  donators
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative
                CHANGE
                  email_unsubscribed_at email_unsubscribed_at DATETIME DEFAULT NULL,
                CHANGE
                  contributed_at contributed_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_contribution
                CHANGE
                  start_date start_date DATETIME DEFAULT NULL,
                CHANGE
                  end_date end_date DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_payment
                CHANGE
                  date date DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_revenue_declaration
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  email_templates
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  emails
                CHANGE
                  delivered_at delivered_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  `events`
                CHANGE
                  begin_at begin_at DATETIME NOT NULL,
                CHANGE
                  finish_at finish_at DATETIME NOT NULL,
                CHANGE
                  push_sent_at push_sent_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE events_invitations CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  events_registrations
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  confirmed_at confirmed_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  facebook_profiles
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  facebook_videos
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE failed_login_attempt CHANGE at at DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  filesystem_file
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  general_convention
                CHANGE
                  reported_at reported_at DATETIME NOT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  general_meeting_report
                CHANGE
                  date date DATETIME NOT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_borough
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_canton
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_city
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_city_community
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_consular_district
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_country
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_custom_zone
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_department
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_district
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_foreign_district
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_region
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_vote_place
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  geo_zone
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  hub_item
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  image
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  invalid_email_address
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE invitations CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE jecoute_data_survey CHANGE posted_at posted_at DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_news
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_region
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_resource_link
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_riposte
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_survey
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jemarche_data_survey
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jemengage_header_blocks
                CHANGE
                  deadline_date deadline_date DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  legislative_newsletter_subscription
                CHANGE
                  confirmed_at confirmed_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  live_stream
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  local_election_candidacies_group
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  local_election_candidacy
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  local_election_substitute_candidacy
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_campaign
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_campaign_report
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  medias
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mooc
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mooc_attachment_link
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE mooc_chapter CHANGE published_at published_at DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mooc_elements
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  moodle_user
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  moodle_user_job
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  my_team
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  my_team_delegated_access
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  my_team_member
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event
                CHANGE
                  start_date start_date DATETIME NOT NULL,
                CHANGE
                  end_date end_date DATETIME NOT NULL,
                CHANGE
                  ticket_start_date ticket_start_date DATETIME NOT NULL,
                CHANGE
                  ticket_end_date ticket_end_date DATETIME NOT NULL,
                CHANGE
                  inscription_edit_deadline inscription_edit_deadline DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                CHANGE
                  birthdate birthdate DATE DEFAULT NULL,
                CHANGE
                  confirmed_at confirmed_at DATETIME DEFAULT NULL,
                CHANGE
                  canceled_at canceled_at DATETIME DEFAULT NULL,
                CHANGE
                  ticket_sent_at ticket_sent_at DATETIME DEFAULT NULL,
                CHANGE
                  confirmation_sent_at confirmation_sent_at DATETIME DEFAULT NULL,
                CHANGE
                  push_sent_at push_sent_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment
                CHANGE
                  expired_checked_at expired_checked_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment_status
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_reminder
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_scan
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  newsletter_subscriptions
                CHANGE
                  confirmed_at confirmed_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL,
                CHANGE
                  deleted_at deleted_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  notification
                CHANGE
                  delivered_at delivered_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_access_tokens
                CHANGE
                  revoked_at revoked_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_auth_codes
                CHANGE
                  revoked_at revoked_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_clients
                CHANGE
                  deleted_at deleted_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_refresh_tokens
                CHANGE
                  revoked_at revoked_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  ohme_contact
                CHANGE
                  last_payment_date last_payment_date DATETIME DEFAULT NULL,
                CHANGE
                  ohme_created_at ohme_created_at DATETIME DEFAULT NULL,
                CHANGE
                  ohme_updated_at ohme_updated_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_building_block
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_building_block_statistics
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL,
                CHANGE
                  closed_at closed_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE pap_building_event CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_building_statistics
                CHANGE
                  last_passage last_passage DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL,
                CHANGE
                  closed_at closed_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_campaign
                CHANGE
                  begin_at begin_at DATETIME DEFAULT NULL,
                CHANGE
                  finish_at finish_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_campaign_history
                CHANGE
                  begin_at begin_at DATETIME NOT NULL,
                CHANGE
                  finish_at finish_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_floor
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pap_floor_statistics
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL,
                CHANGE
                  closed_at closed_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  petition_signature
                CHANGE
                  validated_at validated_at DATETIME DEFAULT NULL,
                CHANGE
                  reminded_at reminded_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  phoning_campaign
                CHANGE
                  finish_at finish_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  phoning_campaign_history
                CHANGE
                  begin_at begin_at DATETIME NOT NULL,
                CHANGE
                  finish_at finish_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  poll
                CHANGE
                  finish_at finish_at DATETIME NOT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  poll_choice
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  poll_vote
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_elections
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_initial_requests
                CHANGE
                  reminded_at reminded_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE procuration_matching_history CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxies
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE procuration_proxy_action CHANGE date date DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxy_slot
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE procuration_proxy_slot_action CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE procuration_request_action CHANGE date date DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_slot
                CHANGE
                  match_reminded_at match_reminded_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE procuration_request_slot_action CHANGE date date DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_requests
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_rounds
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  projection_managed_users
                CHANGE
                  activated_at activated_at DATETIME DEFAULT NULL,
                CHANGE
                  last_logged_at last_logged_at DATETIME DEFAULT NULL,
                CHANGE
                  resubscribe_email_sent_at resubscribe_email_sent_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  certified_at certified_at DATETIME DEFAULT NULL,
                CHANGE
                  last_membership_donation last_membership_donation DATETIME DEFAULT NULL,
                CHANGE
                  first_membership_donation first_membership_donation DATETIME DEFAULT NULL,
                CHANGE
                  campus_registered_at campus_registered_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  proposals
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL,
                CHANGE
                  deleted_at deleted_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  push_token
                CHANGE
                  last_activity_date last_activity_date DATETIME DEFAULT NULL,
                CHANGE
                  unsubscribed_at unsubscribed_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  qr_code
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE redirections CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  referral
                CHANGE
                  reported_at reported_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  renaissance_newsletter_subscription
                CHANGE
                  confirmed_at confirmed_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  reports
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  resolved_at resolved_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  republican_silence
                CHANGE
                  begin_at begin_at DATETIME NOT NULL,
                CHANGE
                  finish_at finish_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  social_shares
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  tax_receipt
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  team
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  team_member
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  timeline_item_private_message
                CHANGE
                  notification_sent_at notification_sent_at DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  transactional_email_template
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  unregistrations
                CHANGE
                  registered_at registered_at DATETIME NOT NULL,
                CHANGE
                  unregistered_at unregistered_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  uploadable_file
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE user_action_history CHANGE date date DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  user_documents
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  voting_platform_election
                CHANGE
                  closed_at closed_at DATETIME DEFAULT NULL,
                CHANGE
                  canceled_at canceled_at DATETIME DEFAULT NULL,
                CHANGE
                  second_round_end_date second_round_end_date DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql('ALTER TABLE voting_platform_vote CHANGE voted_at voted_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_vote_result CHANGE voted_at voted_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_voter CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  vox_action
                CHANGE
                  date date DATETIME NOT NULL,
                CHANGE
                  canceled_at canceled_at DATETIME DEFAULT NULL,
                CHANGE
                  notified_at_first_notification notified_at_first_notification DATETIME DEFAULT NULL,
                CHANGE
                  notified_at_second_notification notified_at_second_notification DATETIME DEFAULT NULL,
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  vox_action_participant
                CHANGE
                  created_at created_at DATETIME NOT NULL,
                CHANGE
                  updated_at updated_at DATETIME NOT NULL
            SQL);
    }
}
