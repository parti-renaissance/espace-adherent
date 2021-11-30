<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211126020536 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if ($this->sm->tablesExist('adherents')) {
            return;
        }

        $this->addSql('CREATE TABLE adherent_activation_keys (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          value VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          expired_at DATETIME NOT NULL,
          used_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX adherent_activation_token_account_unique (value, adherent_uuid),
          UNIQUE INDEX adherent_activation_token_unique (value),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_adherent_tag (
          adherent_id INT UNSIGNED NOT NULL,
          adherent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_DD297F8225F06C53 (adherent_id),
          INDEX IDX_DD297F82AED03543 (adherent_tag_id),
          PRIMARY KEY(adherent_id, adherent_tag_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_certification_histories (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          administrator_id INT DEFAULT NULL,
          action VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX adherent_certification_histories_adherent_id_idx (adherent_id),
          INDEX adherent_certification_histories_administrator_id_idx (administrator_id),
          INDEX adherent_certification_histories_date_idx (date),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_change_email_token (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          value VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          expired_at DATETIME NOT NULL,
          used_at DATETIME DEFAULT NULL,
          email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_6F8B4B5AE7927C7477241BAC253ECC4 (email, used_at, expired_at),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_charter (
          id SMALLINT AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          accepted_at DATETIME NOT NULL,
          dtype VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_D6F94F2B25F06C53 (adherent_id),
          UNIQUE INDEX UNIQ_D6F94F2B25F06C5370AAEA5 (adherent_id, dtype),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_commitment (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          commitment_actions LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          debate_and_propose_ideas_actions LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          act_for_territory_actions LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          progressivism_actions LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          skills LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          availability VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_D239EF6F25F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_email_subscribe_token (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          adherent_uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          value VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          expired_at DATETIME NOT NULL,
          used_at DATETIME DEFAULT NULL,
          trigger_source VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_376DBA09DF5350C (created_by_administrator_id),
          INDEX IDX_376DBA0CF1918FF (updated_by_administrator_id),
          INDEX IDX_376DBA0F675F31B (author_id),
          UNIQUE INDEX UNIQ_376DBA01D775834 (value),
          UNIQUE INDEX UNIQ_376DBA01D7758346D804024 (value, adherent_uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_email_subscription_histories (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          subscription_type_id INT UNSIGNED NOT NULL,
          adherent_uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          action VARCHAR(32) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX adherent_email_subscription_histories_adherent_action_idx (action),
          INDEX adherent_email_subscription_histories_adherent_date_idx (date),
          INDEX adherent_email_subscription_histories_adherent_uuid_idx (adherent_uuid),
          INDEX IDX_51AD8354B6596C08 (subscription_type_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_email_subscription_history_referent_tag (
          email_subscription_history_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_6FFBE6E88FCB8132 (email_subscription_history_id),
          INDEX IDX_6FFBE6E89C262DB3 (referent_tag_id),
          PRIMARY KEY(
            email_subscription_history_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_instance_quality (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          instance_quality_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          territorial_council_id INT UNSIGNED DEFAULT NULL,
          date DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX adherent_instance_quality_unique (
            adherent_id, instance_quality_id
          ),
          INDEX IDX_D63B17FA25F06C53 (adherent_id),
          INDEX IDX_D63B17FA9F2C3FAB (zone_id),
          INDEX IDX_D63B17FAA623BBD7 (instance_quality_id),
          INDEX IDX_D63B17FAAAA61A99 (territorial_council_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_mandate (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          committee_id INT UNSIGNED DEFAULT NULL,
          territorial_council_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          gender VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          begin_at DATETIME NOT NULL,
          finish_at DATETIME DEFAULT NULL,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          quality VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_additionally_elected TINYINT(1) DEFAULT \'0\',
          reason VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          provisional TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_9C0C3D6025F06C53 (adherent_id),
          INDEX IDX_9C0C3D60AAA61A99 (territorial_council_id),
          INDEX IDX_9C0C3D60ED1A100B (committee_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_message_filters (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          referent_tag_id INT UNSIGNED DEFAULT NULL,
          committee_id INT UNSIGNED DEFAULT NULL,
          adherent_segment_id INT UNSIGNED DEFAULT NULL,
          territorial_council_id INT UNSIGNED DEFAULT NULL,
          political_committee_id INT UNSIGNED DEFAULT NULL,
          user_list_definition_id INT UNSIGNED DEFAULT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          cause_id INT UNSIGNED DEFAULT NULL,
          segment_id INT UNSIGNED DEFAULT NULL,
          synchronized TINYINT(1) DEFAULT \'0\' NOT NULL,
          dtype VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          include_adherents_no_committee TINYINT(1) DEFAULT NULL,
          include_adherents_in_committee TINYINT(1) DEFAULT NULL,
          include_committee_supervisors TINYINT(1) DEFAULT NULL,
          include_committee_hosts TINYINT(1) DEFAULT NULL,
          gender VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          age_min INT DEFAULT NULL,
          age_max INT DEFAULT NULL,
          first_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          interests LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\',
          registered_since DATE DEFAULT NULL,
          registered_until DATE DEFAULT NULL,
          contact_volunteer_team TINYINT(1) DEFAULT \'0\',
          contact_running_mate_team TINYINT(1) DEFAULT \'0\',
          contact_only_volunteers TINYINT(1) DEFAULT \'0\',
          contact_only_running_mates TINYINT(1) DEFAULT \'0\',
          contact_adherents TINYINT(1) DEFAULT \'0\',
          insee_code VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          contact_newsletter TINYINT(1) DEFAULT \'0\',
          postal_code VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          mandate VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          political_function VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          label VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          qualities LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          include_committee_provisional_supervisors TINYINT(1) DEFAULT NULL,
          is_certified TINYINT(1) DEFAULT NULL,
          scope VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_28CA9F9466E2221E (cause_id),
          INDEX IDX_28CA9F949C262DB3 (referent_tag_id),
          INDEX IDX_28CA9F949F2C3FAB (zone_id),
          INDEX IDX_28CA9F94AAA61A99 (territorial_council_id),
          INDEX IDX_28CA9F94C7A72 (political_committee_id),
          INDEX IDX_28CA9F94DB296AAD (segment_id),
          INDEX IDX_28CA9F94ED1A100B (committee_id),
          INDEX IDX_28CA9F94F74563E3 (user_list_definition_id),
          INDEX IDX_28CA9F94FAF04979 (adherent_segment_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_messages (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          filter_id INT UNSIGNED DEFAULT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          subject VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          sent_at DATETIME DEFAULT NULL,
          send_to_timeline TINYINT(1) DEFAULT \'0\' NOT NULL,
          recipient_count INT DEFAULT NULL,
          source VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'platform\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
          json_content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_D187C183D395B25E (filter_id),
          INDEX IDX_D187C183F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_referent_tag (
          adherent_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_79E8AFFD25F06C53 (adherent_id),
          INDEX IDX_79E8AFFD9C262DB3 (referent_tag_id),
          PRIMARY KEY(adherent_id, referent_tag_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_reset_password_tokens (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          value VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          expired_at DATETIME NOT NULL,
          used_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX adherent_reset_password_token_account_unique (value, adherent_uuid),
          UNIQUE INDEX adherent_reset_password_token_unique (value),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_segment (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          author_id INT UNSIGNED NOT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          member_ids LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          mailchimp_id INT DEFAULT NULL,
          synchronized TINYINT(1) DEFAULT \'0\' NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          segment_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_9DF0C7EBF675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_subscription_type (
          adherent_id INT UNSIGNED NOT NULL,
          subscription_type_id INT UNSIGNED NOT NULL,
          INDEX IDX_F93DC28A25F06C53 (adherent_id),
          INDEX IDX_F93DC28AB6596C08 (subscription_type_id),
          PRIMARY KEY(
            adherent_id, subscription_type_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_tags (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX adherent_tag_name_unique (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_thematic_community (
          adherent_id INT UNSIGNED NOT NULL,
          thematic_community_id INT UNSIGNED NOT NULL,
          INDEX IDX_DAB0B4EC1BE5825E (thematic_community_id),
          INDEX IDX_DAB0B4EC25F06C53 (adherent_id),
          PRIMARY KEY(
            adherent_id, thematic_community_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherent_zone (
          adherent_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_1C14D08525F06C53 (adherent_id),
          INDEX IDX_1C14D0859F2C3FAB (zone_id),
          PRIMARY KEY(adherent_id, zone_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE adherents (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          managed_area_id INT DEFAULT NULL,
          coordinator_committee_area_id INT DEFAULT NULL,
          media_id BIGINT DEFAULT NULL,
          procuration_managed_area_id INT DEFAULT NULL,
          assessor_managed_area_id INT DEFAULT NULL,
          municipal_chief_managed_area_id INT DEFAULT NULL,
          jecoute_managed_area_id INT DEFAULT NULL,
          senator_area_id INT DEFAULT NULL,
          managed_district_id INT UNSIGNED DEFAULT NULL,
          consular_managed_area_id INT DEFAULT NULL,
          assessor_role_id INT DEFAULT NULL,
          municipal_manager_role_id INT DEFAULT NULL,
          municipal_manager_supervisor_role_id INT DEFAULT NULL,
          senatorial_candidate_managed_area_id INT DEFAULT NULL,
          lre_area_id INT DEFAULT NULL,
          legislative_candidate_managed_district_id INT UNSIGNED DEFAULT NULL,
          candidate_managed_area_id INT UNSIGNED DEFAULT NULL,
          coalition_moderator_role_id INT UNSIGNED DEFAULT NULL,
          password VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          old_password VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          email_address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          birthdate DATE DEFAULT NULL,
          position VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT \'DISABLED\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
          registered_at DATETIME NOT NULL,
          activated_at DATETIME DEFAULT NULL,
          updated_at DATETIME DEFAULT NULL,
          last_logged_at DATETIME DEFAULT NULL,
          interests LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          local_host_emails_subscription TINYINT(1) DEFAULT \'0\' NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          first_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          address_address VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_insee VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          com_mobile TINYINT(1) DEFAULT NULL,
          adherent TINYINT(1) DEFAULT \'0\' NOT NULL,
          emails_subscriptions LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          remind_sent TINYINT(1) DEFAULT \'0\' NOT NULL,
          mandates LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          address_region VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          nickname VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          nickname_used TINYINT(1) DEFAULT \'0\' NOT NULL,
          comments_cgu_accepted TINYINT(1) DEFAULT \'0\' NOT NULL,
          description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          facebook_page_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_page_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          display_media TINYINT(1) NOT NULL,
          nationality VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          custom_gender VARCHAR(80) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          canary_tester TINYINT(1) DEFAULT \'0\' NOT NULL,
          email_unsubscribed TINYINT(1) DEFAULT \'0\' NOT NULL,
          email_unsubscribed_at DATETIME DEFAULT NULL,
          print_privilege TINYINT(1) DEFAULT \'0\' NOT NULL,
          election_results_reporter TINYINT(1) DEFAULT \'0\' NOT NULL,
          certified_at DATETIME DEFAULT NULL,
          address_geocodable_hash VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          linkedin_page_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          telegram_page_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          job VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          activity_area VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          membership_reminded_at DATETIME DEFAULT NULL,
          notified_for_election TINYINT(1) DEFAULT \'0\' NOT NULL,
          source VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          coalition_subscription TINYINT(1) DEFAULT \'0\' NOT NULL,
          cause_subscription TINYINT(1) DEFAULT \'0\' NOT NULL,
          coalitions_cgu_accepted TINYINT(1) DEFAULT \'0\' NOT NULL,
          vote_inspector TINYINT(1) DEFAULT \'0\' NOT NULL,
          national_role TINYINT(1) DEFAULT \'0\' NOT NULL,
          mailchimp_status VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          phoning_manager_role TINYINT(1) DEFAULT \'0\' NOT NULL,
          pap_national_manager_role TINYINT(1) DEFAULT \'0\' NOT NULL,
          UNIQUE INDEX UNIQ_562C7DA393494FA8 (senator_area_id),
          UNIQUE INDEX UNIQ_562C7DA3FCCAF6D5 (
            senatorial_candidate_managed_area_id
          ),
          UNIQUE INDEX UNIQ_562C7DA3E4A5D7A5 (assessor_role_id),
          UNIQUE INDEX UNIQ_562C7DA3E1B55931 (assessor_managed_area_id),
          UNIQUE INDEX UNIQ_562C7DA3DC184E71 (managed_area_id),
          UNIQUE INDEX UNIQ_562C7DA3CC72679B (
            municipal_chief_managed_area_id
          ),
          UNIQUE INDEX UNIQ_562C7DA3A188FE64 (nickname),
          UNIQUE INDEX UNIQ_562C7DA3A132C3C5 (managed_district_id),
          UNIQUE INDEX UNIQ_562C7DA39BF75CAD (
            legislative_candidate_managed_district_id
          ),
          UNIQUE INDEX UNIQ_562C7DA39801977F (
            municipal_manager_supervisor_role_id
          ),
          UNIQUE INDEX UNIQ_562C7DA394E3BB99 (jecoute_managed_area_id),
          UNIQUE INDEX adherents_email_address_unique (email_address),
          UNIQUE INDEX UNIQ_562C7DA38828ED30 (coalition_moderator_role_id),
          UNIQUE INDEX UNIQ_562C7DA379DE69AA (municipal_manager_role_id),
          UNIQUE INDEX UNIQ_562C7DA379645AD5 (lre_area_id),
          UNIQUE INDEX UNIQ_562C7DA37657F304 (candidate_managed_area_id),
          UNIQUE INDEX UNIQ_562C7DA339054338 (procuration_managed_area_id),
          UNIQUE INDEX UNIQ_562C7DA31A912B27 (coordinator_committee_area_id),
          UNIQUE INDEX UNIQ_562C7DA3122E5FF4 (consular_managed_area_id),
          INDEX IDX_562C7DA3EA9FDD75 (media_id),
          UNIQUE INDEX adherents_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE administrator_export_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          administrator_id INT NOT NULL,
          route_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          parameters LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\',
          exported_at DATETIME DEFAULT NULL,
          INDEX IDX_10499F014B09E92C (administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE administrators (
          id INT AUTO_INCREMENT NOT NULL,
          email_address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          google_authenticator_secret VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          activated TINYINT(1) NOT NULL,
          UNIQUE INDEX administrators_email_address_unique (email_address),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE algolia_candidature (
          id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE application_request_running_mate (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          curriculum_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_local_association_member TINYINT(1) DEFAULT \'0\' NOT NULL,
          local_association_domain LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_political_activist TINYINT(1) DEFAULT \'0\' NOT NULL,
          political_activist_details LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_previous_elected_official TINYINT(1) DEFAULT \'0\' NOT NULL,
          previous_elected_official_details LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          favorite_theme_details LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          project_details LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          professional_assets LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          favorite_cities LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          email_address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          address VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          country VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          profession VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          custom_favorite_theme LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          gender VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          taken_for_city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          displayed TINYINT(1) DEFAULT \'1\' NOT NULL,
          INDEX IDX_D1D6095625F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE application_request_tag (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE application_request_technical_skill (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          display TINYINT(1) DEFAULT \'1\' NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE application_request_theme (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          display TINYINT(1) DEFAULT \'1\' NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE application_request_volunteer (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          custom_technical_skills VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_previous_campaign_member TINYINT(1) NOT NULL,
          previous_campaign_details LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          share_associative_commitment TINYINT(1) NOT NULL,
          associative_commitment_details LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          favorite_cities LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          email_address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          address VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          country VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          profession VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          custom_favorite_theme LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          gender VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          taken_for_city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          displayed TINYINT(1) DEFAULT \'1\' NOT NULL,
          INDEX IDX_1139657025F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE article_proposal_theme (
          article_id BIGINT NOT NULL,
          proposal_theme_id INT NOT NULL,
          INDEX IDX_F6B9A2217294869C (article_id),
          INDEX IDX_F6B9A221B85948AF (proposal_theme_id),
          PRIMARY KEY(article_id, proposal_theme_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE articles (
          id BIGINT AUTO_INCREMENT NOT NULL,
          category_id INT DEFAULT NULL,
          media_id BIGINT DEFAULT NULL,
          published_at DATETIME NOT NULL,
          published TINYINT(1) NOT NULL,
          display_media TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          deleted_at DATETIME DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          keywords VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_BFDD316812469DE2 (category_id),
          INDEX IDX_BFDD3168EA9FDD75 (media_id),
          UNIQUE INDEX UNIQ_BFDD3168989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE articles_categories (
          id INT AUTO_INCREMENT NOT NULL,
          position SMALLINT NOT NULL,
          name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          cta_link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          cta_label VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          display TINYINT(1) DEFAULT \'1\' NOT NULL,
          UNIQUE INDEX UNIQ_DE004A0E989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE assessor_managed_areas (
          id INT AUTO_INCREMENT NOT NULL,
          codes LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE assessor_requests (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          vote_place_id INT DEFAULT NULL,
          gender VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          birth_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          birthdate DATE NOT NULL,
          birth_city VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          address VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          vote_city VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          office_number VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          email_address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          phone VARCHAR(35) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          assessor_city VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          office VARCHAR(15) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          processed TINYINT(1) NOT NULL,
          processed_at DATETIME DEFAULT NULL,
          enabled TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          assessor_postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          assessor_country VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          reachable TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_26BC800F3F90B30 (vote_place_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE assessor_requests_vote_place_wishes (
          assessor_request_id INT UNSIGNED NOT NULL,
          vote_place_id INT NOT NULL,
          INDEX IDX_1517FC131BD1903D (assessor_request_id),
          INDEX IDX_1517FC13F3F90B30 (vote_place_id),
          PRIMARY KEY(
            assessor_request_id, vote_place_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE assessor_role_association (
          id INT AUTO_INCREMENT NOT NULL,
          vote_place_id INT DEFAULT NULL,
          UNIQUE INDEX UNIQ_B93395C2F3F90B30 (vote_place_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE audience (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          age_min INT DEFAULT NULL,
          age_max INT DEFAULT NULL,
          registered_since DATE DEFAULT NULL,
          registered_until DATE DEFAULT NULL,
          is_committee_member TINYINT(1) DEFAULT NULL,
          is_certified TINYINT(1) DEFAULT NULL,
          has_email_subscription TINYINT(1) DEFAULT NULL,
          has_sms_subscription TINYINT(1) DEFAULT NULL,
          scope VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          roles LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          INDEX IDX_FDCD94189F2C3FAB (zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE audience_segment (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          filter_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          recipient_count INT UNSIGNED DEFAULT NULL,
          synchronized TINYINT(1) DEFAULT \'0\' NOT NULL,
          mailchimp_id INT DEFAULT NULL,
          INDEX IDX_C5C2F52FF675F31B (author_id),
          UNIQUE INDEX UNIQ_C5C2F52FD395B25E (filter_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE audience_snapshot (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          first_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          age_min INT DEFAULT NULL,
          age_max INT DEFAULT NULL,
          registered_since DATE DEFAULT NULL,
          registered_until DATE DEFAULT NULL,
          is_committee_member TINYINT(1) DEFAULT NULL,
          is_certified TINYINT(1) DEFAULT NULL,
          has_email_subscription TINYINT(1) DEFAULT NULL,
          has_sms_subscription TINYINT(1) DEFAULT NULL,
          scope VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          roles LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          INDEX IDX_BA99FEBB9F2C3FAB (zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE audience_snapshot_zone (
          audience_snapshot_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_10882DC09F2C3FAB (zone_id),
          INDEX IDX_10882DC0ACA633A8 (audience_snapshot_id),
          PRIMARY KEY(audience_snapshot_id, zone_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE audience_zone (
          audience_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_A719804F848CC616 (audience_id),
          INDEX IDX_A719804F9F2C3FAB (zone_id),
          PRIMARY KEY(audience_id, zone_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE banned_adherent (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          date DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE biography_executive_office_member (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          job VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          executive_officer TINYINT(1) DEFAULT \'0\' NOT NULL,
          first_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          published TINYINT(1) DEFAULT \'0\' NOT NULL,
          description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          facebook_profile VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_profile VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          instagram_profile VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          linked_in_profile VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          deputy_general_delegate TINYINT(1) DEFAULT \'0\' NOT NULL,
          UNIQUE INDEX executive_office_member_slug_unique (slug),
          UNIQUE INDEX executive_office_member_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE board_member (
          id INT AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          area VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_DCFABEDF25F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE board_member_roles (
          board_member_id INT NOT NULL,
          role_id INT UNSIGNED NOT NULL,
          INDEX IDX_1DD1E043C7BA2FD5 (board_member_id),
          INDEX IDX_1DD1E043D60322AC (role_id),
          PRIMARY KEY(board_member_id, role_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE candidate_managed_area (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_C604D2EA9F2C3FAB (zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE cause (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          coalition_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          second_coalition_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          canonical_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          followers_count INT UNSIGNED NOT NULL,
          mailchimp_id INT DEFAULT NULL,
          UNIQUE INDEX cause_name_unique (name),
          UNIQUE INDEX cause_uuid_unique (uuid),
          INDEX IDX_F0DA7FBF38C2B2DC (second_coalition_id),
          INDEX IDX_F0DA7FBFC2A46A23 (coalition_id),
          INDEX IDX_F0DA7FBFF675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE cause_follower (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          cause_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          first_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          email_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          cgu_accepted TINYINT(1) DEFAULT NULL,
          cause_subscription TINYINT(1) DEFAULT NULL,
          coalition_subscription TINYINT(1) DEFAULT NULL,
          UNIQUE INDEX cause_follower_unique (cause_id, adherent_id),
          UNIQUE INDEX cause_follower_uuid_unique (uuid),
          INDEX IDX_6F9A854425F06C53 (adherent_id),
          INDEX IDX_6F9A854466E2221E (cause_id),
          INDEX IDX_6F9A85449F2C3FAB (zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE cause_quick_action (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          cause_id INT UNSIGNED NOT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_DC1B329B66E2221E (cause_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE certification_request (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          processed_by_id INT DEFAULT NULL,
          found_duplicated_adherent_id INT UNSIGNED DEFAULT NULL,
          status VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          document_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          document_mime_type VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          processed_at DATETIME DEFAULT NULL,
          block_reason VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          custom_block_reason LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          block_comment LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          refusal_reason VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          custom_refusal_reason LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          refusal_comment LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          ocr_payload LONGTEXT DEFAULT NULL,
          ocr_status VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          ocr_result VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_6E7481A925F06C53 (adherent_id),
          INDEX IDX_6E7481A92FFD4FD3 (processed_by_id),
          INDEX IDX_6E7481A96EA98020 (found_duplicated_adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE chez_vous_cities (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          department_id INT UNSIGNED NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_codes LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\',
          insee_code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          latitude FLOAT (10, 6) NOT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) NOT NULL COMMENT \'(DC2Type:geo_point)\',
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_A42D9BEDAE80F5DF (department_id),
          UNIQUE INDEX UNIQ_A42D9BED15A3C1BC (insee_code),
          UNIQUE INDEX UNIQ_A42D9BED989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE chez_vous_departments (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          region_id INT UNSIGNED NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          label VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_29E7DD5798260155 (region_id),
          UNIQUE INDEX UNIQ_29E7DD5777153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE chez_vous_markers (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          city_id INT UNSIGNED NOT NULL,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          latitude FLOAT (10, 6) NOT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) NOT NULL COMMENT \'(DC2Type:geo_point)\',
          INDEX IDX_452F890F8BAC62AF (city_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE chez_vous_measure_types (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          updated_at DATETIME NOT NULL,
          source_link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          source_label VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          oldolf_link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          eligibility_link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_B80D46F577153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE chez_vous_measures (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          city_id INT UNSIGNED NOT NULL,
          type_id INT UNSIGNED NOT NULL,
          payload LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\',
          UNIQUE INDEX chez_vous_measures_city_type_unique (city_id, type_id),
          INDEX IDX_E6E8973E8BAC62AF (city_id),
          INDEX IDX_E6E8973EC54C8C93 (type_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE chez_vous_regions (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_A6C12FCC77153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE cities (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          department_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          insee_code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_codes LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          INDEX IDX_D95DB16BAE80F5DF (department_id),
          UNIQUE INDEX UNIQ_D95DB16B15A3C1BC (insee_code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE clarifications (
          id BIGINT AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          deleted_at DATETIME DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          display_media TINYINT(1) NOT NULL,
          published TINYINT(1) NOT NULL,
          keywords VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_2FAB8972EA9FDD75 (media_id),
          UNIQUE INDEX UNIQ_2FAB8972989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE cms_block (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_AD680C0E9DF5350C (created_by_administrator_id),
          INDEX IDX_AD680C0ECF1918FF (updated_by_administrator_id),
          UNIQUE INDEX UNIQ_AD680C0E5E237E06 (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE coalition (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          enabled TINYINT(1) DEFAULT \'1\' NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          youtube_id VARCHAR(11) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX coalition_name_unique (name),
          UNIQUE INDEX coalition_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE coalition_follower (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          coalition_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX coalition_follower_uuid_unique (uuid),
          INDEX IDX_DFF370E225F06C53 (adherent_id),
          INDEX IDX_DFF370E2C2A46A23 (coalition_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE coalition_moderator_role_association (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committee_candidacies_group (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committee_candidacy (
          id INT AUTO_INCREMENT NOT NULL,
          committee_election_id INT UNSIGNED NOT NULL,
          committee_membership_id INT UNSIGNED NOT NULL,
          candidacies_group_id INT UNSIGNED DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          gender VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          biography LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          faith_statement LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_public_faith_statement TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_9A044544E891720 (committee_election_id),
          INDEX IDX_9A04454FC1537C1 (candidacies_group_id),
          INDEX IDX_9A04454FCC6DA91 (committee_membership_id),
          UNIQUE INDEX UNIQ_9A04454D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committee_candidacy_invitation (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          membership_id INT UNSIGNED NOT NULL,
          candidacy_id INT NOT NULL,
          status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          accepted_at DATETIME DEFAULT NULL,
          declined_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_368B01611FB354CD (membership_id),
          INDEX IDX_368B016159B22434 (candidacy_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committee_election (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          committee_id INT UNSIGNED NOT NULL,
          designation_id INT UNSIGNED DEFAULT NULL,
          adherent_notified TINYINT(1) DEFAULT \'0\' NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_2CA406E5ED1A100B (committee_id),
          INDEX IDX_2CA406E5FAC7D83F (designation_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committee_feed_item (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          committee_id INT UNSIGNED DEFAULT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          event_id INT UNSIGNED DEFAULT NULL,
          item_type VARCHAR(18) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          published TINYINT(1) DEFAULT \'1\' NOT NULL,
          INDEX IDX_4F1CDC8071F7E88B (event_id),
          INDEX IDX_4F1CDC80ED1A100B (committee_id),
          INDEX IDX_4F1CDC80F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committee_feed_item_user_documents (
          committee_feed_item_id INT UNSIGNED NOT NULL,
          user_document_id INT UNSIGNED NOT NULL,
          INDEX IDX_D269D0AA6A24B1A2 (user_document_id),
          INDEX IDX_D269D0AABEF808A3 (committee_feed_item_id),
          PRIMARY KEY(
            committee_feed_item_id, user_document_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committee_membership_history_referent_tag (
          committee_membership_history_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_B6A8C718123C64CE (
            committee_membership_history_id
          ),
          INDEX IDX_B6A8C7189C262DB3 (referent_tag_id),
          PRIMARY KEY(
            committee_membership_history_id,
            referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committee_merge_histories (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          source_committee_id INT UNSIGNED NOT NULL,
          destination_committee_id INT UNSIGNED NOT NULL,
          merged_by_id INT DEFAULT NULL,
          reverted_by_id INT DEFAULT NULL,
          date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          reverted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX committee_merge_histories_date_idx (date),
          INDEX committee_merge_histories_destination_committee_id_idx (destination_committee_id),
          INDEX committee_merge_histories_source_committee_id_idx (source_committee_id),
          INDEX IDX_BB95FBBC50FA8329 (merged_by_id),
          INDEX IDX_BB95FBBCA8E1562 (reverted_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committee_merge_histories_merged_memberships (
          committee_merge_history_id INT UNSIGNED NOT NULL,
          committee_membership_id INT UNSIGNED NOT NULL,
          INDEX IDX_CB8E336F9379ED92 (committee_merge_history_id),
          UNIQUE INDEX UNIQ_CB8E336FFCC6DA91 (committee_membership_id),
          PRIMARY KEY(
            committee_merge_history_id, committee_membership_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committee_provisional_supervisor (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          committee_id INT UNSIGNED NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_E394C3D425F06C53 (adherent_id),
          INDEX IDX_E394C3D4ED1A100B (committee_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committee_referent_tag (
          committee_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_285EB1C59C262DB3 (referent_tag_id),
          INDEX IDX_285EB1C5ED1A100B (committee_id),
          PRIMARY KEY(committee_id, referent_tag_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committee_zone (
          committee_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_37C5F2249F2C3FAB (zone_id),
          INDEX IDX_37C5F224ED1A100B (committee_id),
          PRIMARY KEY(committee_id, zone_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committees (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          current_designation_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          canonical_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          facebook_page_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_nickname VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          approved_at DATETIME DEFAULT NULL,
          refused_at DATETIME DEFAULT NULL,
          created_by CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          members_count SMALLINT UNSIGNED NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          address_address VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_insee VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          name_locked TINYINT(1) DEFAULT \'0\' NOT NULL,
          address_region VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          mailchimp_id INT DEFAULT NULL,
          address_geocodable_hash VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          closed_at DATETIME DEFAULT NULL,
          UNIQUE INDEX committee_canonical_name_unique (canonical_name),
          UNIQUE INDEX committee_slug_unique (slug),
          INDEX committee_status_idx (status),
          UNIQUE INDEX committee_uuid_unique (uuid),
          INDEX IDX_A36198C6B4D2A5D1 (current_designation_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committees_membership_histories (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          committee_id INT UNSIGNED DEFAULT NULL,
          adherent_uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          action VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          privilege VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX committees_membership_histories_action_idx (action),
          INDEX committees_membership_histories_adherent_uuid_idx (adherent_uuid),
          INDEX committees_membership_histories_date_idx (date),
          INDEX IDX_4BBAE2C7ED1A100B (committee_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE committees_memberships (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          committee_id INT UNSIGNED NOT NULL,
          privilege VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          joined_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          enable_vote TINYINT(1) DEFAULT NULL,
          UNIQUE INDEX adherent_has_joined_committee (adherent_id, committee_id),
          UNIQUE INDEX adherent_votes_in_committee (adherent_id, enable_vote),
          INDEX committees_memberships_role_idx (privilege),
          INDEX IDX_E7A6490E25F06C53 (adherent_id),
          INDEX IDX_E7A6490EED1A100B (committee_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE consular_district (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          countries LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          cities LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          number SMALLINT NOT NULL,
          points LONGTEXT DEFAULT NULL,
          UNIQUE INDEX consular_district_code_unique (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE consular_managed_area (
          id INT AUTO_INCREMENT NOT NULL,
          consular_district_id INT UNSIGNED DEFAULT NULL,
          INDEX IDX_7937A51292CA96FD (consular_district_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE coordinator_managed_areas (
          id INT AUTO_INCREMENT NOT NULL,
          codes LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          sector VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE custom_search_results (
          id BIGINT AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          keywords VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          display_media TINYINT(1) NOT NULL,
          INDEX IDX_38973E54EA9FDD75 (media_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE department (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          region_id INT UNSIGNED NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          label VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_CD1DE18A98260155 (region_id),
          UNIQUE INDEX UNIQ_CD1DE18A77153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE deputy_managed_users_message (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          district_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          subject VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          offset BIGINT NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_5AC419DD25F06C53 (adherent_id),
          INDEX IDX_5AC419DDB08FA272 (district_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE designation (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          zones LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          candidacy_start_date DATETIME NOT NULL,
          candidacy_end_date DATETIME DEFAULT NULL,
          vote_start_date DATETIME DEFAULT NULL,
          vote_end_date DATETIME DEFAULT NULL,
          result_display_delay SMALLINT UNSIGNED NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          additional_round_duration SMALLINT UNSIGNED NOT NULL,
          lock_period_threshold SMALLINT UNSIGNED NOT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          limited TINYINT(1) DEFAULT \'0\' NOT NULL,
          denomination VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'désignation\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
          pools LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          result_schedule_delay DOUBLE PRECISION UNSIGNED DEFAULT \'0\' NOT NULL,
          notifications INT DEFAULT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE designation_referent_tag (
          designation_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_7538F35A9C262DB3 (referent_tag_id),
          INDEX IDX_7538F35AFAC7D83F (designation_id),
          PRIMARY KEY(designation_id, referent_tag_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE device_zone (
          device_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_29D2153D94A4C7D4 (device_id),
          INDEX IDX_29D2153D9F2C3FAB (zone_id),
          PRIMARY KEY(device_id, zone_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE devices (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          device_uuid VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_logged_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX devices_device_uuid_unique (device_uuid),
          UNIQUE INDEX devices_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE districts (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          geo_data_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED DEFAULT NULL,
          countries LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          code VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          number SMALLINT UNSIGNED NOT NULL,
          name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          department_code VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX district_code_unique (code),
          UNIQUE INDEX district_department_code_number (department_code, number),
          UNIQUE INDEX district_referent_tag_unique (referent_tag_id),
          UNIQUE INDEX UNIQ_68E318DC80E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE donation_donation_tag (
          donation_id INT UNSIGNED NOT NULL,
          donation_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_F2D7087F4DC1279C (donation_id),
          INDEX IDX_F2D7087F790547EA (donation_tag_id),
          PRIMARY KEY(donation_id, donation_tag_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE donation_tags (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          label VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          color VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX donation_tag_label_unique (label),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE donation_transactions (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          donation_id INT UNSIGNED NOT NULL,
          paybox_result_code VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          paybox_authorization_code VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          paybox_payload LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\',
          paybox_date_time DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          paybox_transaction_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          paybox_subscription_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX donation_transactions_result_idx (paybox_result_code),
          INDEX IDX_89D6D36B4DC1279C (donation_id),
          UNIQUE INDEX UNIQ_89D6D36B5A4036C7 (paybox_transaction_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE donations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          donator_id INT UNSIGNED NOT NULL,
          amount INT NOT NULL,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          client_ip VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          updated_at DATETIME NOT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          address_address VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_insee VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          duration SMALLINT DEFAULT 0 NOT NULL,
          subscription_ended_at DATETIME DEFAULT NULL,
          status VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          paybox_order_ref VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          nationality VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_region VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          check_number VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          transfer_number VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          filename VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          comment LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          donated_at DATETIME NOT NULL,
          last_success_date DATETIME DEFAULT NULL,
          code VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_geocodable_hash VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          beneficiary VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX donation_duration_idx (duration),
          INDEX donation_status_idx (status),
          INDEX donation_uuid_idx (uuid),
          INDEX IDX_CDE98962831BACAF (donator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE donator_donator_tag (
          donator_id INT UNSIGNED NOT NULL,
          donator_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_6BAEC28C71F026E6 (donator_tag_id),
          INDEX IDX_6BAEC28C831BACAF (donator_id),
          PRIMARY KEY(donator_id, donator_tag_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE donator_identifier (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          identifier VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE donator_kinship (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          donator_id INT UNSIGNED NOT NULL,
          related_id INT UNSIGNED NOT NULL,
          kinship VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_E542211D4162C001 (related_id),
          INDEX IDX_E542211D831BACAF (donator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE donator_tags (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          label VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          color VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX donator_tag_label_unique (label),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE donators (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          reference_donation_id INT UNSIGNED DEFAULT NULL,
          last_successful_donation_id INT UNSIGNED DEFAULT NULL,
          identifier VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          country VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          email_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          comment LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX donator_identifier_unique (identifier),
          INDEX IDX_A902FDD725F06C53 (adherent_id),
          INDEX IDX_A902FDD7B08E074EA9D1C132C808BA5A (
            email_address, first_name, last_name
          ),
          UNIQUE INDEX UNIQ_A902FDD7ABF665A8 (reference_donation_id),
          UNIQUE INDEX UNIQ_A902FDD7DE59CB1A (last_successful_donation_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE elected_representative (
          id INT AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          birth_date DATE NOT NULL,
          birth_place VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          contact_email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          contact_phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          has_followed_training TINYINT(1) DEFAULT \'0\' NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          email_unsubscribed_at DATETIME DEFAULT NULL,
          email_unsubscribed TINYINT(1) DEFAULT \'0\' NOT NULL,
          UNIQUE INDEX UNIQ_BF51F0FD25F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE elected_representative_label (
          id INT AUTO_INCREMENT NOT NULL,
          elected_representative_id INT NOT NULL,
          on_going TINYINT(1) DEFAULT \'1\' NOT NULL,
          begin_year INT DEFAULT NULL,
          finish_year INT DEFAULT NULL,
          name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_D8143704D38DA5D3 (elected_representative_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE elected_representative_mandate (
          id INT AUTO_INCREMENT NOT NULL,
          elected_representative_id INT NOT NULL,
          zone_id INT DEFAULT NULL,
          geo_zone_id INT UNSIGNED DEFAULT NULL,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          is_elected TINYINT(1) DEFAULT \'0\' NOT NULL,
          begin_at DATE NOT NULL,
          finish_at DATE DEFAULT NULL,
          political_affiliation VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          la_remsupport VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          on_going TINYINT(1) DEFAULT \'1\' NOT NULL,
          number SMALLINT DEFAULT 1 NOT NULL,
          INDEX IDX_38609146283AB2A9 (geo_zone_id),
          INDEX IDX_386091469F2C3FAB (zone_id),
          INDEX IDX_38609146D38DA5D3 (elected_representative_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE elected_representative_political_function (
          id INT AUTO_INCREMENT NOT NULL,
          elected_representative_id INT NOT NULL,
          mandate_id INT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          clarification VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          on_going TINYINT(1) DEFAULT \'1\' NOT NULL,
          begin_at DATE NOT NULL,
          finish_at DATE DEFAULT NULL,
          INDEX IDX_303BAF416C1129CD (mandate_id),
          INDEX IDX_303BAF41D38DA5D3 (elected_representative_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE elected_representative_social_network_link (
          id INT AUTO_INCREMENT NOT NULL,
          elected_representative_id INT NOT NULL,
          url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_231377B5D38DA5D3 (elected_representative_id),
          UNIQUE INDEX social_network_elected_representative_unique (type, elected_representative_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE elected_representative_sponsorship (
          id INT AUTO_INCREMENT NOT NULL,
          elected_representative_id INT NOT NULL,
          presidential_election_year INT NOT NULL,
          candidate VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_CA6D486D38DA5D3 (elected_representative_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE elected_representative_user_list_definition (
          elected_representative_id INT NOT NULL,
          user_list_definition_id INT UNSIGNED NOT NULL,
          INDEX IDX_A9C53A24D38DA5D3 (elected_representative_id),
          INDEX IDX_A9C53A24F74563E3 (user_list_definition_id),
          PRIMARY KEY(
            elected_representative_id, user_list_definition_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE elected_representative_user_list_definition_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          elected_representative_id INT NOT NULL,
          user_list_definition_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          administrator_id INT DEFAULT NULL,
          action VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_1ECF756625F06C53 (adherent_id),
          INDEX IDX_1ECF75664B09E92C (administrator_id),
          INDEX IDX_1ECF7566D38DA5D3 (elected_representative_id),
          INDEX IDX_1ECF7566F74563E3 (user_list_definition_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE elected_representative_zone (
          id INT AUTO_INCREMENT NOT NULL,
          category_id INT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          code VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX elected_repr_zone_code (code),
          UNIQUE INDEX elected_representative_zone_name_category_unique (name, category_id),
          INDEX IDX_C52FC4A712469DE2 (category_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE elected_representative_zone_category (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX elected_representative_zone_category_name_unique (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE elected_representative_zone_parent (
          child_id INT NOT NULL,
          parent_id INT NOT NULL,
          INDEX IDX_CECA906F727ACA70 (parent_id),
          INDEX IDX_CECA906FDD62C21B (child_id),
          PRIMARY KEY(child_id, parent_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE elected_representative_zone_referent_tag (
          elected_representative_zone_id INT NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_D2B7A8C59C262DB3 (referent_tag_id),
          INDEX IDX_D2B7A8C5BE31A103 (elected_representative_zone_id),
          PRIMARY KEY(
            elected_representative_zone_id,
            referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE election_city_candidate (
          id INT AUTO_INCREMENT NOT NULL,
          gender VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          political_scheme VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          alliances VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          agreement TINYINT(1) DEFAULT \'0\' NOT NULL,
          eligible_advisers_count INT DEFAULT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          profile VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          investiture_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE election_city_card (
          id INT AUTO_INCREMENT NOT NULL,
          city_id INT UNSIGNED NOT NULL,
          first_candidate_id INT DEFAULT NULL,
          headquarters_manager_id INT DEFAULT NULL,
          politic_manager_id INT DEFAULT NULL,
          task_force_manager_id INT DEFAULT NULL,
          preparation_prevision_id INT DEFAULT NULL,
          candidate_prevision_id INT DEFAULT NULL,
          national_prevision_id INT DEFAULT NULL,
          candidate_option_prevision_id INT DEFAULT NULL,
          third_option_prevision_id INT DEFAULT NULL,
          population INT DEFAULT NULL,
          priority VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          risk TINYINT(1) DEFAULT \'0\' NOT NULL,
          UNIQUE INDEX city_card_city_unique (city_id),
          UNIQUE INDEX UNIQ_EB01E8D1354DEDE5 (candidate_option_prevision_id),
          UNIQUE INDEX UNIQ_EB01E8D15EC54712 (preparation_prevision_id),
          UNIQUE INDEX UNIQ_EB01E8D1781FEED9 (task_force_manager_id),
          UNIQUE INDEX UNIQ_EB01E8D1B29FABBC (headquarters_manager_id),
          UNIQUE INDEX UNIQ_EB01E8D1B86B270B (national_prevision_id),
          UNIQUE INDEX UNIQ_EB01E8D1E449D110 (first_candidate_id),
          UNIQUE INDEX UNIQ_EB01E8D1E4A014FA (politic_manager_id),
          UNIQUE INDEX UNIQ_EB01E8D1EBF42685 (candidate_prevision_id),
          UNIQUE INDEX UNIQ_EB01E8D1F543170A (third_option_prevision_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE election_city_contact (
          id INT AUTO_INCREMENT NOT NULL,
          city_id INT NOT NULL,
          `function` VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          caller VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          done TINYINT(1) DEFAULT \'0\' NOT NULL,
          comment LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_D04AFB68BAC62AF (city_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE election_city_manager (
          id INT AUTO_INCREMENT NOT NULL,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE election_city_partner (
          id INT AUTO_INCREMENT NOT NULL,
          city_id INT NOT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          consensus VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_704D77988BAC62AF (city_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE election_city_prevision (
          id INT AUTO_INCREMENT NOT NULL,
          strategy VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          alliances VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          allies VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          validated_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE election_rounds (
          id INT AUTO_INCREMENT NOT NULL,
          election_id INT NOT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          date DATE NOT NULL,
          INDEX IDX_37C02EA0A708DAFF (election_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE elections (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          introduction LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          proposal_content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          request_content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_1BD26F335E237E06 (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE email_templates (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX email_template_uuid_unique (uuid),
          INDEX IDX_6023E2A5F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE emails (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          message_class VARCHAR(55) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          sender VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          recipients LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          request_payload LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          response_payload LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          delivered_at DATETIME DEFAULT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE epci (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          surface DOUBLE PRECISION NOT NULL,
          department_code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          department_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          region_code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          region_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          city_insee VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          city_code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          city_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          city_full_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          city_dep VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          city_siren VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          code_arr VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          code_cant VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          population INT UNSIGNED DEFAULT NULL,
          epci_dep VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          epci_siren VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          insee VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          fiscal VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE event_group_category (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT \'ENABLED\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX event_group_category_name_unique (name),
          UNIQUE INDEX event_group_category_slug_unique (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE event_referent_tag (
          event_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_D3C8F5BE71F7E88B (event_id),
          INDEX IDX_D3C8F5BE9C262DB3 (referent_tag_id),
          PRIMARY KEY(event_id, referent_tag_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE event_user_documents (
          event_id INT UNSIGNED NOT NULL,
          user_document_id INT UNSIGNED NOT NULL,
          INDEX IDX_7D14491F6A24B1A2 (user_document_id),
          INDEX IDX_7D14491F71F7E88B (event_id),
          PRIMARY KEY(event_id, user_document_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE event_zone (
          base_event_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_BF208CAC3B1C4B73 (base_event_id),
          INDEX IDX_BF208CAC9F2C3FAB (zone_id),
          PRIMARY KEY(base_event_id, zone_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE events (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          organizer_id INT UNSIGNED DEFAULT NULL,
          committee_id INT UNSIGNED DEFAULT NULL,
          coalition_id INT UNSIGNED DEFAULT NULL,
          cause_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          canonical_name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(130) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          capacity INT DEFAULT NULL,
          begin_at DATETIME NOT NULL,
          finish_at DATETIME NOT NULL,
          participants_count SMALLINT UNSIGNED NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          address_address VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_insee VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          status VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          category_id INT UNSIGNED DEFAULT NULL,
          is_for_legislatives TINYINT(1) DEFAULT \'0\',
          published TINYINT(1) DEFAULT \'1\' NOT NULL,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          time_zone VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          address_region VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          invitations LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          address_geocodable_hash VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          visio_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          interests LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          mode VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          reminded TINYINT(1) DEFAULT \'0\' NOT NULL,
          private TINYINT(1) DEFAULT \'0\' NOT NULL,
          electoral TINYINT(1) DEFAULT \'0\' NOT NULL,
          UNIQUE INDEX event_slug_unique (slug),
          UNIQUE INDEX event_uuid_unique (uuid),
          INDEX IDX_5387574A12469DE2 (category_id),
          INDEX IDX_5387574A3826374D (begin_at),
          INDEX IDX_5387574A66E2221E (cause_id),
          INDEX IDX_5387574A7B00651C (status),
          INDEX IDX_5387574A876C4DDA (organizer_id),
          INDEX IDX_5387574AC2A46A23 (coalition_id),
          INDEX IDX_5387574AED1A100B (committee_id),
          INDEX IDX_5387574AFE28FD87 (finish_at),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE events_categories (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          event_group_category_id INT UNSIGNED NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT \'ENABLED\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX event_category_name_unique (name),
          UNIQUE INDEX event_category_slug_unique (slug),
          INDEX IDX_EF0AF3E9A267D842 (event_group_category_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE events_invitations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          event_id INT UNSIGNED DEFAULT NULL,
          email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          message LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          guests LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          first_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_B94D5AAD71F7E88B (event_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE events_registrations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          event_id INT UNSIGNED DEFAULT NULL,
          first_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          email_address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          newsletter_subscriber TINYINT(1) NOT NULL,
          adherent_uuid CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX event_registration_adherent_uuid_idx (adherent_uuid),
          INDEX event_registration_email_address_idx (email_address),
          INDEX IDX_EEFA30C071F7E88B (event_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE facebook_profiles (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          facebook_id VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          email_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          age_range LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json_array)\',
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          access_token VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          has_auto_uploaded TINYINT(1) NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX facebook_profile_facebook_id (facebook_id),
          UNIQUE INDEX facebook_profile_uuid (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE facebook_videos (
          id INT AUTO_INCREMENT NOT NULL,
          facebook_url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          author VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          position INT NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          published TINYINT(1) NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE failed_login_attempt (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          signature VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          at DATETIME NOT NULL,
          extra LONGTEXT NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE filesystem_file (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_id INT DEFAULT NULL,
          updated_by_id INT DEFAULT NULL,
          parent_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          type VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          displayed TINYINT(1) DEFAULT \'1\' NOT NULL,
          original_filename VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          extension VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          mime_type VARCHAR(75) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          size INT UNSIGNED DEFAULT NULL,
          external_link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX filesystem_file_slug_unique (slug),
          INDEX IDX_47F0AE285E237E06 (name),
          INDEX IDX_47F0AE28727ACA70 (parent_id),
          INDEX IDX_47F0AE28896DBBDE (updated_by_id),
          INDEX IDX_47F0AE288CDE5729 (type),
          INDEX IDX_47F0AE28B03A8386 (created_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE filesystem_file_permission (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          file_id INT UNSIGNED NOT NULL,
          name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX file_permission_unique (file_id, name),
          INDEX IDX_BD623E4C93CB796C (file_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE formation_axes (
          id BIGINT AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          path_id INT NOT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          display_media TINYINT(1) NOT NULL,
          position SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          INDEX IDX_7E652CB6D96C566B (path_id),
          INDEX IDX_7E652CB6EA9FDD75 (media_id),
          UNIQUE INDEX UNIQ_7E652CB6989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE formation_files (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          module_id BIGINT DEFAULT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          path VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          extension VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX formation_file_slug_extension (slug, extension),
          INDEX IDX_70BEDE2CAFC2B591 (module_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE formation_modules (
          id BIGINT AUTO_INCREMENT NOT NULL,
          axe_id BIGINT DEFAULT NULL,
          media_id BIGINT DEFAULT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          display_media TINYINT(1) NOT NULL,
          position SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          INDEX IDX_6B4806AC2E30CD41 (axe_id),
          INDEX IDX_6B4806ACEA9FDD75 (media_id),
          UNIQUE INDEX UNIQ_6B4806AC2B36786B (title),
          UNIQUE INDEX UNIQ_6B4806AC989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE formation_paths (
          id INT AUTO_INCREMENT NOT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          position SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          UNIQUE INDEX UNIQ_FD311864989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_borough (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          city_id INT UNSIGNED NOT NULL,
          geo_data_id INT UNSIGNED DEFAULT NULL,
          postal_code LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          population INT DEFAULT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          INDEX IDX_144958748BAC62AF (city_id),
          UNIQUE INDEX UNIQ_1449587477153098 (code),
          UNIQUE INDEX UNIQ_1449587480E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_canton (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          department_id INT UNSIGNED NOT NULL,
          geo_data_id INT UNSIGNED DEFAULT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          INDEX IDX_F04FC05FAE80F5DF (department_id),
          UNIQUE INDEX UNIQ_F04FC05F77153098 (code),
          UNIQUE INDEX UNIQ_F04FC05F80E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_city (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          department_id INT UNSIGNED DEFAULT NULL,
          city_community_id INT UNSIGNED DEFAULT NULL,
          geo_data_id INT UNSIGNED DEFAULT NULL,
          replacement_id INT UNSIGNED DEFAULT NULL,
          postal_code LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          population INT DEFAULT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          INDEX IDX_297C2D346D3B1930 (city_community_id),
          INDEX IDX_297C2D349D25CF90 (replacement_id),
          INDEX IDX_297C2D34AE80F5DF (department_id),
          UNIQUE INDEX UNIQ_297C2D3477153098 (code),
          UNIQUE INDEX UNIQ_297C2D3480E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_city_canton (
          city_id INT UNSIGNED NOT NULL,
          canton_id INT UNSIGNED NOT NULL,
          INDEX IDX_A4AB64718BAC62AF (city_id),
          INDEX IDX_A4AB64718D070D0B (canton_id),
          PRIMARY KEY(city_id, canton_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_city_community (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          geo_data_id INT UNSIGNED DEFAULT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          UNIQUE INDEX UNIQ_E5805E0877153098 (code),
          UNIQUE INDEX UNIQ_E5805E0880E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_city_community_department (
          city_community_id INT UNSIGNED NOT NULL,
          department_id INT UNSIGNED NOT NULL,
          INDEX IDX_1E2D6D066D3B1930 (city_community_id),
          INDEX IDX_1E2D6D06AE80F5DF (department_id),
          PRIMARY KEY(
            city_community_id, department_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_city_district (
          city_id INT UNSIGNED NOT NULL,
          district_id INT UNSIGNED NOT NULL,
          INDEX IDX_5C4191F8BAC62AF (city_id),
          INDEX IDX_5C4191FB08FA272 (district_id),
          PRIMARY KEY(city_id, district_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_consular_district (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          foreign_district_id INT UNSIGNED DEFAULT NULL,
          geo_data_id INT UNSIGNED DEFAULT NULL,
          cities LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          number SMALLINT NOT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          INDEX IDX_BBFC552F72D24D35 (foreign_district_id),
          UNIQUE INDEX UNIQ_BBFC552F77153098 (code),
          UNIQUE INDEX UNIQ_BBFC552F80E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_country (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          foreign_district_id INT UNSIGNED DEFAULT NULL,
          geo_data_id INT UNSIGNED DEFAULT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          INDEX IDX_E465446472D24D35 (foreign_district_id),
          UNIQUE INDEX UNIQ_E465446477153098 (code),
          UNIQUE INDEX UNIQ_E465446480E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_custom_zone (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          geo_data_id INT UNSIGNED DEFAULT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          UNIQUE INDEX UNIQ_ABE4DB5A77153098 (code),
          UNIQUE INDEX UNIQ_ABE4DB5A80E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_data (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          geo_shape GEOMETRY NOT NULL COMMENT \'(DC2Type:geometry)\',
          SPATIAL INDEX geo_data_geo_shape_idx (geo_shape),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_department (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          region_id INT UNSIGNED NOT NULL,
          geo_data_id INT UNSIGNED DEFAULT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          INDEX IDX_B460660498260155 (region_id),
          UNIQUE INDEX UNIQ_B460660477153098 (code),
          UNIQUE INDEX UNIQ_B460660480E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_district (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          department_id INT UNSIGNED NOT NULL,
          geo_data_id INT UNSIGNED DEFAULT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          number SMALLINT NOT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          INDEX IDX_DF782326AE80F5DF (department_id),
          UNIQUE INDEX UNIQ_DF78232677153098 (code),
          UNIQUE INDEX UNIQ_DF78232680E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_foreign_district (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          custom_zone_id INT UNSIGNED NOT NULL,
          geo_data_id INT UNSIGNED DEFAULT NULL,
          number SMALLINT NOT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          INDEX IDX_973BE1F198755666 (custom_zone_id),
          UNIQUE INDEX UNIQ_973BE1F177153098 (code),
          UNIQUE INDEX UNIQ_973BE1F180E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_region (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          country_id INT UNSIGNED NOT NULL,
          geo_data_id INT UNSIGNED DEFAULT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          INDEX IDX_A4B3C808F92F3E70 (country_id),
          UNIQUE INDEX UNIQ_A4B3C80877153098 (code),
          UNIQUE INDEX UNIQ_A4B3C80880E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_zone (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          geo_data_id INT UNSIGNED DEFAULT NULL,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          team_code VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          postal_code LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          UNIQUE INDEX geo_zone_code_type_unique (code, type),
          INDEX geo_zone_type_idx (type),
          UNIQUE INDEX UNIQ_A4CCEF0780E32C3E (geo_data_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE geo_zone_parent (
          child_id INT UNSIGNED NOT NULL,
          parent_id INT UNSIGNED NOT NULL,
          INDEX IDX_8E49B9D727ACA70 (parent_id),
          INDEX IDX_8E49B9DDD62C21B (child_id),
          PRIMARY KEY(child_id, parent_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE home_blocks (
          id BIGINT AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          position SMALLINT NOT NULL,
          position_name VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          title VARCHAR(70) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          subtitle VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          type VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          link VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          updated_at DATETIME NOT NULL,
          display_filter TINYINT(1) DEFAULT \'1\' NOT NULL,
          display_titles TINYINT(1) DEFAULT \'0\' NOT NULL,
          display_block TINYINT(1) DEFAULT \'1\' NOT NULL,
          title_cta VARCHAR(70) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          color_cta VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          bg_color VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          video_controls TINYINT(1) DEFAULT \'0\' NOT NULL,
          video_autoplay_loop TINYINT(1) DEFAULT \'1\' NOT NULL,
          INDEX IDX_3EE9FCC5EA9FDD75 (media_id),
          UNIQUE INDEX UNIQ_3EE9FCC5462CE4F5 (position),
          UNIQUE INDEX UNIQ_3EE9FCC54DBB5058 (position_name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE image (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          extension VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_C53D045FD17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE instance_quality (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          scopes LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          custom TINYINT(1) DEFAULT \'1\' NOT NULL,
          UNIQUE INDEX UNIQ_BB26C6D377153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE institutional_events_categories (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT \'ENABLED\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX institutional_event_category_name_unique (name),
          UNIQUE INDEX institutional_event_slug_unique (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE interactive_choices (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          step SMALLINT UNSIGNED NOT NULL,
          content_key VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          label VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX interactive_choices_content_key_unique (content_key),
          UNIQUE INDEX interactive_choices_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE interactive_invitation_has_choices (
          invitation_id INT UNSIGNED NOT NULL,
          choice_id INT UNSIGNED NOT NULL,
          INDEX IDX_31A811A2998666D1 (choice_id),
          INDEX IDX_31A811A2A35D7AF0 (invitation_id),
          PRIMARY KEY(invitation_id, choice_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE interactive_invitations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          friend_age SMALLINT UNSIGNED NOT NULL,
          friend_gender VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          friend_position VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          author_first_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          author_last_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          author_email_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          mail_subject VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          mail_body LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX interactive_invitations_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE internal_api_application (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          application_name VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          hostname VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          scope_required TINYINT(1) DEFAULT \'0\' NOT NULL,
          UNIQUE INDEX internal_application_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE invitations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          first_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          message LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          client_ip VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE je_marche_reports (
          id INT AUTO_INCREMENT NOT NULL,
          type VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          email_address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_code VARCHAR(11) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          convinced LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          almost_convinced LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          not_convinced SMALLINT UNSIGNED DEFAULT NULL,
          reaction LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE jecoute_choice (
          id INT AUTO_INCREMENT NOT NULL,
          question_id INT DEFAULT NULL,
          content VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          position SMALLINT NOT NULL,
          INDEX IDX_80BD898B1E27F6BF (question_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE jecoute_data_answer (
          id INT AUTO_INCREMENT NOT NULL,
          survey_question_id INT DEFAULT NULL,
          data_survey_id INT UNSIGNED DEFAULT NULL,
          text_field LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_12FB393E3C5110AB (data_survey_id),
          INDEX IDX_12FB393EA6DF29BA (survey_question_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE jecoute_data_answer_selected_choices (
          data_answer_id INT NOT NULL,
          choice_id INT NOT NULL,
          INDEX IDX_10DF117259C0831 (data_answer_id),
          INDEX IDX_10DF117998666D1 (choice_id),
          PRIMARY KEY(data_answer_id, choice_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE jecoute_data_survey (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          survey_id INT UNSIGNED NOT NULL,
          posted_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_6579E8E7B3FE509D (survey_id),
          INDEX IDX_6579E8E7F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE jecoute_managed_areas (
          id INT AUTO_INCREMENT NOT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          codes LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          INDEX IDX_DF8531749F2C3FAB (zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE jecoute_news (
          id INT AUTO_INCREMENT NOT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          created_by_id INT DEFAULT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          text LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          external_link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          topic VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          notification TINYINT(1) DEFAULT \'0\' NOT NULL,
          published TINYINT(1) DEFAULT \'1\' NOT NULL,
          space VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_34362099F2C3FAB (zone_id),
          INDEX IDX_3436209B03A8386 (created_by_id),
          INDEX IDX_3436209F675F31B (author_id),
          UNIQUE INDEX jecoute_news_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE jecoute_question (
          id INT AUTO_INCREMENT NOT NULL,
          content VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          discr VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE jecoute_region (
          id INT AUTO_INCREMENT NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          administrator_id INT DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          subtitle VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          primary_color VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          external_link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          banner VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          logo VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          enabled TINYINT(1) DEFAULT \'1\' NOT NULL,
          INDEX IDX_4E74226F4B09E92C (administrator_id),
          INDEX IDX_4E74226FF675F31B (author_id),
          UNIQUE INDEX UNIQ_4E74226F9F2C3FAB (zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE jecoute_riposte (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_id INT DEFAULT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          body LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          source_url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          with_notification TINYINT(1) DEFAULT \'1\' NOT NULL,
          enabled TINYINT(1) DEFAULT \'1\' NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          nb_views INT UNSIGNED DEFAULT 0 NOT NULL,
          nb_detail_views INT UNSIGNED DEFAULT 0 NOT NULL,
          nb_source_views INT UNSIGNED DEFAULT 0 NOT NULL,
          nb_ripostes INT UNSIGNED DEFAULT 0 NOT NULL,
          open_graph LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\',
          INDEX IDX_17E1064BB03A8386 (created_by_id),
          INDEX IDX_17E1064BF675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE jecoute_suggested_question (
          id INT NOT NULL,
          published TINYINT(1) DEFAULT \'0\' NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE jecoute_survey (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          administrator_id INT DEFAULT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          published TINYINT(1) DEFAULT \'0\' NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          tags LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          blocked_changes TINYINT(1) DEFAULT \'0\',
          INDEX IDX_EC4948E54B09E92C (administrator_id),
          INDEX IDX_EC4948E59F2C3FAB (zone_id),
          INDEX IDX_EC4948E5F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE jecoute_survey_question (
          id INT AUTO_INCREMENT NOT NULL,
          survey_id INT UNSIGNED DEFAULT NULL,
          question_id INT DEFAULT NULL,
          position SMALLINT NOT NULL,
          from_suggested_question INT DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_A2FBFA811E27F6BF (question_id),
          INDEX IDX_A2FBFA81B3FE509D (survey_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE jemarche_data_survey (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          data_survey_id INT UNSIGNED DEFAULT NULL,
          device_id INT UNSIGNED DEFAULT NULL,
          first_name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          email_address LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          agreed_to_stay_in_contact TINYINT(1) NOT NULL,
          agreed_to_contact_for_join TINYINT(1) NOT NULL,
          agreed_to_treat_personal_data TINYINT(1) NOT NULL,
          postal_code LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          profession VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          age_range VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          gender_other LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_8DF5D81894A4C7D4 (device_id),
          UNIQUE INDEX UNIQ_8DF5D8183C5110AB (data_survey_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE legislative_candidates (
          id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL,
          district_zone_id SMALLINT UNSIGNED DEFAULT NULL,
          media_id BIGINT DEFAULT NULL,
          facebook_page_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          email_address VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_page_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          donation_page_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          website_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          district_name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          district_number SMALLINT NOT NULL,
          description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          display_media TINYINT(1) NOT NULL,
          career VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          position INT NOT NULL,
          geojson LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'none\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_AE55AF9B23F5C396 (district_zone_id),
          INDEX IDX_AE55AF9BEA9FDD75 (media_id),
          UNIQUE INDEX legislative_candidates_slug_unique (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE legislative_district_zones (
          id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL,
          area_code VARCHAR(4) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          area_type VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          keywords LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          `rank` SMALLINT UNSIGNED NOT NULL,
          UNIQUE INDEX legislative_district_zones_area_code_unique (area_code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE list_total_result (
          id INT AUTO_INCREMENT NOT NULL,
          list_id INT DEFAULT NULL,
          vote_result_id INT NOT NULL,
          total INT DEFAULT 0 NOT NULL,
          INDEX IDX_A19B071E3DAE168B (list_id),
          INDEX IDX_A19B071E45EB7186 (vote_result_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE live_links (
          id INT AUTO_INCREMENT NOT NULL,
          position SMALLINT NOT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          link VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          updated_at DATETIME NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE lre_area (
          id INT AUTO_INCREMENT NOT NULL,
          referent_tag_id INT UNSIGNED DEFAULT NULL,
          all_tags TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_8D3B8F189C262DB3 (referent_tag_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE mailchimp_campaign (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          message_id INT UNSIGNED DEFAULT NULL,
          report_id INT UNSIGNED DEFAULT NULL,
          external_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          synchronized TINYINT(1) DEFAULT \'0\' NOT NULL,
          recipient_count INT DEFAULT NULL,
          status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          detail VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          label VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          static_segment_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_CFABD309537A1329 (message_id),
          UNIQUE INDEX UNIQ_CFABD3094BD2A4C0 (report_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE mailchimp_campaign_mailchimp_segment (
          mailchimp_campaign_id INT UNSIGNED NOT NULL,
          mailchimp_segment_id INT NOT NULL,
          INDEX IDX_901CE107828112CC (mailchimp_campaign_id),
          INDEX IDX_901CE107D21E482E (mailchimp_segment_id),
          PRIMARY KEY(
            mailchimp_campaign_id, mailchimp_segment_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE mailchimp_campaign_report (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          open_total INT NOT NULL,
          open_unique INT NOT NULL,
          open_rate INT NOT NULL,
          last_open DATETIME DEFAULT NULL,
          click_total INT NOT NULL,
          click_unique INT NOT NULL,
          click_rate INT NOT NULL,
          last_click DATETIME DEFAULT NULL,
          email_sent INT NOT NULL,
          unsubscribed INT NOT NULL,
          unsubscribed_rate INT NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE mailchimp_segment (
          id INT AUTO_INCREMENT NOT NULL,
          list VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          external_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE medias (
          id BIGINT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          path VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          width INT NOT NULL,
          height INT NOT NULL,
          size BIGINT NOT NULL,
          mime_type VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          compressed_display TINYINT(1) DEFAULT \'1\' NOT NULL,
          UNIQUE INDEX UNIQ_12D2AF81B548B0F (path),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE ministry_list_total_result (
          id INT AUTO_INCREMENT NOT NULL,
          ministry_vote_result_id INT DEFAULT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          nuance VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          adherent_count INT DEFAULT NULL,
          eligible_count INT DEFAULT NULL,
          total INT DEFAULT 0 NOT NULL,
          position INT DEFAULT NULL,
          candidate_first_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          candidate_last_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          outgoing_mayor TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_99D1332580711B75 (ministry_vote_result_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE ministry_vote_result (
          id INT AUTO_INCREMENT NOT NULL,
          election_round_id INT NOT NULL,
          city_id INT UNSIGNED DEFAULT NULL,
          created_by_id INT UNSIGNED DEFAULT NULL,
          updated_by_id INT UNSIGNED DEFAULT NULL,
          registered INT NOT NULL,
          abstentions INT NOT NULL,
          participated INT NOT NULL,
          expressed INT NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_B9F11DAE896DBBDE (updated_by_id),
          INDEX IDX_B9F11DAE8BAC62AF (city_id),
          INDEX IDX_B9F11DAEB03A8386 (created_by_id),
          INDEX IDX_B9F11DAEFCBF5E32 (election_round_id),
          UNIQUE INDEX ministry_vote_result_city_round_unique (city_id, election_round_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE mooc (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          article_image_id INT UNSIGNED DEFAULT NULL,
          list_image_id INT UNSIGNED DEFAULT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          content VARCHAR(800) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          youtube_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          youtube_duration TIME DEFAULT NULL,
          share_twitter_text VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          share_facebook_text VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          share_email_subject VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          share_email_body VARCHAR(500) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX mooc_slug (slug),
          UNIQUE INDEX UNIQ_9D5D3B5543C8160D (list_image_id),
          UNIQUE INDEX UNIQ_9D5D3B55684DD106 (article_image_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE mooc_attachment_file (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          path VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          extension VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX mooc_attachment_file_slug_extension (slug, extension),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE mooc_attachment_link (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          link VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE mooc_chapter (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          mooc_id INT UNSIGNED DEFAULT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          published TINYINT(1) DEFAULT \'0\' NOT NULL,
          published_at DATETIME NOT NULL,
          position SMALLINT NOT NULL,
          INDEX IDX_A3EDA0D1255EEB87 (mooc_id),
          UNIQUE INDEX mooc_chapter_slug (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE mooc_element_attachment_file (
          base_mooc_element_id INT UNSIGNED NOT NULL,
          attachment_file_id INT UNSIGNED NOT NULL,
          INDEX IDX_88759A265B5E2CEA (attachment_file_id),
          INDEX IDX_88759A26B1828C9D (base_mooc_element_id),
          PRIMARY KEY(
            base_mooc_element_id, attachment_file_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE mooc_element_attachment_link (
          base_mooc_element_id INT UNSIGNED NOT NULL,
          attachment_link_id INT UNSIGNED NOT NULL,
          INDEX IDX_324635C7653157F7 (attachment_link_id),
          INDEX IDX_324635C7B1828C9D (base_mooc_element_id),
          PRIMARY KEY(
            base_mooc_element_id, attachment_link_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE mooc_elements (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          chapter_id INT UNSIGNED DEFAULT NULL,
          image_id INT UNSIGNED DEFAULT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          youtube_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          position SMALLINT NOT NULL,
          duration TIME DEFAULT NULL,
          typeform_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          share_twitter_text VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          share_facebook_text VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          share_email_subject VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          share_email_body VARCHAR(500) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_691284C53DA5256D (image_id),
          INDEX IDX_691284C5579F4768 (chapter_id),
          UNIQUE INDEX mooc_element_slug (slug, chapter_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE municipal_chief_areas (
          id INT AUTO_INCREMENT NOT NULL,
          jecoute_access TINYINT(1) DEFAULT \'0\' NOT NULL,
          insee_code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE municipal_manager_role_association (
          id INT AUTO_INCREMENT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE municipal_manager_role_association_cities (
          municipal_manager_role_association_id INT NOT NULL,
          city_id INT UNSIGNED NOT NULL,
          INDEX IDX_A713D9C2D96891C (
            municipal_manager_role_association_id
          ),
          UNIQUE INDEX UNIQ_A713D9C28BAC62AF (city_id),
          PRIMARY KEY(
            municipal_manager_role_association_id,
            city_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE municipal_manager_supervisor_role (
          id INT AUTO_INCREMENT NOT NULL,
          referent_id INT UNSIGNED NOT NULL,
          INDEX IDX_F304FF35E47E35 (referent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE my_team_delegate_access_committee (
          delegated_access_id INT UNSIGNED NOT NULL,
          committee_id INT UNSIGNED NOT NULL,
          INDEX IDX_C52A163FED1A100B (committee_id),
          INDEX IDX_C52A163FFD98FA7A (delegated_access_id),
          PRIMARY KEY(
            delegated_access_id, committee_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE my_team_delegated_access (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          delegator_id INT UNSIGNED DEFAULT NULL,
          delegated_id INT UNSIGNED DEFAULT NULL,
          role VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          accesses LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          restricted_cities LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_421C13B98825BEFA (delegator_id),
          INDEX IDX_421C13B9B7E7AE18 (delegated_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE national_council_candidacies_group (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE national_council_candidacy (
          id INT AUTO_INCREMENT NOT NULL,
          election_id INT UNSIGNED NOT NULL,
          candidacies_group_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          gender VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          biography LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          quality VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          faith_statement LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_public_faith_statement TINYINT(1) DEFAULT \'0\' NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_31A7A20525F06C53 (adherent_id),
          INDEX IDX_31A7A205A708DAFF (election_id),
          INDEX IDX_31A7A205FC1537C1 (candidacies_group_id),
          UNIQUE INDEX UNIQ_31A7A205D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE national_council_election (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          designation_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_F3809347FAC7D83F (designation_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE newsletter_invitations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          first_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          client_ip VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE newsletter_subscriptions (
          id BIGINT AUTO_INCREMENT NOT NULL,
          email VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_code VARCHAR(11) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          deleted_at DATETIME DEFAULT NULL,
          country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          from_event TINYINT(1) DEFAULT \'0\' NOT NULL,
          confirmed_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          token CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_B3C13B0B5F37A13B (token),
          UNIQUE INDEX UNIQ_B3C13B0BD17F50A6 (uuid),
          UNIQUE INDEX UNIQ_B3C13B0BE7927C74 (email),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE notification (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          notification_class VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          body LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          delivered_at DATETIME DEFAULT NULL,
          topic VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          tokens LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE oauth_access_tokens (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          client_id INT UNSIGNED NOT NULL,
          user_id INT UNSIGNED DEFAULT NULL,
          device_id INT UNSIGNED DEFAULT NULL,
          identifier VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          revoked_at DATETIME DEFAULT NULL,
          created_at DATETIME NOT NULL,
          scopes LONGTEXT NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_CA42527C19EB6921 (client_id),
          INDEX IDX_CA42527C94A4C7D4 (device_id),
          INDEX IDX_CA42527CA76ED395 (user_id),
          UNIQUE INDEX oauth_access_tokens_identifier_unique (identifier),
          UNIQUE INDEX oauth_access_tokens_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE oauth_auth_codes (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          client_id INT UNSIGNED NOT NULL,
          user_id INT UNSIGNED DEFAULT NULL,
          device_id INT UNSIGNED DEFAULT NULL,
          identifier VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          revoked_at DATETIME DEFAULT NULL,
          created_at DATETIME NOT NULL,
          scopes LONGTEXT NOT NULL,
          redirect_uri LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_BB493F8319EB6921 (client_id),
          INDEX IDX_BB493F8394A4C7D4 (device_id),
          INDEX IDX_BB493F83A76ED395 (user_id),
          UNIQUE INDEX oauth_auth_codes_identifier_unique (identifier),
          UNIQUE INDEX oauth_auth_codes_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE oauth_clients (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          redirect_uris LONGTEXT NOT NULL,
          secret VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          allowed_grant_types LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          supported_scopes LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          ask_user_for_authorization TINYINT(1) DEFAULT \'1\' NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          deleted_at DATETIME DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          requested_roles LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          UNIQUE INDEX oauth_clients_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE oauth_refresh_tokens (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          access_token_id INT UNSIGNED DEFAULT NULL,
          identifier VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          revoked_at DATETIME DEFAULT NULL,
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_5AB6872CCB2688 (access_token_id),
          UNIQUE INDEX oauth_refresh_tokens_identifier_unique (identifier),
          UNIQUE INDEX oauth_refresh_tokens_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE order_articles (
          id INT AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          position SMALLINT NOT NULL,
          published TINYINT(1) NOT NULL,
          display_media TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          deleted_at DATETIME DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          keywords VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_5E25D3D9EA9FDD75 (media_id),
          UNIQUE INDEX UNIQ_5E25D3D9989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE order_section_order_article (
          order_article_id INT NOT NULL,
          order_section_id INT NOT NULL,
          INDEX IDX_A956D4E46BF91E2F (order_section_id),
          INDEX IDX_A956D4E4C14E7BC9 (order_article_id),
          PRIMARY KEY(
            order_article_id, order_section_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE order_sections (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          position SMALLINT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE organizational_chart_item (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          tree_root INT UNSIGNED DEFAULT NULL,
          parent_id INT UNSIGNED DEFAULT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          lft INT NOT NULL,
          lvl INT NOT NULL,
          rgt INT NOT NULL,
          type VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_29C1CBAC727ACA70 (parent_id),
          INDEX IDX_29C1CBACA977936C (tree_root),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE pages (
          id BIGINT AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          header_media_id BIGINT DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          deleted_at DATETIME DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          keywords VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          display_media TINYINT(1) NOT NULL,
          twitter_description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          layout VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'default\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_2074E5755B42DC0F (header_media_id),
          INDEX IDX_2074E575EA9FDD75 (media_id),
          UNIQUE INDEX UNIQ_2074E575989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE pap_address (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          number VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          insee_code VARCHAR(5) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          offset_x INT DEFAULT NULL,
          offset_y INT DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          INDEX IDX_47071E114118D12385E16F6B (latitude, longitude),
          INDEX IDX_47071E11D17F50A6 (uuid),
          INDEX IDX_47071E11D8AD1DD1AFAA2D47 (offset_x, offset_y),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE pap_building (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          address_id INT UNSIGNED NOT NULL,
          current_campaign_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_112ABBE148ED5CAD (current_campaign_id),
          UNIQUE INDEX UNIQ_112ABBE1F5B7AF75 (address_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE pap_building_block (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          building_id INT UNSIGNED NOT NULL,
          created_by_adherent_id INT UNSIGNED DEFAULT NULL,
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_61470C814D2A7E12 (building_id),
          INDEX IDX_61470C8185C9D733 (created_by_adherent_id),
          INDEX IDX_61470C81DF6CFDC9 (updated_by_adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE pap_building_block_statistics (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          building_block_id INT UNSIGNED NOT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          status VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_8B79BF6032618357 (building_block_id),
          INDEX IDX_8B79BF60F639F774 (campaign_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE pap_building_statistics (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          building_id INT UNSIGNED NOT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          last_passage_done_by_id INT UNSIGNED DEFAULT NULL,
          status VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_passage DATETIME DEFAULT NULL,
          nb_voters SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          nb_doors SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          nb_surveys SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_B6FB4E7B4D2A7E12 (building_id),
          INDEX IDX_B6FB4E7BDCDF6621 (last_passage_done_by_id),
          INDEX IDX_B6FB4E7BF639F774 (campaign_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE pap_campaign (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          survey_id INT UNSIGNED NOT NULL,
          administrator_id INT DEFAULT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          brief LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          goal INT NOT NULL,
          begin_at DATETIME DEFAULT NULL,
          finish_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_EF50C8E84B09E92C (administrator_id),
          INDEX IDX_EF50C8E8B3FE509D (survey_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE pap_campaign_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          questioner_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          data_survey_id INT UNSIGNED DEFAULT NULL,
          building_id INT UNSIGNED NOT NULL,
          status VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          building_block VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          floor SMALLINT UNSIGNED DEFAULT NULL,
          door VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          email_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          age_range VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          profession VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          to_contact TINYINT(1) DEFAULT NULL,
          to_join TINYINT(1) DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          finish_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_5A3F26F725F06C53 (adherent_id),
          INDEX IDX_5A3F26F74D2A7E12 (building_id),
          INDEX IDX_5A3F26F7CC0DE6E1 (questioner_id),
          INDEX IDX_5A3F26F7F639F774 (campaign_id),
          UNIQUE INDEX UNIQ_5A3F26F73C5110AB (data_survey_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE pap_floor (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          building_block_id INT UNSIGNED NOT NULL,
          created_by_adherent_id INT UNSIGNED DEFAULT NULL,
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          number SMALLINT UNSIGNED NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_633C3C6432618357 (building_block_id),
          INDEX IDX_633C3C6485C9D733 (created_by_adherent_id),
          INDEX IDX_633C3C64DF6CFDC9 (updated_by_adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE pap_floor_statistics (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          floor_id INT UNSIGNED NOT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          status VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_853B68C8854679E2 (floor_id),
          INDEX IDX_853B68C8F639F774 (campaign_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE pap_voter (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          address_id INT UNSIGNED NOT NULL,
          first_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          birthdate DATE DEFAULT NULL,
          vote_place VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          source VARCHAR(5) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_FBF5A013F5B7AF75 (address_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE phoning_campaign (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          team_id INT UNSIGNED DEFAULT NULL,
          audience_id INT UNSIGNED DEFAULT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          survey_id INT UNSIGNED NOT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          created_by_adherent_id INT UNSIGNED DEFAULT NULL,
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          goal INT NOT NULL,
          finish_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          brief LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          permanent TINYINT(1) DEFAULT \'0\' NOT NULL,
          participants_count INT DEFAULT 0 NOT NULL,
          INDEX IDX_C3882BA4296CD8AE (team_id),
          INDEX IDX_C3882BA485C9D733 (created_by_adherent_id),
          INDEX IDX_C3882BA49DF5350C (created_by_administrator_id),
          INDEX IDX_C3882BA4B3FE509D (survey_id),
          INDEX IDX_C3882BA4CF1918FF (updated_by_administrator_id),
          INDEX IDX_C3882BA4DF6CFDC9 (updated_by_adherent_id),
          UNIQUE INDEX UNIQ_C3882BA4848CC616 (audience_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE phoning_campaign_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          data_survey_id INT UNSIGNED DEFAULT NULL,
          caller_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          type VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_code_checked TINYINT(1) DEFAULT NULL,
          begin_at DATETIME NOT NULL,
          finish_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          need_email_renewal TINYINT(1) DEFAULT NULL,
          need_sms_renewal TINYINT(1) DEFAULT NULL,
          engagement VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          note SMALLINT UNSIGNED DEFAULT NULL,
          profession VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_EC19119825F06C53 (adherent_id),
          INDEX IDX_EC191198A5626C52 (caller_id),
          INDEX IDX_EC191198F639F774 (campaign_id),
          UNIQUE INDEX UNIQ_EC1911983C5110AB (data_survey_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE political_committee (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          territorial_council_id INT UNSIGNED NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          is_active TINYINT(1) DEFAULT \'0\' NOT NULL,
          UNIQUE INDEX UNIQ_39FAEE955E237E06 (name),
          UNIQUE INDEX UNIQ_39FAEE95AAA61A99 (territorial_council_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE political_committee_feed_item (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          political_committee_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          is_locked TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_54369E83C7A72 (political_committee_id),
          INDEX IDX_54369E83F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE political_committee_membership (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          political_committee_id INT UNSIGNED NOT NULL,
          joined_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          is_additional TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_FD85437BC7A72 (political_committee_id),
          UNIQUE INDEX UNIQ_FD85437B25F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE political_committee_quality (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          political_committee_membership_id INT UNSIGNED NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          joined_at DATETIME NOT NULL,
          INDEX IDX_243D6D3A78632915 (
            political_committee_membership_id
          ),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE poll (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          administrator_id INT DEFAULT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          question VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          finish_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          published TINYINT(1) DEFAULT \'0\' NOT NULL,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_84BCFA454B09E92C (administrator_id),
          INDEX IDX_84BCFA459F2C3FAB (zone_id),
          INDEX IDX_84BCFA45F675F31B (author_id),
          UNIQUE INDEX poll_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE poll_choice (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          poll_id INT UNSIGNED NOT NULL,
          value VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_2DAE19C93C947C0F (poll_id),
          UNIQUE INDEX poll_choice_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE poll_vote (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          choice_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          device_id INT UNSIGNED DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_ED568EBE25F06C53 (adherent_id),
          INDEX IDX_ED568EBE94A4C7D4 (device_id),
          INDEX IDX_ED568EBE998666D1 (choice_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE procuration_managed_areas (
          id INT AUTO_INCREMENT NOT NULL,
          codes LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE procuration_proxies (
          id INT AUTO_INCREMENT NOT NULL,
          gender VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          first_names VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          address VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city_insee VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          country VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          email_address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          birthdate DATE DEFAULT NULL,
          vote_postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          vote_city_insee VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          vote_city_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          vote_country VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          vote_office VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          reliability SMALLINT NOT NULL,
          disabled TINYINT(1) NOT NULL,
          reliability_description VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          proxies_count SMALLINT UNSIGNED DEFAULT 1 NOT NULL,
          french_request_available TINYINT(1) DEFAULT \'1\' NOT NULL,
          foreign_request_available TINYINT(1) DEFAULT \'1\' NOT NULL,
          state VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          reachable TINYINT(1) DEFAULT \'0\' NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE procuration_proxies_to_election_rounds (
          procuration_proxy_id INT NOT NULL,
          election_round_id INT NOT NULL,
          INDEX IDX_D075F5A9E15E419B (procuration_proxy_id),
          INDEX IDX_D075F5A9FCBF5E32 (election_round_id),
          PRIMARY KEY(
            procuration_proxy_id, election_round_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE procuration_requests (
          id INT AUTO_INCREMENT NOT NULL,
          procuration_request_found_by_id INT UNSIGNED DEFAULT NULL,
          found_proxy_id INT DEFAULT NULL,
          gender VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          first_names VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          address VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city_insee VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          country VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          email_address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          birthdate DATE DEFAULT NULL,
          vote_postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          vote_city_insee VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          vote_city_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          vote_country VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          vote_office VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          reason VARCHAR(15) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          processed TINYINT(1) NOT NULL,
          processed_at DATETIME DEFAULT NULL,
          reminded INT NOT NULL,
          request_from_france TINYINT(1) DEFAULT \'1\' NOT NULL,
          state VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          reachable TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_9769FD842F1B6663 (found_proxy_id),
          INDEX IDX_9769FD84888FDEEE (
            procuration_request_found_by_id
          ),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE procuration_requests_to_election_rounds (
          procuration_request_id INT NOT NULL,
          election_round_id INT NOT NULL,
          INDEX IDX_A47BBD53128D9C53 (procuration_request_id),
          INDEX IDX_A47BBD53FCBF5E32 (election_round_id),
          PRIMARY KEY(
            procuration_request_id, election_round_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE programmatic_foundation_approach (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          position SMALLINT NOT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE programmatic_foundation_measure (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          sub_approach_id INT UNSIGNED DEFAULT NULL,
          position SMALLINT NOT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          is_leading TINYINT(1) NOT NULL,
          is_expanded TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_213A5F1EF0ED738A (sub_approach_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE programmatic_foundation_measure_tag (
          measure_id INT UNSIGNED NOT NULL,
          tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_F004297F5DA37D00 (measure_id),
          INDEX IDX_F004297FBAD26311 (tag_id),
          PRIMARY KEY(measure_id, tag_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE programmatic_foundation_project (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          measure_id INT UNSIGNED DEFAULT NULL,
          position SMALLINT NOT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          city VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          is_expanded TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_8E8E96D55DA37D00 (measure_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE programmatic_foundation_project_tag (
          project_id INT UNSIGNED NOT NULL,
          tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_9F63872166D1F9C (project_id),
          INDEX IDX_9F63872BAD26311 (tag_id),
          PRIMARY KEY(project_id, tag_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE programmatic_foundation_sub_approach (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          approach_id INT UNSIGNED DEFAULT NULL,
          position SMALLINT NOT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          subtitle VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_expanded TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_735C1D0115140614 (approach_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE programmatic_foundation_tag (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          label VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_12127927EA750E8 (label),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE projection_managed_users (
          id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
          status SMALLINT NOT NULL,
          type VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          original_id BIGINT UNSIGNED NOT NULL,
          email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          age SMALLINT DEFAULT NULL,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          committees LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_committee_member TINYINT(1) NOT NULL,
          is_committee_host TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          is_committee_supervisor TINYINT(1) NOT NULL,
          subscribed_tags LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          committee_postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          interests LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          supervisor_tags LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          subscription_types LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          adherent_uuid CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          committee_uuids LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          vote_committee_id INT DEFAULT NULL,
          address VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          certified_at DATETIME DEFAULT NULL,
          is_committee_provisional_supervisor TINYINT(1) NOT NULL,
          INDEX IDX_90A7D656108B7592 (original_id),
          INDEX projection_managed_users_search (status, postal_code, country),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE projection_managed_users_zone (
          managed_user_id BIGINT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_E4D4ADCD9F2C3FAB (zone_id),
          INDEX IDX_E4D4ADCDC679DD78 (managed_user_id),
          PRIMARY KEY(managed_user_id, zone_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE proposal_proposal_theme (
          proposal_id INT NOT NULL,
          proposal_theme_id INT NOT NULL,
          INDEX IDX_6B80CE41B85948AF (proposal_theme_id),
          INDEX IDX_6B80CE41F4792058 (proposal_id),
          PRIMARY KEY(proposal_id, proposal_theme_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE proposals (
          id INT AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          position SMALLINT NOT NULL,
          published TINYINT(1) NOT NULL,
          display_media TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          deleted_at DATETIME DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          keywords VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_A5BA3A8FEA9FDD75 (media_id),
          UNIQUE INDEX UNIQ_A5BA3A8F989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE proposals_themes (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          color VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE push_token (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          device_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          identifier VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          source VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_51BC138125F06C53 (adherent_id),
          INDEX IDX_51BC138194A4C7D4 (device_id),
          UNIQUE INDEX UNIQ_51BC1381772E836A (identifier),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE qr_code (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_id INT DEFAULT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          redirect_url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          count INT NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_7D8B1FB5B03A8386 (created_by_id),
          UNIQUE INDEX qr_code_name (name),
          UNIQUE INDEX qr_code_uuid (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE redirections (
          id INT AUTO_INCREMENT NOT NULL,
          url_from VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          url_to VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          type INT NOT NULL,
          updated_at DATETIME NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE referent (
          id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          gender VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          email_address VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          facebook_page_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_page_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          geojson LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          area_label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT \'DISABLED\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          display_media TINYINT(1) NOT NULL,
          INDEX IDX_FE9AAC6CEA9FDD75 (media_id),
          UNIQUE INDEX referent_slug_unique (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE referent_area (
          id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL,
          area_code VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          area_type VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          keywords LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX referent_area_area_code_unique (area_code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE referent_areas (
          referent_id SMALLINT UNSIGNED NOT NULL,
          area_id SMALLINT UNSIGNED NOT NULL,
          INDEX IDX_75CEBC6C35E47E35 (referent_id),
          INDEX IDX_75CEBC6CBD0F409C (area_id),
          PRIMARY KEY(referent_id, area_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE referent_managed_areas (
          id INT AUTO_INCREMENT NOT NULL,
          marker_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          marker_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE referent_managed_areas_tags (
          referent_managed_area_id INT NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_8BE84DD56B99CC25 (referent_managed_area_id),
          INDEX IDX_8BE84DD59C262DB3 (referent_tag_id),
          PRIMARY KEY(
            referent_managed_area_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE referent_managed_users_message (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          subject VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          include_adherents_no_committee TINYINT(1) DEFAULT \'0\' NOT NULL,
          include_adherents_in_committee TINYINT(1) DEFAULT \'0\' NOT NULL,
          include_hosts TINYINT(1) DEFAULT \'0\' NOT NULL,
          include_supervisors TINYINT(1) DEFAULT \'0\' NOT NULL,
          query_area_code LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          query_id LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          offset BIGINT NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          interests LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          gender VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          age_minimum INT DEFAULT NULL,
          age_maximum INT DEFAULT NULL,
          registered_from DATE DEFAULT NULL,
          registered_to DATE DEFAULT NULL,
          query_zone VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_1E41AC6125F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE referent_person_link (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          person_organizational_chart_item_id INT UNSIGNED DEFAULT NULL,
          referent_id SMALLINT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          first_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          phone VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_jecoute_manager TINYINT(1) DEFAULT \'0\' NOT NULL,
          is_municipal_manager_supervisor TINYINT(1) DEFAULT \'0\' NOT NULL,
          co_referent VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          restricted_cities LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          INDEX IDX_BC75A60A25F06C53 (adherent_id),
          INDEX IDX_BC75A60A35E47E35 (referent_id),
          INDEX IDX_BC75A60A810B5A42 (
            person_organizational_chart_item_id
          ),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE referent_person_link_committee (
          referent_person_link_id INT UNSIGNED NOT NULL,
          committee_id INT UNSIGNED NOT NULL,
          INDEX IDX_1C97B2A5B3E4DE86 (referent_person_link_id),
          INDEX IDX_1C97B2A5ED1A100B (committee_id),
          PRIMARY KEY(
            referent_person_link_id, committee_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE referent_space_access_information (
          id INT AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          previous_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          last_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          UNIQUE INDEX UNIQ_CD8FDF4825F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE referent_tags (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          code VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          external_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_135D29D99F2C3FAB (zone_id),
          UNIQUE INDEX referent_tag_code_unique (code),
          UNIQUE INDEX referent_tag_name_unique (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE referent_team_member (
          id INT AUTO_INCREMENT NOT NULL,
          member_id INT UNSIGNED NOT NULL,
          referent_id INT UNSIGNED NOT NULL,
          limited TINYINT(1) DEFAULT \'0\' NOT NULL,
          restricted_cities LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          INDEX IDX_6C0067135E47E35 (referent_id),
          UNIQUE INDEX UNIQ_6C006717597D3FE (member_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE referent_team_member_committee (
          referent_team_member_id INT NOT NULL,
          committee_id INT UNSIGNED NOT NULL,
          INDEX IDX_EC89860BED1A100B (committee_id),
          INDEX IDX_EC89860BFE4CA267 (referent_team_member_id),
          PRIMARY KEY(
            referent_team_member_id, committee_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE referent_user_filter_referent_tag (
          referent_user_filter_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_F2BB20FE9C262DB3 (referent_tag_id),
          INDEX IDX_F2BB20FEEFAB50C4 (referent_user_filter_id),
          PRIMARY KEY(
            referent_user_filter_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE region (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          country VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_F62F17677153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE reports (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          committee_id INT UNSIGNED DEFAULT NULL,
          community_event_id INT UNSIGNED DEFAULT NULL,
          reasons LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json_array)\',
          comment LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(16) CHARACTER SET utf8mb4 DEFAULT \'unresolved\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          resolved_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_F11FA74583B12DAC (community_event_id),
          INDEX IDX_F11FA745ED1A100B (committee_id),
          INDEX IDX_F11FA745F675F31B (author_id),
          INDEX report_status_idx (status),
          INDEX report_type_idx (type),
          UNIQUE INDEX report_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE republican_silence (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          begin_at DATETIME NOT NULL,
          finish_at DATETIME NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE republican_silence_referent_tag (
          republican_silence_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_543DED2612359909 (republican_silence_id),
          INDEX IDX_543DED269C262DB3 (referent_tag_id),
          PRIMARY KEY(
            republican_silence_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE roles (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          code VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX board_member_role_code_unique (code),
          UNIQUE INDEX board_member_role_name_unique (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE running_mate_request_application_request_tag (
          running_mate_request_id INT UNSIGNED NOT NULL,
          application_request_tag_id INT NOT NULL,
          INDEX IDX_9D534FCF9644FEDA (application_request_tag_id),
          INDEX IDX_9D534FCFCEDF4387 (running_mate_request_id),
          PRIMARY KEY(
            running_mate_request_id, application_request_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE running_mate_request_referent_tag (
          running_mate_request_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_53AB4FAB9C262DB3 (referent_tag_id),
          INDEX IDX_53AB4FABCEDF4387 (running_mate_request_id),
          PRIMARY KEY(
            running_mate_request_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE running_mate_request_theme (
          running_mate_request_id INT UNSIGNED NOT NULL,
          theme_id INT NOT NULL,
          INDEX IDX_A732622759027487 (theme_id),
          INDEX IDX_A7326227CEDF4387 (running_mate_request_id),
          PRIMARY KEY(
            running_mate_request_id, theme_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE saved_board_members (
          board_member_owner_id INT NOT NULL,
          board_member_saved_id INT NOT NULL,
          INDEX IDX_32865A324821D202 (board_member_saved_id),
          INDEX IDX_32865A32FDCCD727 (board_member_owner_id),
          PRIMARY KEY(
            board_member_owner_id, board_member_saved_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE scope (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          features LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          apps LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          UNIQUE INDEX scope_code_unique (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE senator_area (
          id INT AUTO_INCREMENT NOT NULL,
          department_tag_id INT UNSIGNED DEFAULT NULL,
          INDEX IDX_D229BBF7AEC89CE1 (department_tag_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE senatorial_candidate_areas (
          id INT AUTO_INCREMENT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE senatorial_candidate_areas_tags (
          senatorial_candidate_area_id INT NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_F83208FA9C262DB3 (referent_tag_id),
          INDEX IDX_F83208FAA7BF84E8 (senatorial_candidate_area_id),
          PRIMARY KEY(
            senatorial_candidate_area_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE sms_campaign (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          audience_id INT UNSIGNED DEFAULT NULL,
          administrator_id INT DEFAULT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          recipient_count INT DEFAULT NULL,
          response_payload LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          external_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          sent_at DATETIME DEFAULT NULL,
          adherent_count INT DEFAULT NULL,
          INDEX IDX_79E333DC4B09E92C (administrator_id),
          UNIQUE INDEX UNIQ_79E333DC848CC616 (audience_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE sms_stop_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          event_date DATETIME DEFAULT NULL,
          campaign_external_id INT DEFAULT NULL,
          receiver VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE social_share_categories (
          id BIGINT AUTO_INCREMENT NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          position SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE social_shares (
          id BIGINT AUTO_INCREMENT NOT NULL,
          social_share_category_id BIGINT DEFAULT NULL,
          media_id BIGINT DEFAULT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          type VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          position SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          default_url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          facebook_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          published TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_8E1413A085040FAD (social_share_category_id),
          INDEX IDX_8E1413A0EA9FDD75 (media_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE subscription_type (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          external_id VARCHAR(64) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          position SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          INDEX IDX_BBE2473777153098 (code),
          UNIQUE INDEX UNIQ_BBE2473777153098 (code),
          UNIQUE INDEX UNIQ_BBE247379F75D7B0 (external_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE team (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          created_by_adherent_id INT UNSIGNED DEFAULT NULL,
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_C4E0A61F85C9D733 (created_by_adherent_id),
          INDEX IDX_C4E0A61F9DF5350C (created_by_administrator_id),
          INDEX IDX_C4E0A61FCF1918FF (updated_by_administrator_id),
          INDEX IDX_C4E0A61FDF6CFDC9 (updated_by_adherent_id),
          UNIQUE INDEX team_name_unique (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE team_member (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          team_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_6FFBDA125F06C53 (adherent_id),
          INDEX IDX_6FFBDA1296CD8AE (team_id),
          UNIQUE INDEX team_member_unique (team_id, adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE team_member_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          team_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          administrator_id INT DEFAULT NULL,
          team_manager_id INT UNSIGNED DEFAULT NULL,
          action VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_1F330628296CD8AE (team_id),
          INDEX team_member_history_adherent_id_idx (adherent_id),
          INDEX team_member_history_administrator_id_idx (administrator_id),
          INDEX team_member_history_date_idx (date),
          INDEX team_member_history_team_manager_id_idx (team_manager_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          current_designation_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          codes VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          mailchimp_id INT DEFAULT NULL,
          is_active TINYINT(1) DEFAULT \'1\' NOT NULL,
          INDEX IDX_B6DCA2A5B4D2A5D1 (current_designation_id),
          UNIQUE INDEX territorial_council_codes_unique (codes),
          UNIQUE INDEX territorial_council_name_unique (name),
          UNIQUE INDEX territorial_council_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_candidacies_group (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_candidacy (
          id INT AUTO_INCREMENT NOT NULL,
          election_id INT UNSIGNED NOT NULL,
          membership_id INT UNSIGNED NOT NULL,
          candidacies_group_id INT UNSIGNED DEFAULT NULL,
          gender VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          biography LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          faith_statement LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_public_faith_statement TINYINT(1) DEFAULT \'0\' NOT NULL,
          quality VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_39885B61FB354CD (membership_id),
          INDEX IDX_39885B6A708DAFF (election_id),
          INDEX IDX_39885B6FC1537C1 (candidacies_group_id),
          UNIQUE INDEX UNIQ_39885B6D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_candidacy_invitation (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          membership_id INT UNSIGNED NOT NULL,
          candidacy_id INT NOT NULL,
          status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          accepted_at DATETIME DEFAULT NULL,
          declined_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_DA86009A1FB354CD (membership_id),
          INDEX IDX_DA86009A59B22434 (candidacy_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_convocation (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          territorial_council_id INT UNSIGNED DEFAULT NULL,
          political_committee_id INT UNSIGNED DEFAULT NULL,
          created_by_id INT UNSIGNED DEFAULT NULL,
          meeting_start_date DATETIME NOT NULL,
          meeting_end_date DATETIME NOT NULL,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          mode VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          meeting_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          address_address VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_insee VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_region VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_geocodable_hash VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_A9919BF0AAA61A99 (territorial_council_id),
          INDEX IDX_A9919BF0B03A8386 (created_by_id),
          INDEX IDX_A9919BF0C7A72 (political_committee_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_election (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          territorial_council_id INT UNSIGNED DEFAULT NULL,
          designation_id INT UNSIGNED DEFAULT NULL,
          election_poll_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          election_mode VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          meeting_start_date DATETIME DEFAULT NULL,
          meeting_end_date DATETIME DEFAULT NULL,
          address_address VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_insee VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_region VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_geocodable_hash VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          meeting_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_14CBC36BAAA61A99 (territorial_council_id),
          INDEX IDX_14CBC36BFAC7D83F (designation_id),
          UNIQUE INDEX UNIQ_14CBC36B8649F5F1 (election_poll_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_election_poll (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          gender VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_election_poll_choice (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          election_poll_id INT UNSIGNED NOT NULL,
          value VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_63EBCF6B8649F5F1 (election_poll_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_election_poll_vote (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          choice_id INT UNSIGNED DEFAULT NULL,
          membership_id INT UNSIGNED NOT NULL,
          created_at DATETIME NOT NULL,
          INDEX IDX_BCDA0C151FB354CD (membership_id),
          INDEX IDX_BCDA0C15998666D1 (choice_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_feed_item (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          territorial_council_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          is_locked TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_45241D62AAA61A99 (territorial_council_id),
          INDEX IDX_45241D62F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_membership (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          territorial_council_id INT UNSIGNED NOT NULL,
          joined_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_2A998316AAA61A99 (territorial_council_id),
          UNIQUE INDEX UNIQ_2A99831625F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_membership_log (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          type VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(500) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          quality_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          actual_territorial_council VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          actual_quality_names LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          found_territorial_councils LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          created_at DATETIME NOT NULL,
          is_resolved TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_2F6D242025F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_official_report (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          political_committee_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          created_by_id INT UNSIGNED DEFAULT NULL,
          updated_by_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_8D80D385896DBBDE (updated_by_id),
          INDEX IDX_8D80D385B03A8386 (created_by_id),
          INDEX IDX_8D80D385C7A72 (political_committee_id),
          INDEX IDX_8D80D385F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_official_report_document (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_id INT UNSIGNED DEFAULT NULL,
          report_id INT UNSIGNED DEFAULT NULL,
          filename VARCHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          extension VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          mime_type VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          version SMALLINT UNSIGNED NOT NULL,
          created_at DATETIME NOT NULL,
          INDEX IDX_78C1161D4BD2A4C0 (report_id),
          INDEX IDX_78C1161DB03A8386 (created_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_quality (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          territorial_council_membership_id INT UNSIGNED NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          zone VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          joined_at DATETIME NOT NULL,
          INDEX IDX_C018E022E797FAB0 (
            territorial_council_membership_id
          ),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_referent_tag (
          territorial_council_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_78DBEB909C262DB3 (referent_tag_id),
          INDEX IDX_78DBEB90AAA61A99 (territorial_council_id),
          PRIMARY KEY(
            territorial_council_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE territorial_council_zone (
          territorial_council_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_9467B41E9F2C3FAB (zone_id),
          INDEX IDX_9467B41EAAA61A99 (territorial_council_id),
          PRIMARY KEY(territorial_council_id, zone_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE thematic_community (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          enabled TINYINT(1) NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          canonical_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE thematic_community_contact (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          birth_date DATE DEFAULT NULL,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          activity_area VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          job_area VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          job VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          address_address VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_insee VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_region VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_geocodable_hash VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          custom_gender VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          position VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE thematic_community_membership (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          community_id INT UNSIGNED DEFAULT NULL,
          contact_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          joined_at DATETIME NOT NULL,
          association TINYINT(1) DEFAULT \'0\' NOT NULL,
          association_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          expert TINYINT(1) DEFAULT \'0\' NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          has_job TINYINT(1) DEFAULT \'0\' NOT NULL,
          job VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          motivations LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_22B6AC0525F06C53 (adherent_id),
          INDEX IDX_22B6AC05E7A1254A (contact_id),
          INDEX IDX_22B6AC05FDA7B0BF (community_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE thematic_community_membership_user_list_definition (
          thematic_community_membership_id INT UNSIGNED NOT NULL,
          user_list_definition_id INT UNSIGNED NOT NULL,
          INDEX IDX_58815EB9403AE2A5 (
            thematic_community_membership_id
          ),
          INDEX IDX_58815EB9F74563E3 (user_list_definition_id),
          PRIMARY KEY(
            thematic_community_membership_id,
            user_list_definition_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE timeline_manifesto_translations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          translatable_id BIGINT DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          locale VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_F7BD6C172C2AC5D3 (translatable_id),
          UNIQUE INDEX timeline_manifesto_translations_unique_translation (translatable_id, locale),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE timeline_manifestos (
          id BIGINT AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          display_media TINYINT(1) NOT NULL,
          INDEX IDX_C6ED4403EA9FDD75 (media_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE timeline_measure_translations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          translatable_id BIGINT DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          locale VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_5C9EB6072C2AC5D3 (translatable_id),
          UNIQUE INDEX timeline_measure_translations_unique_translation (translatable_id, locale),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE timeline_measures (
          id BIGINT AUTO_INCREMENT NOT NULL,
          manifesto_id BIGINT NOT NULL,
          link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          updated_at DATETIME NOT NULL,
          major TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_BA475ED737E924 (manifesto_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE timeline_measures_profiles (
          measure_id BIGINT NOT NULL,
          profile_id BIGINT NOT NULL,
          INDEX IDX_B83D81AE5DA37D00 (measure_id),
          INDEX IDX_B83D81AECCFA12B8 (profile_id),
          PRIMARY KEY(measure_id, profile_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE timeline_profile_translations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          translatable_id BIGINT DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          locale VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_41B3A6DA2C2AC5D3 (translatable_id),
          UNIQUE INDEX timeline_profile_translations_unique_translation (translatable_id, locale),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE timeline_profiles (
          id BIGINT AUTO_INCREMENT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE timeline_theme_translations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          translatable_id BIGINT DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          locale VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_F81F72932C2AC5D3 (translatable_id),
          UNIQUE INDEX timeline_theme_translations_unique_translation (translatable_id, locale),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE timeline_themes (
          id BIGINT AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          featured TINYINT(1) DEFAULT \'0\' NOT NULL,
          display_media TINYINT(1) NOT NULL,
          INDEX IDX_8ADDB8F6EA9FDD75 (media_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE timeline_themes_measures (
          theme_id BIGINT NOT NULL,
          measure_id BIGINT NOT NULL,
          INDEX IDX_EB8A7B0C59027487 (theme_id),
          INDEX IDX_EB8A7B0C5DA37D00 (measure_id),
          PRIMARY KEY(measure_id, theme_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE ton_macron_choices (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          step SMALLINT UNSIGNED NOT NULL,
          content_key VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          label VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX ton_macron_choices_content_key_unique (content_key),
          UNIQUE INDEX ton_macron_choices_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE ton_macron_friend_invitation_has_choices (
          invitation_id INT UNSIGNED NOT NULL,
          choice_id INT UNSIGNED NOT NULL,
          INDEX IDX_BB3BCAEE998666D1 (choice_id),
          INDEX IDX_BB3BCAEEA35D7AF0 (invitation_id),
          PRIMARY KEY(invitation_id, choice_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE ton_macron_friend_invitations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          friend_first_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          friend_age SMALLINT UNSIGNED NOT NULL,
          friend_gender VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          friend_position VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          friend_email_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          author_first_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          author_last_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          author_email_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          mail_subject VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          mail_body LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX ton_macron_friend_invitations_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE unregistration_referent_tag (
          unregistration_id INT NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_59B7AC414D824CA (unregistration_id),
          INDEX IDX_59B7AC49C262DB3 (referent_tag_id),
          PRIMARY KEY(
            unregistration_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE unregistrations (
          id INT AUTO_INCREMENT NOT NULL,
          excluded_by_id INT DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          reasons LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json_array)\',
          comment LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          registered_at DATETIME NOT NULL,
          unregistered_at DATETIME NOT NULL,
          is_adherent TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_F9E4AA0C5B30B80B (excluded_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE user_authorizations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          user_id INT UNSIGNED DEFAULT NULL,
          client_id INT UNSIGNED DEFAULT NULL,
          scopes LONGTEXT NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_4044823019EB6921 (client_id),
          INDEX IDX_40448230A76ED395 (user_id),
          UNIQUE INDEX user_authorizations_unique (user_id, client_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE user_documents (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          original_name VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          extension VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          size INT NOT NULL,
          mime_type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          type VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX document_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE user_list_definition (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          type VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          code VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          label VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          color VARCHAR(7) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX user_list_definition_type_code_unique (type, code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE volunteer_request_application_request_tag (
          volunteer_request_id INT UNSIGNED NOT NULL,
          application_request_tag_id INT NOT NULL,
          INDEX IDX_6F3FA2699644FEDA (application_request_tag_id),
          INDEX IDX_6F3FA269B8D6887 (volunteer_request_id),
          PRIMARY KEY(
            volunteer_request_id, application_request_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE volunteer_request_referent_tag (
          volunteer_request_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_DA2917429C262DB3 (referent_tag_id),
          INDEX IDX_DA291742B8D6887 (volunteer_request_id),
          PRIMARY KEY(
            volunteer_request_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE volunteer_request_technical_skill (
          volunteer_request_id INT UNSIGNED NOT NULL,
          technical_skill_id INT NOT NULL,
          INDEX IDX_7F8C5C1EB8D6887 (volunteer_request_id),
          INDEX IDX_7F8C5C1EE98F0EFD (technical_skill_id),
          PRIMARY KEY(
            volunteer_request_id, technical_skill_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE volunteer_request_theme (
          volunteer_request_id INT UNSIGNED NOT NULL,
          theme_id INT NOT NULL,
          INDEX IDX_5427AF5359027487 (theme_id),
          INDEX IDX_5427AF53B8D6887 (volunteer_request_id),
          PRIMARY KEY(volunteer_request_id, theme_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE vote_place (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          address VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_code LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          country VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          holder_office_available TINYINT(1) NOT NULL,
          substitute_office_available TINYINT(1) NOT NULL,
          alias VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          enabled TINYINT(1) DEFAULT \'1\' NOT NULL,
          UNIQUE INDEX UNIQ_2574310677153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE vote_result (
          id INT AUTO_INCREMENT NOT NULL,
          vote_place_id INT DEFAULT NULL,
          election_round_id INT NOT NULL,
          created_by_id INT UNSIGNED DEFAULT NULL,
          updated_by_id INT UNSIGNED DEFAULT NULL,
          city_id INT UNSIGNED DEFAULT NULL,
          registered INT NOT NULL,
          abstentions INT NOT NULL,
          participated INT NOT NULL,
          expressed INT NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX city_vote_result_city_round_unique (city_id, election_round_id),
          INDEX IDX_1F8DB349896DBBDE (updated_by_id),
          INDEX IDX_1F8DB3498BAC62AF (city_id),
          INDEX IDX_1F8DB349B03A8386 (created_by_id),
          INDEX IDX_1F8DB349F3F90B30 (vote_place_id),
          INDEX IDX_1F8DB349FCBF5E32 (election_round_id),
          UNIQUE INDEX vote_place_result_city_round_unique (
            vote_place_id, election_round_id
          ),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE vote_result_list (
          id INT AUTO_INCREMENT NOT NULL,
          list_collection_id INT DEFAULT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          nuance VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          adherent_count INT DEFAULT NULL,
          eligible_count INT DEFAULT NULL,
          position INT DEFAULT NULL,
          candidate_first_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          candidate_last_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          outgoing_mayor TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_677ED502DB567AF4 (list_collection_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE vote_result_list_collection (
          id INT AUTO_INCREMENT NOT NULL,
          city_id INT UNSIGNED DEFAULT NULL,
          election_round_id INT DEFAULT NULL,
          INDEX IDX_9C1DD9638BAC62AF (city_id),
          INDEX IDX_9C1DD963FCBF5E32 (election_round_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_candidate (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          candidate_group_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          biography LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          image_path VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          faith_statement LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          additionally_elected TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_3F426D6D25F06C53 (adherent_id),
          INDEX IDX_3F426D6D5F0A9B94 (candidate_group_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_candidate_group (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          election_pool_id INT DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          elected TINYINT(1) DEFAULT \'0\' NOT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_2C1A353AC1E98F21 (election_pool_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_candidate_group_result (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          candidate_group_id INT UNSIGNED DEFAULT NULL,
          election_pool_result_id INT UNSIGNED DEFAULT NULL,
          total INT UNSIGNED DEFAULT 0 NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          total_mentions LONGTEXT DEFAULT NULL,
          majority_mention VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_7249D5375F0A9B94 (candidate_group_id),
          INDEX IDX_7249D537B5BA5CC5 (election_pool_result_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_election (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          designation_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          closed_at DATETIME DEFAULT NULL,
          second_round_end_date DATETIME DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          additional_places SMALLINT UNSIGNED DEFAULT NULL,
          additional_places_gender VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_4E144C94FAC7D83F (designation_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_election_entity (
          id INT AUTO_INCREMENT NOT NULL,
          committee_id INT UNSIGNED DEFAULT NULL,
          election_id INT UNSIGNED DEFAULT NULL,
          territorial_council_id INT UNSIGNED DEFAULT NULL,
          INDEX IDX_7AAD259FAAA61A99 (territorial_council_id),
          INDEX IDX_7AAD259FED1A100B (committee_id),
          UNIQUE INDEX UNIQ_7AAD259FA708DAFF (election_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_election_pool (
          id INT AUTO_INCREMENT NOT NULL,
          election_id INT UNSIGNED DEFAULT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_7225D6EFA708DAFF (election_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_election_pool_result (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          election_pool_id INT DEFAULT NULL,
          election_round_result_id INT UNSIGNED DEFAULT NULL,
          is_elected TINYINT(1) DEFAULT \'0\' NOT NULL,
          expressed INT UNSIGNED DEFAULT 0 NOT NULL,
          blank INT UNSIGNED DEFAULT 0 NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_13C1C73F8FFC0F0B (election_round_result_id),
          INDEX IDX_13C1C73FC1E98F21 (election_pool_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_election_result (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          election_id INT UNSIGNED DEFAULT NULL,
          participated INT UNSIGNED DEFAULT 0 NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_67EFA0E4A708DAFF (election_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_election_round (
          id INT AUTO_INCREMENT NOT NULL,
          election_id INT UNSIGNED DEFAULT NULL,
          is_active TINYINT(1) DEFAULT \'1\' NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_F15D87B7A708DAFF (election_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_election_round_election_pool (
          election_round_id INT NOT NULL,
          election_pool_id INT NOT NULL,
          INDEX IDX_E6665F19C1E98F21 (election_pool_id),
          INDEX IDX_E6665F19FCBF5E32 (election_round_id),
          PRIMARY KEY(
            election_round_id, election_pool_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_election_round_result (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          election_round_id INT DEFAULT NULL,
          election_result_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_F267096619FCFB29 (election_result_id),
          UNIQUE INDEX UNIQ_F2670966FCBF5E32 (election_round_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_vote (
          id INT AUTO_INCREMENT NOT NULL,
          voter_id INT DEFAULT NULL,
          election_round_id INT DEFAULT NULL,
          voted_at DATETIME NOT NULL,
          INDEX IDX_DCBB2B7BEBB4B8AD (voter_id),
          INDEX IDX_DCBB2B7BFCBF5E32 (election_round_id),
          UNIQUE INDEX unique_vote (voter_id, election_round_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_vote_choice (
          id INT AUTO_INCREMENT NOT NULL,
          vote_result_id INT DEFAULT NULL,
          candidate_group_id INT UNSIGNED DEFAULT NULL,
          election_pool_id INT DEFAULT NULL,
          is_blank TINYINT(1) DEFAULT \'0\' NOT NULL,
          mention VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_B009F31145EB7186 (vote_result_id),
          INDEX IDX_B009F3115F0A9B94 (candidate_group_id),
          INDEX IDX_B009F311C1E98F21 (election_pool_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_vote_result (
          id INT AUTO_INCREMENT NOT NULL,
          election_round_id INT DEFAULT NULL,
          voter_key VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          voted_at DATETIME NOT NULL,
          INDEX IDX_62C86890FCBF5E32 (election_round_id),
          UNIQUE INDEX unique_vote (voter_key, election_round_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_voter (
          id INT AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          created_at DATETIME NOT NULL,
          is_ghost TINYINT(1) DEFAULT \'0\' NOT NULL,
          UNIQUE INDEX UNIQ_AB02EC0225F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_voters_list (
          id INT AUTO_INCREMENT NOT NULL,
          election_id INT UNSIGNED DEFAULT NULL,
          UNIQUE INDEX UNIQ_3C73500DA708DAFF (election_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE voting_platform_voters_list_voter (
          voters_list_id INT NOT NULL,
          voter_id INT NOT NULL,
          INDEX IDX_7CC26956EBB4B8AD (voter_id),
          INDEX IDX_7CC26956FB0C8C84 (voters_list_id),
          PRIMARY KEY(voters_list_id, voter_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('CREATE TABLE web_hooks (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          client_id INT UNSIGNED NOT NULL,
          event VARCHAR(64) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          callbacks LONGTEXT NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          service VARCHAR(64) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_CDB836AD19EB6921 (client_id),
          UNIQUE INDEX web_hook_event_client_id_unique (event, client_id),
          UNIQUE INDEX web_hook_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');

        $this->addSql('ALTER TABLE
          adherent_certification_histories
        ADD
          CONSTRAINT FK_732EE81A25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_certification_histories
        ADD
          CONSTRAINT FK_732EE81A4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_charter
        ADD
          CONSTRAINT FK_D6F94F2B25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          adherent_commitment
        ADD
          CONSTRAINT FK_D239EF6F25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_email_subscribe_token
        ADD
          CONSTRAINT FK_376DBA0F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          adherent_email_subscribe_token
        ADD
          CONSTRAINT FK_376DBA09DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_email_subscribe_token
        ADD
          CONSTRAINT FK_376DBA0CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_email_subscription_histories
        ADD
          CONSTRAINT FK_51AD8354B6596C08 FOREIGN KEY (subscription_type_id) REFERENCES subscription_type (id)');
        $this->addSql('ALTER TABLE
          adherent_email_subscription_history_referent_tag
        ADD
          CONSTRAINT FK_6FFBE6E88FCB8132 FOREIGN KEY (email_subscription_history_id) REFERENCES adherent_email_subscription_histories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_email_subscription_history_referent_tag
        ADD
          CONSTRAINT FK_6FFBE6E89C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_instance_quality
        ADD
          CONSTRAINT FK_D63B17FA25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_instance_quality
        ADD
          CONSTRAINT FK_D63B17FAA623BBD7 FOREIGN KEY (instance_quality_id) REFERENCES instance_quality (id)');
        $this->addSql('ALTER TABLE
          adherent_instance_quality
        ADD
          CONSTRAINT FK_D63B17FA9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          adherent_instance_quality
        ADD
          CONSTRAINT FK_D63B17FAAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id)');
        $this->addSql('ALTER TABLE
          adherent_mandate
        ADD
          CONSTRAINT FK_9C0C3D6025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_mandate
        ADD
          CONSTRAINT FK_9C0C3D60ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_mandate
        ADD
          CONSTRAINT FK_9C0C3D60AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F9466E2221E FOREIGN KEY (cause_id) REFERENCES cause (id)');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F94FAF04979 FOREIGN KEY (adherent_segment_id) REFERENCES adherent_segment (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F94DB296AAD FOREIGN KEY (segment_id) REFERENCES audience_segment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F949F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F94ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F949C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F94AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id)');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F94C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id)');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F94F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id)');
        $this->addSql('ALTER TABLE
          referent_user_filter_referent_tag
        ADD
          CONSTRAINT FK_F2BB20FEEFAB50C4 FOREIGN KEY (referent_user_filter_id) REFERENCES adherent_message_filters (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_user_filter_referent_tag
        ADD
          CONSTRAINT FK_F2BB20FE9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_messages
        ADD
          CONSTRAINT FK_D187C183F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          adherent_messages
        ADD
          CONSTRAINT FK_D187C183D395B25E FOREIGN KEY (filter_id) REFERENCES adherent_message_filters (id)');
        $this->addSql('ALTER TABLE
          adherent_segment
        ADD
          CONSTRAINT FK_9DF0C7EBF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA39BF75CAD FOREIGN KEY (
            legislative_candidate_managed_district_id
          ) REFERENCES districts (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3DC184E71 FOREIGN KEY (managed_area_id) REFERENCES referent_managed_areas (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA31A912B27 FOREIGN KEY (coordinator_committee_area_id) REFERENCES coordinator_managed_areas (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA339054338 FOREIGN KEY (procuration_managed_area_id) REFERENCES procuration_managed_areas (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3E1B55931 FOREIGN KEY (assessor_managed_area_id) REFERENCES assessor_managed_areas (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3E4A5D7A5 FOREIGN KEY (assessor_role_id) REFERENCES assessor_role_association (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA379DE69AA FOREIGN KEY (municipal_manager_role_id) REFERENCES municipal_manager_role_association (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA39801977F FOREIGN KEY (
            municipal_manager_supervisor_role_id
          ) REFERENCES municipal_manager_supervisor_role (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA38828ED30 FOREIGN KEY (coalition_moderator_role_id) REFERENCES coalition_moderator_role_association (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA394E3BB99 FOREIGN KEY (jecoute_managed_area_id) REFERENCES jecoute_managed_areas (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3A132C3C5 FOREIGN KEY (managed_district_id) REFERENCES districts (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3CC72679B FOREIGN KEY (
            municipal_chief_managed_area_id
          ) REFERENCES municipal_chief_areas (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3FCCAF6D5 FOREIGN KEY (
            senatorial_candidate_managed_area_id
          ) REFERENCES senatorial_candidate_areas (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA379645AD5 FOREIGN KEY (lre_area_id) REFERENCES lre_area (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA37657F304 FOREIGN KEY (candidate_managed_area_id) REFERENCES candidate_managed_area (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA393494FA8 FOREIGN KEY (senator_area_id) REFERENCES senator_area (id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3122E5FF4 FOREIGN KEY (consular_managed_area_id) REFERENCES consular_managed_area (id)');
        $this->addSql('ALTER TABLE
          adherent_subscription_type
        ADD
          CONSTRAINT FK_F93DC28A25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_subscription_type
        ADD
          CONSTRAINT FK_F93DC28AB6596C08 FOREIGN KEY (subscription_type_id) REFERENCES subscription_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_adherent_tag
        ADD
          CONSTRAINT FK_DD297F8225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_adherent_tag
        ADD
          CONSTRAINT FK_DD297F82AED03543 FOREIGN KEY (adherent_tag_id) REFERENCES adherent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_thematic_community
        ADD
          CONSTRAINT FK_DAB0B4EC25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_thematic_community
        ADD
          CONSTRAINT FK_DAB0B4EC1BE5825E FOREIGN KEY (thematic_community_id) REFERENCES thematic_community (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_referent_tag
        ADD
          CONSTRAINT FK_79E8AFFD25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_referent_tag
        ADD
          CONSTRAINT FK_79E8AFFD9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_zone
        ADD
          CONSTRAINT FK_1C14D08525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_zone
        ADD
          CONSTRAINT FK_1C14D0859F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          administrator_export_history
        ADD
          CONSTRAINT FK_10499F014B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          application_request_running_mate
        ADD
          CONSTRAINT FK_D1D6095625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          running_mate_request_theme
        ADD
          CONSTRAINT FK_A7326227CEDF4387 FOREIGN KEY (running_mate_request_id) REFERENCES application_request_running_mate (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          running_mate_request_theme
        ADD
          CONSTRAINT FK_A732622759027487 FOREIGN KEY (theme_id) REFERENCES application_request_theme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          running_mate_request_application_request_tag
        ADD
          CONSTRAINT FK_9D534FCFCEDF4387 FOREIGN KEY (running_mate_request_id) REFERENCES application_request_running_mate (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          running_mate_request_application_request_tag
        ADD
          CONSTRAINT FK_9D534FCF9644FEDA FOREIGN KEY (application_request_tag_id) REFERENCES application_request_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          running_mate_request_referent_tag
        ADD
          CONSTRAINT FK_53AB4FABCEDF4387 FOREIGN KEY (running_mate_request_id) REFERENCES application_request_running_mate (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          running_mate_request_referent_tag
        ADD
          CONSTRAINT FK_53AB4FAB9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          application_request_volunteer
        ADD
          CONSTRAINT FK_1139657025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_technical_skill
        ADD
          CONSTRAINT FK_7F8C5C1EB8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_technical_skill
        ADD
          CONSTRAINT FK_7F8C5C1EE98F0EFD FOREIGN KEY (technical_skill_id) REFERENCES application_request_technical_skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_theme
        ADD
          CONSTRAINT FK_5427AF53B8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_theme
        ADD
          CONSTRAINT FK_5427AF5359027487 FOREIGN KEY (theme_id) REFERENCES application_request_theme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_application_request_tag
        ADD
          CONSTRAINT FK_6F3FA269B8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_application_request_tag
        ADD
          CONSTRAINT FK_6F3FA2699644FEDA FOREIGN KEY (application_request_tag_id) REFERENCES application_request_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_referent_tag
        ADD
          CONSTRAINT FK_DA291742B8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_referent_tag
        ADD
          CONSTRAINT FK_DA2917429C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          articles
        ADD
          CONSTRAINT FK_BFDD316812469DE2 FOREIGN KEY (category_id) REFERENCES articles_categories (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          articles
        ADD
          CONSTRAINT FK_BFDD3168EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          article_proposal_theme
        ADD
          CONSTRAINT FK_F6B9A2217294869C FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          article_proposal_theme
        ADD
          CONSTRAINT FK_F6B9A221B85948AF FOREIGN KEY (proposal_theme_id) REFERENCES proposals_themes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          assessor_requests
        ADD
          CONSTRAINT FK_26BC800F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id)');
        $this->addSql('ALTER TABLE
          assessor_requests_vote_place_wishes
        ADD
          CONSTRAINT FK_1517FC131BD1903D FOREIGN KEY (assessor_request_id) REFERENCES assessor_requests (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          assessor_requests_vote_place_wishes
        ADD
          CONSTRAINT FK_1517FC13F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          assessor_role_association
        ADD
          CONSTRAINT FK_B93395C2F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id)');
        $this->addSql('ALTER TABLE
          audience
        ADD
          CONSTRAINT FK_FDCD94189F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          audience_zone
        ADD
          CONSTRAINT FK_A719804F848CC616 FOREIGN KEY (audience_id) REFERENCES audience (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          audience_zone
        ADD
          CONSTRAINT FK_A719804F9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          audience_segment
        ADD
          CONSTRAINT FK_C5C2F52FD395B25E FOREIGN KEY (filter_id) REFERENCES adherent_message_filters (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          audience_segment
        ADD
          CONSTRAINT FK_C5C2F52FF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          audience_snapshot
        ADD
          CONSTRAINT FK_BA99FEBB9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          audience_snapshot_zone
        ADD
          CONSTRAINT FK_10882DC0ACA633A8 FOREIGN KEY (audience_snapshot_id) REFERENCES audience_snapshot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          audience_snapshot_zone
        ADD
          CONSTRAINT FK_10882DC09F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          board_member
        ADD
          CONSTRAINT FK_DCFABEDF25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          board_member_roles
        ADD
          CONSTRAINT FK_1DD1E043C7BA2FD5 FOREIGN KEY (board_member_id) REFERENCES board_member (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          board_member_roles
        ADD
          CONSTRAINT FK_1DD1E043D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          saved_board_members
        ADD
          CONSTRAINT FK_32865A32FDCCD727 FOREIGN KEY (board_member_owner_id) REFERENCES board_member (id)');
        $this->addSql('ALTER TABLE
          saved_board_members
        ADD
          CONSTRAINT FK_32865A324821D202 FOREIGN KEY (board_member_saved_id) REFERENCES board_member (id)');
        $this->addSql('ALTER TABLE
          candidate_managed_area
        ADD
          CONSTRAINT FK_C604D2EA9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          cause
        ADD
          CONSTRAINT FK_F0DA7FBFF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          cause
        ADD
          CONSTRAINT FK_F0DA7FBFC2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id)');
        $this->addSql('ALTER TABLE
          cause
        ADD
          CONSTRAINT FK_F0DA7FBF38C2B2DC FOREIGN KEY (second_coalition_id) REFERENCES coalition (id)');
        $this->addSql('ALTER TABLE
          cause_follower
        ADD
          CONSTRAINT FK_6F9A854466E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          cause_follower
        ADD
          CONSTRAINT FK_6F9A85449F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          cause_follower
        ADD
          CONSTRAINT FK_6F9A854425F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          cause_quick_action
        ADD
          CONSTRAINT FK_DC1B329B66E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE certification_request CHANGE ocr_payload ocr_payload JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE
          certification_request
        ADD
          CONSTRAINT FK_6E7481A925F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          certification_request
        ADD
          CONSTRAINT FK_6E7481A92FFD4FD3 FOREIGN KEY (processed_by_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          certification_request
        ADD
          CONSTRAINT FK_6E7481A96EA98020 FOREIGN KEY (found_duplicated_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          chez_vous_cities
        ADD
          CONSTRAINT FK_A42D9BEDAE80F5DF FOREIGN KEY (department_id) REFERENCES chez_vous_departments (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          chez_vous_departments
        ADD
          CONSTRAINT FK_29E7DD5798260155 FOREIGN KEY (region_id) REFERENCES chez_vous_regions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          chez_vous_markers
        ADD
          CONSTRAINT FK_452F890F8BAC62AF FOREIGN KEY (city_id) REFERENCES chez_vous_cities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          chez_vous_measures
        ADD
          CONSTRAINT FK_E6E8973E8BAC62AF FOREIGN KEY (city_id) REFERENCES chez_vous_cities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          chez_vous_measures
        ADD
          CONSTRAINT FK_E6E8973EC54C8C93 FOREIGN KEY (type_id) REFERENCES chez_vous_measure_types (id)');
        $this->addSql('ALTER TABLE
          cities
        ADD
          CONSTRAINT FK_D95DB16BAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE
          clarifications
        ADD
          CONSTRAINT FK_2FAB8972EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          cms_block
        ADD
          CONSTRAINT FK_AD680C0E9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          cms_block
        ADD
          CONSTRAINT FK_AD680C0ECF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          coalition_follower
        ADD
          CONSTRAINT FK_DFF370E2C2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          coalition_follower
        ADD
          CONSTRAINT FK_DFF370E225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_candidacy
        ADD
          CONSTRAINT FK_9A044544E891720 FOREIGN KEY (committee_election_id) REFERENCES committee_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_candidacy
        ADD
          CONSTRAINT FK_9A04454FCC6DA91 FOREIGN KEY (committee_membership_id) REFERENCES committees_memberships (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_candidacy
        ADD
          CONSTRAINT FK_9A04454FC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES committee_candidacies_group (id)');
        $this->addSql('ALTER TABLE
          committee_candidacy_invitation
        ADD
          CONSTRAINT FK_368B016159B22434 FOREIGN KEY (candidacy_id) REFERENCES committee_candidacy (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_candidacy_invitation
        ADD
          CONSTRAINT FK_368B01611FB354CD FOREIGN KEY (membership_id) REFERENCES committees_memberships (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_election
        ADD
          CONSTRAINT FK_2CA406E5ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_election
        ADD
          CONSTRAINT FK_2CA406E5FAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id)');
        $this->addSql('ALTER TABLE
          committee_feed_item
        ADD
          CONSTRAINT FK_4F1CDC80ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE
          committee_feed_item
        ADD
          CONSTRAINT FK_4F1CDC80F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          committee_feed_item
        ADD
          CONSTRAINT FK_4F1CDC8071F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_feed_item_user_documents
        ADD
          CONSTRAINT FK_D269D0AABEF808A3 FOREIGN KEY (committee_feed_item_id) REFERENCES committee_feed_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_feed_item_user_documents
        ADD
          CONSTRAINT FK_D269D0AA6A24B1A2 FOREIGN KEY (user_document_id) REFERENCES user_documents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_merge_histories
        ADD
          CONSTRAINT FK_BB95FBBC3BF0CCB3 FOREIGN KEY (source_committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE
          committee_merge_histories
        ADD
          CONSTRAINT FK_BB95FBBC5C34CBC4 FOREIGN KEY (destination_committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE
          committee_merge_histories
        ADD
          CONSTRAINT FK_BB95FBBC50FA8329 FOREIGN KEY (merged_by_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          committee_merge_histories
        ADD
          CONSTRAINT FK_BB95FBBCA8E1562 FOREIGN KEY (reverted_by_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          committee_merge_histories_merged_memberships
        ADD
          CONSTRAINT FK_CB8E336F9379ED92 FOREIGN KEY (committee_merge_history_id) REFERENCES committee_merge_histories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_merge_histories_merged_memberships
        ADD
          CONSTRAINT FK_CB8E336FFCC6DA91 FOREIGN KEY (committee_membership_id) REFERENCES committees_memberships (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_provisional_supervisor
        ADD
          CONSTRAINT FK_E394C3D425F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          committee_provisional_supervisor
        ADD
          CONSTRAINT FK_E394C3D4ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committees
        ADD
          CONSTRAINT FK_A36198C6B4D2A5D1 FOREIGN KEY (current_designation_id) REFERENCES designation (id)');
        $this->addSql('ALTER TABLE
          committee_referent_tag
        ADD
          CONSTRAINT FK_285EB1C5ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_referent_tag
        ADD
          CONSTRAINT FK_285EB1C59C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_zone
        ADD
          CONSTRAINT FK_37C5F224ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_zone
        ADD
          CONSTRAINT FK_37C5F2249F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committees_membership_histories
        ADD
          CONSTRAINT FK_4BBAE2C7ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE
          committee_membership_history_referent_tag
        ADD
          CONSTRAINT FK_B6A8C718123C64CE FOREIGN KEY (
            committee_membership_history_id
          ) REFERENCES committees_membership_histories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_membership_history_referent_tag
        ADD
          CONSTRAINT FK_B6A8C7189C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committees_memberships
        ADD
          CONSTRAINT FK_E7A6490E25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          committees_memberships
        ADD
          CONSTRAINT FK_E7A6490EED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE consular_district CHANGE points points JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE
          consular_managed_area
        ADD
          CONSTRAINT FK_7937A51292CA96FD FOREIGN KEY (consular_district_id) REFERENCES consular_district (id)');
        $this->addSql('ALTER TABLE
          custom_search_results
        ADD
          CONSTRAINT FK_38973E54EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          department
        ADD
          CONSTRAINT FK_CD1DE18A98260155 FOREIGN KEY (region_id) REFERENCES region (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          deputy_managed_users_message
        ADD
          CONSTRAINT FK_5AC419DDB08FA272 FOREIGN KEY (district_id) REFERENCES districts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          deputy_managed_users_message
        ADD
          CONSTRAINT FK_5AC419DD25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          designation_referent_tag
        ADD
          CONSTRAINT FK_7538F35AFAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          designation_referent_tag
        ADD
          CONSTRAINT FK_7538F35A9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          device_zone
        ADD
          CONSTRAINT FK_29D2153D94A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          device_zone
        ADD
          CONSTRAINT FK_29D2153D9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          districts
        ADD
          CONSTRAINT FK_68E318DC80E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          districts
        ADD
          CONSTRAINT FK_68E318DC9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('ALTER TABLE
          donation_transactions
        ADD
          CONSTRAINT FK_89D6D36B4DC1279C FOREIGN KEY (donation_id) REFERENCES donations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          donations
        ADD
          CONSTRAINT FK_CDE98962831BACAF FOREIGN KEY (donator_id) REFERENCES donators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          donation_donation_tag
        ADD
          CONSTRAINT FK_F2D7087F4DC1279C FOREIGN KEY (donation_id) REFERENCES donations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          donation_donation_tag
        ADD
          CONSTRAINT FK_F2D7087F790547EA FOREIGN KEY (donation_tag_id) REFERENCES donation_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          donator_kinship
        ADD
          CONSTRAINT FK_E542211D831BACAF FOREIGN KEY (donator_id) REFERENCES donators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          donator_kinship
        ADD
          CONSTRAINT FK_E542211D4162C001 FOREIGN KEY (related_id) REFERENCES donators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          donators
        ADD
          CONSTRAINT FK_A902FDD725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          donators
        ADD
          CONSTRAINT FK_A902FDD7DE59CB1A FOREIGN KEY (last_successful_donation_id) REFERENCES donations (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          donators
        ADD
          CONSTRAINT FK_A902FDD7ABF665A8 FOREIGN KEY (reference_donation_id) REFERENCES donations (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          donator_donator_tag
        ADD
          CONSTRAINT FK_6BAEC28C831BACAF FOREIGN KEY (donator_id) REFERENCES donators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          donator_donator_tag
        ADD
          CONSTRAINT FK_6BAEC28C71F026E6 FOREIGN KEY (donator_tag_id) REFERENCES donator_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative
        ADD
          CONSTRAINT FK_BF51F0FD25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition
        ADD
          CONSTRAINT FK_A9C53A24D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition
        ADD
          CONSTRAINT FK_A9C53A24F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_label
        ADD
          CONSTRAINT FK_D8143704D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        ADD
          CONSTRAINT FK_386091469F2C3FAB FOREIGN KEY (zone_id) REFERENCES elected_representative_zone (id)');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        ADD
          CONSTRAINT FK_38609146283AB2A9 FOREIGN KEY (geo_zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        ADD
          CONSTRAINT FK_38609146D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_political_function
        ADD
          CONSTRAINT FK_303BAF41D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_political_function
        ADD
          CONSTRAINT FK_303BAF416C1129CD FOREIGN KEY (mandate_id) REFERENCES elected_representative_mandate (id)');
        $this->addSql('ALTER TABLE
          elected_representative_social_network_link
        ADD
          CONSTRAINT FK_231377B5D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_sponsorship
        ADD
          CONSTRAINT FK_CA6D486D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition_history
        ADD
          CONSTRAINT FK_1ECF7566D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition_history
        ADD
          CONSTRAINT FK_1ECF7566F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition_history
        ADD
          CONSTRAINT FK_1ECF756625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition_history
        ADD
          CONSTRAINT FK_1ECF75664B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          elected_representative_zone
        ADD
          CONSTRAINT FK_C52FC4A712469DE2 FOREIGN KEY (category_id) REFERENCES elected_representative_zone_category (id)');
        $this->addSql('ALTER TABLE
          elected_representative_zone_referent_tag
        ADD
          CONSTRAINT FK_D2B7A8C5BE31A103 FOREIGN KEY (elected_representative_zone_id) REFERENCES elected_representative_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_zone_referent_tag
        ADD
          CONSTRAINT FK_D2B7A8C59C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_zone_parent
        ADD
          CONSTRAINT FK_CECA906FDD62C21B FOREIGN KEY (child_id) REFERENCES elected_representative_zone (id)');
        $this->addSql('ALTER TABLE
          elected_representative_zone_parent
        ADD
          CONSTRAINT FK_CECA906F727ACA70 FOREIGN KEY (parent_id) REFERENCES elected_representative_zone (id)');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D18BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1E449D110 FOREIGN KEY (first_candidate_id) REFERENCES election_city_candidate (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1B29FABBC FOREIGN KEY (headquarters_manager_id) REFERENCES election_city_manager (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1E4A014FA FOREIGN KEY (politic_manager_id) REFERENCES election_city_manager (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1781FEED9 FOREIGN KEY (task_force_manager_id) REFERENCES election_city_manager (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1354DEDE5 FOREIGN KEY (candidate_option_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D15EC54712 FOREIGN KEY (preparation_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1F543170A FOREIGN KEY (third_option_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1EBF42685 FOREIGN KEY (candidate_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1B86B270B FOREIGN KEY (national_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_contact
        ADD
          CONSTRAINT FK_D04AFB68BAC62AF FOREIGN KEY (city_id) REFERENCES election_city_card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_partner
        ADD
          CONSTRAINT FK_704D77988BAC62AF FOREIGN KEY (city_id) REFERENCES election_city_card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_rounds
        ADD
          CONSTRAINT FK_37C02EA0A708DAFF FOREIGN KEY (election_id) REFERENCES elections (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          email_templates
        ADD
          CONSTRAINT FK_6023E2A5F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574A876C4DDA FOREIGN KEY (organizer_id) REFERENCES adherents (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574AED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574AC2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id)');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574A66E2221E FOREIGN KEY (cause_id) REFERENCES cause (id)');
        $this->addSql('ALTER TABLE
          event_referent_tag
        ADD
          CONSTRAINT FK_D3C8F5BE71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          event_referent_tag
        ADD
          CONSTRAINT FK_D3C8F5BE9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          event_zone
        ADD
          CONSTRAINT FK_BF208CAC3B1C4B73 FOREIGN KEY (base_event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          event_zone
        ADD
          CONSTRAINT FK_BF208CAC9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          event_user_documents
        ADD
          CONSTRAINT FK_7D14491F71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          event_user_documents
        ADD
          CONSTRAINT FK_7D14491F6A24B1A2 FOREIGN KEY (user_document_id) REFERENCES user_documents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          events_categories
        ADD
          CONSTRAINT FK_EF0AF3E9A267D842 FOREIGN KEY (event_group_category_id) REFERENCES event_group_category (id)');
        $this->addSql('ALTER TABLE
          events_invitations
        ADD
          CONSTRAINT FK_B94D5AAD71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          events_registrations
        ADD
          CONSTRAINT FK_EEFA30C071F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE failed_login_attempt CHANGE extra extra JSON NOT NULL');
        $this->addSql('ALTER TABLE
          filesystem_file
        ADD
          CONSTRAINT FK_47F0AE28B03A8386 FOREIGN KEY (created_by_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          filesystem_file
        ADD
          CONSTRAINT FK_47F0AE28896DBBDE FOREIGN KEY (updated_by_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          filesystem_file
        ADD
          CONSTRAINT FK_47F0AE28727ACA70 FOREIGN KEY (parent_id) REFERENCES filesystem_file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          filesystem_file_permission
        ADD
          CONSTRAINT FK_BD623E4C93CB796C FOREIGN KEY (file_id) REFERENCES filesystem_file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          formation_axes
        ADD
          CONSTRAINT FK_7E652CB6D96C566B FOREIGN KEY (path_id) REFERENCES formation_paths (id)');
        $this->addSql('ALTER TABLE
          formation_axes
        ADD
          CONSTRAINT FK_7E652CB6EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          formation_files
        ADD
          CONSTRAINT FK_70BEDE2CAFC2B591 FOREIGN KEY (module_id) REFERENCES formation_modules (id)');
        $this->addSql('ALTER TABLE
          formation_modules
        ADD
          CONSTRAINT FK_6B4806AC2E30CD41 FOREIGN KEY (axe_id) REFERENCES formation_axes (id)');
        $this->addSql('ALTER TABLE
          formation_modules
        ADD
          CONSTRAINT FK_6B4806ACEA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          geo_borough
        ADD
          CONSTRAINT FK_144958748BAC62AF FOREIGN KEY (city_id) REFERENCES geo_city (id)');
        $this->addSql('ALTER TABLE
          geo_borough
        ADD
          CONSTRAINT FK_1449587480E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          geo_canton
        ADD
          CONSTRAINT FK_F04FC05FAE80F5DF FOREIGN KEY (department_id) REFERENCES geo_department (id)');
        $this->addSql('ALTER TABLE
          geo_canton
        ADD
          CONSTRAINT FK_F04FC05F80E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          geo_city
        ADD
          CONSTRAINT FK_297C2D34AE80F5DF FOREIGN KEY (department_id) REFERENCES geo_department (id)');
        $this->addSql('ALTER TABLE
          geo_city
        ADD
          CONSTRAINT FK_297C2D346D3B1930 FOREIGN KEY (city_community_id) REFERENCES geo_city_community (id)');
        $this->addSql('ALTER TABLE
          geo_city
        ADD
          CONSTRAINT FK_297C2D349D25CF90 FOREIGN KEY (replacement_id) REFERENCES geo_city (id)');
        $this->addSql('ALTER TABLE
          geo_city
        ADD
          CONSTRAINT FK_297C2D3480E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          geo_city_district
        ADD
          CONSTRAINT FK_5C4191F8BAC62AF FOREIGN KEY (city_id) REFERENCES geo_city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          geo_city_district
        ADD
          CONSTRAINT FK_5C4191FB08FA272 FOREIGN KEY (district_id) REFERENCES geo_district (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          geo_city_canton
        ADD
          CONSTRAINT FK_A4AB64718BAC62AF FOREIGN KEY (city_id) REFERENCES geo_city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          geo_city_canton
        ADD
          CONSTRAINT FK_A4AB64718D070D0B FOREIGN KEY (canton_id) REFERENCES geo_canton (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          geo_city_community
        ADD
          CONSTRAINT FK_E5805E0880E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          geo_city_community_department
        ADD
          CONSTRAINT FK_1E2D6D066D3B1930 FOREIGN KEY (city_community_id) REFERENCES geo_city_community (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          geo_city_community_department
        ADD
          CONSTRAINT FK_1E2D6D06AE80F5DF FOREIGN KEY (department_id) REFERENCES geo_department (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          geo_consular_district
        ADD
          CONSTRAINT FK_BBFC552F72D24D35 FOREIGN KEY (foreign_district_id) REFERENCES geo_foreign_district (id)');
        $this->addSql('ALTER TABLE
          geo_consular_district
        ADD
          CONSTRAINT FK_BBFC552F80E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          geo_country
        ADD
          CONSTRAINT FK_E465446472D24D35 FOREIGN KEY (foreign_district_id) REFERENCES geo_foreign_district (id)');
        $this->addSql('ALTER TABLE
          geo_country
        ADD
          CONSTRAINT FK_E465446480E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          geo_custom_zone
        ADD
          CONSTRAINT FK_ABE4DB5A80E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          geo_department
        ADD
          CONSTRAINT FK_B460660498260155 FOREIGN KEY (region_id) REFERENCES geo_region (id)');
        $this->addSql('ALTER TABLE
          geo_department
        ADD
          CONSTRAINT FK_B460660480E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          geo_district
        ADD
          CONSTRAINT FK_DF782326AE80F5DF FOREIGN KEY (department_id) REFERENCES geo_department (id)');
        $this->addSql('ALTER TABLE
          geo_district
        ADD
          CONSTRAINT FK_DF78232680E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          geo_foreign_district
        ADD
          CONSTRAINT FK_973BE1F198755666 FOREIGN KEY (custom_zone_id) REFERENCES geo_custom_zone (id)');
        $this->addSql('ALTER TABLE
          geo_foreign_district
        ADD
          CONSTRAINT FK_973BE1F180E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          geo_region
        ADD
          CONSTRAINT FK_A4B3C808F92F3E70 FOREIGN KEY (country_id) REFERENCES geo_country (id)');
        $this->addSql('ALTER TABLE
          geo_region
        ADD
          CONSTRAINT FK_A4B3C80880E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          geo_zone
        ADD
          CONSTRAINT FK_A4CCEF0780E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('ALTER TABLE
          geo_zone_parent
        ADD
          CONSTRAINT FK_8E49B9DDD62C21B FOREIGN KEY (child_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          geo_zone_parent
        ADD
          CONSTRAINT FK_8E49B9D727ACA70 FOREIGN KEY (parent_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          home_blocks
        ADD
          CONSTRAINT FK_3EE9FCC5EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          interactive_invitation_has_choices
        ADD
          CONSTRAINT FK_31A811A2A35D7AF0 FOREIGN KEY (invitation_id) REFERENCES interactive_invitations (id)');
        $this->addSql('ALTER TABLE
          interactive_invitation_has_choices
        ADD
          CONSTRAINT FK_31A811A2998666D1 FOREIGN KEY (choice_id) REFERENCES interactive_choices (id)');
        $this->addSql('ALTER TABLE
          jecoute_choice
        ADD
          CONSTRAINT FK_80BD898B1E27F6BF FOREIGN KEY (question_id) REFERENCES jecoute_question (id)');
        $this->addSql('ALTER TABLE
          jecoute_data_answer
        ADD
          CONSTRAINT FK_12FB393EA6DF29BA FOREIGN KEY (survey_question_id) REFERENCES jecoute_survey_question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          jecoute_data_answer
        ADD
          CONSTRAINT FK_12FB393E3C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          jecoute_data_answer_selected_choices
        ADD
          CONSTRAINT FK_10DF117259C0831 FOREIGN KEY (data_answer_id) REFERENCES jecoute_data_answer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          jecoute_data_answer_selected_choices
        ADD
          CONSTRAINT FK_10DF117998666D1 FOREIGN KEY (choice_id) REFERENCES jecoute_choice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          jecoute_data_survey
        ADD
          CONSTRAINT FK_6579E8E7F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_data_survey
        ADD
          CONSTRAINT FK_6579E8E7B3FE509D FOREIGN KEY (survey_id) REFERENCES jecoute_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          jecoute_managed_areas
        ADD
          CONSTRAINT FK_DF8531749F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          jecoute_news
        ADD
          CONSTRAINT FK_34362099F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_news
        ADD
          CONSTRAINT FK_3436209B03A8386 FOREIGN KEY (created_by_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_news
        ADD
          CONSTRAINT FK_3436209F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_region
        ADD
          CONSTRAINT FK_4E74226F9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          jecoute_region
        ADD
          CONSTRAINT FK_4E74226FF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_region
        ADD
          CONSTRAINT FK_4E74226F4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_riposte
        ADD
          CONSTRAINT FK_17E1064BB03A8386 FOREIGN KEY (created_by_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_riposte
        ADD
          CONSTRAINT FK_17E1064BF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          jecoute_suggested_question
        ADD
          CONSTRAINT FK_8280E9DABF396750 FOREIGN KEY (id) REFERENCES jecoute_question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          jecoute_survey
        ADD
          CONSTRAINT FK_EC4948E5F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_survey
        ADD
          CONSTRAINT FK_EC4948E59F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          jecoute_survey
        ADD
          CONSTRAINT FK_EC4948E54B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_survey_question
        ADD
          CONSTRAINT FK_A2FBFA81B3FE509D FOREIGN KEY (survey_id) REFERENCES jecoute_survey (id)');
        $this->addSql('ALTER TABLE
          jecoute_survey_question
        ADD
          CONSTRAINT FK_A2FBFA811E27F6BF FOREIGN KEY (question_id) REFERENCES jecoute_question (id)');
        $this->addSql('ALTER TABLE
          jemarche_data_survey
        ADD
          CONSTRAINT FK_8DF5D81894A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jemarche_data_survey
        ADD
          CONSTRAINT FK_8DF5D8183C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          legislative_candidates
        ADD
          CONSTRAINT FK_AE55AF9B23F5C396 FOREIGN KEY (district_zone_id) REFERENCES legislative_district_zones (id)');
        $this->addSql('ALTER TABLE
          legislative_candidates
        ADD
          CONSTRAINT FK_AE55AF9BEA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          list_total_result
        ADD
          CONSTRAINT FK_A19B071E3DAE168B FOREIGN KEY (list_id) REFERENCES vote_result_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          list_total_result
        ADD
          CONSTRAINT FK_A19B071E45EB7186 FOREIGN KEY (vote_result_id) REFERENCES vote_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          lre_area
        ADD
          CONSTRAINT FK_8D3B8F189C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('ALTER TABLE
          mailchimp_campaign
        ADD
          CONSTRAINT FK_CFABD3094BD2A4C0 FOREIGN KEY (report_id) REFERENCES mailchimp_campaign_report (id)');
        $this->addSql('ALTER TABLE
          mailchimp_campaign
        ADD
          CONSTRAINT FK_CFABD309537A1329 FOREIGN KEY (message_id) REFERENCES adherent_messages (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          mailchimp_campaign_mailchimp_segment
        ADD
          CONSTRAINT FK_901CE107828112CC FOREIGN KEY (mailchimp_campaign_id) REFERENCES mailchimp_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          mailchimp_campaign_mailchimp_segment
        ADD
          CONSTRAINT FK_901CE107D21E482E FOREIGN KEY (mailchimp_segment_id) REFERENCES mailchimp_segment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ministry_list_total_result
        ADD
          CONSTRAINT FK_99D1332580711B75 FOREIGN KEY (ministry_vote_result_id) REFERENCES ministry_vote_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ministry_vote_result
        ADD
          CONSTRAINT FK_B9F11DAEFCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ministry_vote_result
        ADD
          CONSTRAINT FK_B9F11DAE8BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE
          ministry_vote_result
        ADD
          CONSTRAINT FK_B9F11DAEB03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          ministry_vote_result
        ADD
          CONSTRAINT FK_B9F11DAE896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          mooc
        ADD
          CONSTRAINT FK_9D5D3B55684DD106 FOREIGN KEY (article_image_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE
          mooc
        ADD
          CONSTRAINT FK_9D5D3B5543C8160D FOREIGN KEY (list_image_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE
          mooc_chapter
        ADD
          CONSTRAINT FK_A3EDA0D1255EEB87 FOREIGN KEY (mooc_id) REFERENCES mooc (id)');
        $this->addSql('ALTER TABLE
          mooc_elements
        ADD
          CONSTRAINT FK_691284C5579F4768 FOREIGN KEY (chapter_id) REFERENCES mooc_chapter (id)');
        $this->addSql('ALTER TABLE
          mooc_elements
        ADD
          CONSTRAINT FK_691284C53DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE
          mooc_element_attachment_link
        ADD
          CONSTRAINT FK_324635C7B1828C9D FOREIGN KEY (base_mooc_element_id) REFERENCES mooc_elements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          mooc_element_attachment_link
        ADD
          CONSTRAINT FK_324635C7653157F7 FOREIGN KEY (attachment_link_id) REFERENCES mooc_attachment_link (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          mooc_element_attachment_file
        ADD
          CONSTRAINT FK_88759A26B1828C9D FOREIGN KEY (base_mooc_element_id) REFERENCES mooc_elements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          mooc_element_attachment_file
        ADD
          CONSTRAINT FK_88759A265B5E2CEA FOREIGN KEY (attachment_file_id) REFERENCES mooc_attachment_file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          municipal_manager_role_association_cities
        ADD
          CONSTRAINT FK_A713D9C2D96891C FOREIGN KEY (
            municipal_manager_role_association_id
          ) REFERENCES municipal_manager_role_association (id)');
        $this->addSql('ALTER TABLE
          municipal_manager_role_association_cities
        ADD
          CONSTRAINT FK_A713D9C28BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE
          municipal_manager_supervisor_role
        ADD
          CONSTRAINT FK_F304FF35E47E35 FOREIGN KEY (referent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          my_team_delegated_access
        ADD
          CONSTRAINT FK_421C13B98825BEFA FOREIGN KEY (delegator_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          my_team_delegated_access
        ADD
          CONSTRAINT FK_421C13B9B7E7AE18 FOREIGN KEY (delegated_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          my_team_delegate_access_committee
        ADD
          CONSTRAINT FK_C52A163FFD98FA7A FOREIGN KEY (delegated_access_id) REFERENCES my_team_delegated_access (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          my_team_delegate_access_committee
        ADD
          CONSTRAINT FK_C52A163FED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          national_council_candidacy
        ADD
          CONSTRAINT FK_31A7A205A708DAFF FOREIGN KEY (election_id) REFERENCES national_council_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          national_council_candidacy
        ADD
          CONSTRAINT FK_31A7A205FC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES national_council_candidacies_group (id)');
        $this->addSql('ALTER TABLE
          national_council_candidacy
        ADD
          CONSTRAINT FK_31A7A20525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          national_council_election
        ADD
          CONSTRAINT FK_F3809347FAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id)');
        $this->addSql('ALTER TABLE oauth_access_tokens CHANGE scopes scopes JSON NOT NULL');
        $this->addSql('ALTER TABLE
          oauth_access_tokens
        ADD
          CONSTRAINT FK_CA42527C19EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)');
        $this->addSql('ALTER TABLE
          oauth_access_tokens
        ADD
          CONSTRAINT FK_CA42527CA76ED395 FOREIGN KEY (user_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          oauth_access_tokens
        ADD
          CONSTRAINT FK_CA42527C94A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_auth_codes CHANGE scopes scopes JSON NOT NULL');
        $this->addSql('ALTER TABLE
          oauth_auth_codes
        ADD
          CONSTRAINT FK_BB493F8319EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)');
        $this->addSql('ALTER TABLE
          oauth_auth_codes
        ADD
          CONSTRAINT FK_BB493F83A76ED395 FOREIGN KEY (user_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          oauth_auth_codes
        ADD
          CONSTRAINT FK_BB493F8394A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_clients CHANGE redirect_uris redirect_uris JSON NOT NULL');
        $this->addSql('ALTER TABLE
          oauth_refresh_tokens
        ADD
          CONSTRAINT FK_5AB6872CCB2688 FOREIGN KEY (access_token_id) REFERENCES oauth_access_tokens (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          order_articles
        ADD
          CONSTRAINT FK_5E25D3D9EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          order_section_order_article
        ADD
          CONSTRAINT FK_A956D4E4C14E7BC9 FOREIGN KEY (order_article_id) REFERENCES order_articles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          order_section_order_article
        ADD
          CONSTRAINT FK_A956D4E46BF91E2F FOREIGN KEY (order_section_id) REFERENCES order_sections (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          organizational_chart_item
        ADD
          CONSTRAINT FK_29C1CBACA977936C FOREIGN KEY (tree_root) REFERENCES organizational_chart_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          organizational_chart_item
        ADD
          CONSTRAINT FK_29C1CBAC727ACA70 FOREIGN KEY (parent_id) REFERENCES organizational_chart_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pages
        ADD
          CONSTRAINT FK_2074E5755B42DC0F FOREIGN KEY (header_media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          pages
        ADD
          CONSTRAINT FK_2074E575EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          pap_building
        ADD
          CONSTRAINT FK_112ABBE1F5B7AF75 FOREIGN KEY (address_id) REFERENCES pap_address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_building
        ADD
          CONSTRAINT FK_112ABBE148ED5CAD FOREIGN KEY (current_campaign_id) REFERENCES pap_campaign (id)');
        $this->addSql('ALTER TABLE
          pap_building_block
        ADD
          CONSTRAINT FK_61470C814D2A7E12 FOREIGN KEY (building_id) REFERENCES pap_building (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_building_block
        ADD
          CONSTRAINT FK_61470C8185C9D733 FOREIGN KEY (created_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_building_block
        ADD
          CONSTRAINT FK_61470C81DF6CFDC9 FOREIGN KEY (updated_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_building_block_statistics
        ADD
          CONSTRAINT FK_8B79BF6032618357 FOREIGN KEY (building_block_id) REFERENCES pap_building_block (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_building_block_statistics
        ADD
          CONSTRAINT FK_8B79BF60F639F774 FOREIGN KEY (campaign_id) REFERENCES pap_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_building_statistics
        ADD
          CONSTRAINT FK_B6FB4E7B4D2A7E12 FOREIGN KEY (building_id) REFERENCES pap_building (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_building_statistics
        ADD
          CONSTRAINT FK_B6FB4E7BF639F774 FOREIGN KEY (campaign_id) REFERENCES pap_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_building_statistics
        ADD
          CONSTRAINT FK_B6FB4E7BDCDF6621 FOREIGN KEY (last_passage_done_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          CONSTRAINT FK_EF50C8E8B3FE509D FOREIGN KEY (survey_id) REFERENCES jecoute_survey (id)');
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          CONSTRAINT FK_EF50C8E84B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F7CC0DE6E1 FOREIGN KEY (questioner_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F7F639F774 FOREIGN KEY (campaign_id) REFERENCES pap_campaign (id)');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F74D2A7E12 FOREIGN KEY (building_id) REFERENCES pap_building (id)');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F73C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_floor
        ADD
          CONSTRAINT FK_633C3C6432618357 FOREIGN KEY (building_block_id) REFERENCES pap_building_block (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_floor
        ADD
          CONSTRAINT FK_633C3C6485C9D733 FOREIGN KEY (created_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_floor
        ADD
          CONSTRAINT FK_633C3C64DF6CFDC9 FOREIGN KEY (updated_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_floor_statistics
        ADD
          CONSTRAINT FK_853B68C8854679E2 FOREIGN KEY (floor_id) REFERENCES pap_floor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_floor_statistics
        ADD
          CONSTRAINT FK_853B68C8F639F774 FOREIGN KEY (campaign_id) REFERENCES pap_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_voter
        ADD
          CONSTRAINT FK_FBF5A013F5B7AF75 FOREIGN KEY (address_id) REFERENCES pap_address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          CONSTRAINT FK_C3882BA4296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          CONSTRAINT FK_C3882BA4848CC616 FOREIGN KEY (audience_id) REFERENCES audience_snapshot (id)');
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          CONSTRAINT FK_C3882BA4B3FE509D FOREIGN KEY (survey_id) REFERENCES jecoute_survey (id)');
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          CONSTRAINT FK_C3882BA49DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          CONSTRAINT FK_C3882BA4CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          CONSTRAINT FK_C3882BA485C9D733 FOREIGN KEY (created_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          CONSTRAINT FK_C3882BA4DF6CFDC9 FOREIGN KEY (updated_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC191198A5626C52 FOREIGN KEY (caller_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC19119825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC191198F639F774 FOREIGN KEY (campaign_id) REFERENCES phoning_campaign (id)');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC1911983C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          political_committee
        ADD
          CONSTRAINT FK_39FAEE95AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          political_committee_feed_item
        ADD
          CONSTRAINT FK_54369E83C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          political_committee_feed_item
        ADD
          CONSTRAINT FK_54369E83F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          political_committee_membership
        ADD
          CONSTRAINT FK_FD85437B25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          political_committee_membership
        ADD
          CONSTRAINT FK_FD85437BC7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          political_committee_quality
        ADD
          CONSTRAINT FK_243D6D3A78632915 FOREIGN KEY (
            political_committee_membership_id
          ) REFERENCES political_committee_membership (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          poll
        ADD
          CONSTRAINT FK_84BCFA45F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          poll
        ADD
          CONSTRAINT FK_84BCFA459F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          poll
        ADD
          CONSTRAINT FK_84BCFA454B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          poll_choice
        ADD
          CONSTRAINT FK_2DAE19C93C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          poll_vote
        ADD
          CONSTRAINT FK_ED568EBE998666D1 FOREIGN KEY (choice_id) REFERENCES poll_choice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          poll_vote
        ADD
          CONSTRAINT FK_ED568EBE25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          poll_vote
        ADD
          CONSTRAINT FK_ED568EBE94A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_proxies_to_election_rounds
        ADD
          CONSTRAINT FK_D075F5A9E15E419B FOREIGN KEY (procuration_proxy_id) REFERENCES procuration_proxies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_proxies_to_election_rounds
        ADD
          CONSTRAINT FK_D075F5A9FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_requests
        ADD
          CONSTRAINT FK_9769FD842F1B6663 FOREIGN KEY (found_proxy_id) REFERENCES procuration_proxies (id)');
        $this->addSql('ALTER TABLE
          procuration_requests
        ADD
          CONSTRAINT FK_9769FD84888FDEEE FOREIGN KEY (
            procuration_request_found_by_id
          ) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_requests_to_election_rounds
        ADD
          CONSTRAINT FK_A47BBD53128D9C53 FOREIGN KEY (procuration_request_id) REFERENCES procuration_requests (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_requests_to_election_rounds
        ADD
          CONSTRAINT FK_A47BBD53FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          programmatic_foundation_measure
        ADD
          CONSTRAINT FK_213A5F1EF0ED738A FOREIGN KEY (sub_approach_id) REFERENCES programmatic_foundation_sub_approach (id)');
        $this->addSql('ALTER TABLE
          programmatic_foundation_measure_tag
        ADD
          CONSTRAINT FK_F004297F5DA37D00 FOREIGN KEY (measure_id) REFERENCES programmatic_foundation_measure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          programmatic_foundation_measure_tag
        ADD
          CONSTRAINT FK_F004297FBAD26311 FOREIGN KEY (tag_id) REFERENCES programmatic_foundation_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          programmatic_foundation_project
        ADD
          CONSTRAINT FK_8E8E96D55DA37D00 FOREIGN KEY (measure_id) REFERENCES programmatic_foundation_measure (id)');
        $this->addSql('ALTER TABLE
          programmatic_foundation_project_tag
        ADD
          CONSTRAINT FK_9F63872166D1F9C FOREIGN KEY (project_id) REFERENCES programmatic_foundation_project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          programmatic_foundation_project_tag
        ADD
          CONSTRAINT FK_9F63872BAD26311 FOREIGN KEY (tag_id) REFERENCES programmatic_foundation_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          programmatic_foundation_sub_approach
        ADD
          CONSTRAINT FK_735C1D0115140614 FOREIGN KEY (approach_id) REFERENCES programmatic_foundation_approach (id)');
        $this->addSql('ALTER TABLE
          projection_managed_users_zone
        ADD
          CONSTRAINT FK_E4D4ADCDC679DD78 FOREIGN KEY (managed_user_id) REFERENCES projection_managed_users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          projection_managed_users_zone
        ADD
          CONSTRAINT FK_E4D4ADCD9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          proposals
        ADD
          CONSTRAINT FK_A5BA3A8FEA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          proposal_proposal_theme
        ADD
          CONSTRAINT FK_6B80CE41F4792058 FOREIGN KEY (proposal_id) REFERENCES proposals (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          proposal_proposal_theme
        ADD
          CONSTRAINT FK_6B80CE41B85948AF FOREIGN KEY (proposal_theme_id) REFERENCES proposals_themes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          push_token
        ADD
          CONSTRAINT FK_51BC138125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          push_token
        ADD
          CONSTRAINT FK_51BC138194A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          qr_code
        ADD
          CONSTRAINT FK_7D8B1FB5B03A8386 FOREIGN KEY (created_by_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          referent
        ADD
          CONSTRAINT FK_FE9AAC6CEA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          referent_areas
        ADD
          CONSTRAINT FK_75CEBC6C35E47E35 FOREIGN KEY (referent_id) REFERENCES referent (id)');
        $this->addSql('ALTER TABLE
          referent_areas
        ADD
          CONSTRAINT FK_75CEBC6CBD0F409C FOREIGN KEY (area_id) REFERENCES referent_area (id)');
        $this->addSql('ALTER TABLE
          referent_managed_areas_tags
        ADD
          CONSTRAINT FK_8BE84DD56B99CC25 FOREIGN KEY (referent_managed_area_id) REFERENCES referent_managed_areas (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_managed_areas_tags
        ADD
          CONSTRAINT FK_8BE84DD59C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('ALTER TABLE
          referent_managed_users_message
        ADD
          CONSTRAINT FK_1E41AC6125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_person_link
        ADD
          CONSTRAINT FK_BC75A60A810B5A42 FOREIGN KEY (
            person_organizational_chart_item_id
          ) REFERENCES organizational_chart_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_person_link
        ADD
          CONSTRAINT FK_BC75A60A35E47E35 FOREIGN KEY (referent_id) REFERENCES referent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_person_link
        ADD
          CONSTRAINT FK_BC75A60A25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          referent_person_link_committee
        ADD
          CONSTRAINT FK_1C97B2A5B3E4DE86 FOREIGN KEY (referent_person_link_id) REFERENCES referent_person_link (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_person_link_committee
        ADD
          CONSTRAINT FK_1C97B2A5ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_space_access_information
        ADD
          CONSTRAINT FK_CD8FDF4825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_tags
        ADD
          CONSTRAINT FK_135D29D99F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          referent_team_member
        ADD
          CONSTRAINT FK_6C006717597D3FE FOREIGN KEY (member_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_team_member
        ADD
          CONSTRAINT FK_6C0067135E47E35 FOREIGN KEY (referent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_team_member_committee
        ADD
          CONSTRAINT FK_EC89860BFE4CA267 FOREIGN KEY (referent_team_member_id) REFERENCES referent_team_member (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_team_member_committee
        ADD
          CONSTRAINT FK_EC89860BED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          reports
        ADD
          CONSTRAINT FK_F11FA745F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          reports
        ADD
          CONSTRAINT FK_F11FA745ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE
          reports
        ADD
          CONSTRAINT FK_F11FA74583B12DAC FOREIGN KEY (community_event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE
          republican_silence_referent_tag
        ADD
          CONSTRAINT FK_543DED2612359909 FOREIGN KEY (republican_silence_id) REFERENCES republican_silence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          republican_silence_referent_tag
        ADD
          CONSTRAINT FK_543DED269C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          senator_area
        ADD
          CONSTRAINT FK_D229BBF7AEC89CE1 FOREIGN KEY (department_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('ALTER TABLE
          senatorial_candidate_areas_tags
        ADD
          CONSTRAINT FK_F83208FAA7BF84E8 FOREIGN KEY (senatorial_candidate_area_id) REFERENCES senatorial_candidate_areas (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          senatorial_candidate_areas_tags
        ADD
          CONSTRAINT FK_F83208FA9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('ALTER TABLE
          sms_campaign
        ADD
          CONSTRAINT FK_79E333DC848CC616 FOREIGN KEY (audience_id) REFERENCES audience_snapshot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          sms_campaign
        ADD
          CONSTRAINT FK_79E333DC4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          social_shares
        ADD
          CONSTRAINT FK_8E1413A085040FAD FOREIGN KEY (social_share_category_id) REFERENCES social_share_categories (id)');
        $this->addSql('ALTER TABLE
          social_shares
        ADD
          CONSTRAINT FK_8E1413A0EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          team
        ADD
          CONSTRAINT FK_C4E0A61F9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          team
        ADD
          CONSTRAINT FK_C4E0A61FCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          team
        ADD
          CONSTRAINT FK_C4E0A61F85C9D733 FOREIGN KEY (created_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          team
        ADD
          CONSTRAINT FK_C4E0A61FDF6CFDC9 FOREIGN KEY (updated_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          team_member
        ADD
          CONSTRAINT FK_6FFBDA1296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          team_member
        ADD
          CONSTRAINT FK_6FFBDA125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          team_member_history
        ADD
          CONSTRAINT FK_1F330628296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          team_member_history
        ADD
          CONSTRAINT FK_1F33062825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          team_member_history
        ADD
          CONSTRAINT FK_1F3306284B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          team_member_history
        ADD
          CONSTRAINT FK_1F33062846E746A6 FOREIGN KEY (team_manager_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          territorial_council
        ADD
          CONSTRAINT FK_B6DCA2A5B4D2A5D1 FOREIGN KEY (current_designation_id) REFERENCES designation (id)');
        $this->addSql('ALTER TABLE
          territorial_council_referent_tag
        ADD
          CONSTRAINT FK_78DBEB90AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_referent_tag
        ADD
          CONSTRAINT FK_78DBEB909C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_zone
        ADD
          CONSTRAINT FK_9467B41EAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_zone
        ADD
          CONSTRAINT FK_9467B41E9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy
        ADD
          CONSTRAINT FK_39885B6A708DAFF FOREIGN KEY (election_id) REFERENCES territorial_council_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy
        ADD
          CONSTRAINT FK_39885B61FB354CD FOREIGN KEY (membership_id) REFERENCES territorial_council_membership (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy
        ADD
          CONSTRAINT FK_39885B6FC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES territorial_council_candidacies_group (id)');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy_invitation
        ADD
          CONSTRAINT FK_DA86009A59B22434 FOREIGN KEY (candidacy_id) REFERENCES territorial_council_candidacy (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy_invitation
        ADD
          CONSTRAINT FK_DA86009A1FB354CD FOREIGN KEY (membership_id) REFERENCES territorial_council_membership (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_convocation
        ADD
          CONSTRAINT FK_A9919BF0AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id)');
        $this->addSql('ALTER TABLE
          territorial_council_convocation
        ADD
          CONSTRAINT FK_A9919BF0C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id)');
        $this->addSql('ALTER TABLE
          territorial_council_convocation
        ADD
          CONSTRAINT FK_A9919BF0B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          territorial_council_election
        ADD
          CONSTRAINT FK_14CBC36BAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id)');
        $this->addSql('ALTER TABLE
          territorial_council_election
        ADD
          CONSTRAINT FK_14CBC36B8649F5F1 FOREIGN KEY (election_poll_id) REFERENCES territorial_council_election_poll (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_election
        ADD
          CONSTRAINT FK_14CBC36BFAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id)');
        $this->addSql('ALTER TABLE
          territorial_council_election_poll_choice
        ADD
          CONSTRAINT FK_63EBCF6B8649F5F1 FOREIGN KEY (election_poll_id) REFERENCES territorial_council_election_poll (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_election_poll_vote
        ADD
          CONSTRAINT FK_BCDA0C15998666D1 FOREIGN KEY (choice_id) REFERENCES territorial_council_election_poll_choice (id)');
        $this->addSql('ALTER TABLE
          territorial_council_election_poll_vote
        ADD
          CONSTRAINT FK_BCDA0C151FB354CD FOREIGN KEY (membership_id) REFERENCES territorial_council_membership (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_feed_item
        ADD
          CONSTRAINT FK_45241D62AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_feed_item
        ADD
          CONSTRAINT FK_45241D62F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          territorial_council_membership
        ADD
          CONSTRAINT FK_2A99831625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_membership
        ADD
          CONSTRAINT FK_2A998316AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_membership_log
        ADD
          CONSTRAINT FK_2F6D242025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_official_report
        ADD
          CONSTRAINT FK_8D80D385C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_official_report
        ADD
          CONSTRAINT FK_8D80D385F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          territorial_council_official_report
        ADD
          CONSTRAINT FK_8D80D385B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          territorial_council_official_report
        ADD
          CONSTRAINT FK_8D80D385896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          territorial_council_official_report_document
        ADD
          CONSTRAINT FK_78C1161DB03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          territorial_council_official_report_document
        ADD
          CONSTRAINT FK_78C1161D4BD2A4C0 FOREIGN KEY (report_id) REFERENCES territorial_council_official_report (id)');
        $this->addSql('ALTER TABLE
          territorial_council_quality
        ADD
          CONSTRAINT FK_C018E022E797FAB0 FOREIGN KEY (
            territorial_council_membership_id
          ) REFERENCES territorial_council_membership (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          thematic_community_membership
        ADD
          CONSTRAINT FK_22B6AC05FDA7B0BF FOREIGN KEY (community_id) REFERENCES thematic_community (id)');
        $this->addSql('ALTER TABLE
          thematic_community_membership
        ADD
          CONSTRAINT FK_22B6AC0525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          thematic_community_membership
        ADD
          CONSTRAINT FK_22B6AC05E7A1254A FOREIGN KEY (contact_id) REFERENCES thematic_community_contact (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          thematic_community_membership_user_list_definition
        ADD
          CONSTRAINT FK_58815EB9403AE2A5 FOREIGN KEY (
            thematic_community_membership_id
          ) REFERENCES thematic_community_membership (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          thematic_community_membership_user_list_definition
        ADD
          CONSTRAINT FK_58815EB9F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          timeline_manifesto_translations
        ADD
          CONSTRAINT FK_F7BD6C172C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_manifestos (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          timeline_manifestos
        ADD
          CONSTRAINT FK_C6ED4403EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          timeline_measure_translations
        ADD
          CONSTRAINT FK_5C9EB6072C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_measures (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          timeline_measures
        ADD
          CONSTRAINT FK_BA475ED737E924 FOREIGN KEY (manifesto_id) REFERENCES timeline_manifestos (id)');
        $this->addSql('ALTER TABLE
          timeline_measures_profiles
        ADD
          CONSTRAINT FK_B83D81AE5DA37D00 FOREIGN KEY (measure_id) REFERENCES timeline_measures (id)');
        $this->addSql('ALTER TABLE
          timeline_measures_profiles
        ADD
          CONSTRAINT FK_B83D81AECCFA12B8 FOREIGN KEY (profile_id) REFERENCES timeline_profiles (id)');
        $this->addSql('ALTER TABLE
          timeline_themes_measures
        ADD
          CONSTRAINT FK_EB8A7B0C5DA37D00 FOREIGN KEY (measure_id) REFERENCES timeline_measures (id)');
        $this->addSql('ALTER TABLE
          timeline_themes_measures
        ADD
          CONSTRAINT FK_EB8A7B0C59027487 FOREIGN KEY (theme_id) REFERENCES timeline_themes (id)');
        $this->addSql('ALTER TABLE
          timeline_profile_translations
        ADD
          CONSTRAINT FK_41B3A6DA2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_profiles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          timeline_theme_translations
        ADD
          CONSTRAINT FK_F81F72932C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_themes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          timeline_themes
        ADD
          CONSTRAINT FK_8ADDB8F6EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE
          ton_macron_friend_invitation_has_choices
        ADD
          CONSTRAINT FK_BB3BCAEEA35D7AF0 FOREIGN KEY (invitation_id) REFERENCES ton_macron_friend_invitations (id)');
        $this->addSql('ALTER TABLE
          ton_macron_friend_invitation_has_choices
        ADD
          CONSTRAINT FK_BB3BCAEE998666D1 FOREIGN KEY (choice_id) REFERENCES ton_macron_choices (id)');
        $this->addSql('ALTER TABLE
          unregistrations
        ADD
          CONSTRAINT FK_F9E4AA0C5B30B80B FOREIGN KEY (excluded_by_id) REFERENCES administrators (id)');
        $this->addSql('ALTER TABLE
          unregistration_referent_tag
        ADD
          CONSTRAINT FK_59B7AC414D824CA FOREIGN KEY (unregistration_id) REFERENCES unregistrations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          unregistration_referent_tag
        ADD
          CONSTRAINT FK_59B7AC49C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_authorizations CHANGE scopes scopes JSON NOT NULL');
        $this->addSql('ALTER TABLE
          user_authorizations
        ADD
          CONSTRAINT FK_40448230A76ED395 FOREIGN KEY (user_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          user_authorizations
        ADD
          CONSTRAINT FK_4044823019EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE
          vote_result
        ADD
          CONSTRAINT FK_1F8DB349FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          vote_result
        ADD
          CONSTRAINT FK_1F8DB349B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          vote_result
        ADD
          CONSTRAINT FK_1F8DB349896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          vote_result
        ADD
          CONSTRAINT FK_1F8DB3498BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          vote_result
        ADD
          CONSTRAINT FK_1F8DB349F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          vote_result_list
        ADD
          CONSTRAINT FK_677ED502DB567AF4 FOREIGN KEY (list_collection_id) REFERENCES vote_result_list_collection (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          vote_result_list_collection
        ADD
          CONSTRAINT FK_9C1DD9638BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE
          vote_result_list_collection
        ADD
          CONSTRAINT FK_9C1DD963FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id)');
        $this->addSql('ALTER TABLE
          voting_platform_candidate
        ADD
          CONSTRAINT FK_3F426D6D5F0A9B94 FOREIGN KEY (candidate_group_id) REFERENCES voting_platform_candidate_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_candidate
        ADD
          CONSTRAINT FK_3F426D6D25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          voting_platform_candidate_group
        ADD
          CONSTRAINT FK_2C1A353AC1E98F21 FOREIGN KEY (election_pool_id) REFERENCES voting_platform_election_pool (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_candidate_group_result
        CHANGE
          total_mentions total_mentions JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE
          voting_platform_candidate_group_result
        ADD
          CONSTRAINT FK_7249D5375F0A9B94 FOREIGN KEY (candidate_group_id) REFERENCES voting_platform_candidate_group (id)');
        $this->addSql('ALTER TABLE
          voting_platform_candidate_group_result
        ADD
          CONSTRAINT FK_7249D537B5BA5CC5 FOREIGN KEY (election_pool_result_id) REFERENCES voting_platform_election_pool_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_election
        ADD
          CONSTRAINT FK_4E144C94FAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id)');
        $this->addSql('ALTER TABLE
          voting_platform_election_entity
        ADD
          CONSTRAINT FK_7AAD259FED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          voting_platform_election_entity
        ADD
          CONSTRAINT FK_7AAD259FAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          voting_platform_election_entity
        ADD
          CONSTRAINT FK_7AAD259FA708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_election_pool
        ADD
          CONSTRAINT FK_7225D6EFA708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_election_pool_result
        ADD
          CONSTRAINT FK_13C1C73FC1E98F21 FOREIGN KEY (election_pool_id) REFERENCES voting_platform_election_pool (id)');
        $this->addSql('ALTER TABLE
          voting_platform_election_pool_result
        ADD
          CONSTRAINT FK_13C1C73F8FFC0F0B FOREIGN KEY (election_round_result_id) REFERENCES voting_platform_election_round_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_election_result
        ADD
          CONSTRAINT FK_67EFA0E4A708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_election_round
        ADD
          CONSTRAINT FK_F15D87B7A708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_election_round_election_pool
        ADD
          CONSTRAINT FK_E6665F19FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES voting_platform_election_round (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_election_round_election_pool
        ADD
          CONSTRAINT FK_E6665F19C1E98F21 FOREIGN KEY (election_pool_id) REFERENCES voting_platform_election_pool (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_election_round_result
        ADD
          CONSTRAINT FK_F2670966FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES voting_platform_election_round (id)');
        $this->addSql('ALTER TABLE
          voting_platform_election_round_result
        ADD
          CONSTRAINT FK_F267096619FCFB29 FOREIGN KEY (election_result_id) REFERENCES voting_platform_election_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_vote
        ADD
          CONSTRAINT FK_DCBB2B7BEBB4B8AD FOREIGN KEY (voter_id) REFERENCES voting_platform_voter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_vote
        ADD
          CONSTRAINT FK_DCBB2B7BFCBF5E32 FOREIGN KEY (election_round_id) REFERENCES voting_platform_election_round (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_vote_choice
        ADD
          CONSTRAINT FK_B009F31145EB7186 FOREIGN KEY (vote_result_id) REFERENCES voting_platform_vote_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_vote_choice
        ADD
          CONSTRAINT FK_B009F3115F0A9B94 FOREIGN KEY (candidate_group_id) REFERENCES voting_platform_candidate_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_vote_choice
        ADD
          CONSTRAINT FK_B009F311C1E98F21 FOREIGN KEY (election_pool_id) REFERENCES voting_platform_election_pool (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_vote_result
        ADD
          CONSTRAINT FK_62C86890FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES voting_platform_election_round (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_voter
        ADD
          CONSTRAINT FK_AB02EC0225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          voting_platform_voters_list
        ADD
          CONSTRAINT FK_3C73500DA708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_voters_list_voter
        ADD
          CONSTRAINT FK_7CC26956FB0C8C84 FOREIGN KEY (voters_list_id) REFERENCES voting_platform_voters_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          voting_platform_voters_list_voter
        ADD
          CONSTRAINT FK_7CC26956EBB4B8AD FOREIGN KEY (voter_id) REFERENCES voting_platform_voter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE web_hooks CHANGE callbacks callbacks JSON NOT NULL');
        $this->addSql('ALTER TABLE
          web_hooks
        ADD
          CONSTRAINT FK_CDB836AD19EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_adherent_tag DROP FOREIGN KEY FK_DD297F8225F06C53');
        $this->addSql('ALTER TABLE adherent_adherent_tag DROP FOREIGN KEY FK_DD297F82AED03543');
        $this->addSql('ALTER TABLE adherent_certification_histories DROP FOREIGN KEY FK_732EE81A25F06C53');
        $this->addSql('ALTER TABLE adherent_certification_histories DROP FOREIGN KEY FK_732EE81A4B09E92C');
        $this->addSql('ALTER TABLE adherent_charter DROP FOREIGN KEY FK_D6F94F2B25F06C53');
        $this->addSql('ALTER TABLE adherent_commitment DROP FOREIGN KEY FK_D239EF6F25F06C53');
        $this->addSql('ALTER TABLE adherent_email_subscribe_token DROP FOREIGN KEY FK_376DBA0F675F31B');
        $this->addSql('ALTER TABLE adherent_email_subscribe_token DROP FOREIGN KEY FK_376DBA09DF5350C');
        $this->addSql('ALTER TABLE adherent_email_subscribe_token DROP FOREIGN KEY FK_376DBA0CF1918FF');
        $this->addSql('ALTER TABLE adherent_email_subscription_histories DROP FOREIGN KEY FK_51AD8354B6596C08');
        $this->addSql('ALTER TABLE
          adherent_email_subscription_history_referent_tag
        DROP
          FOREIGN KEY FK_6FFBE6E88FCB8132');
        $this->addSql('ALTER TABLE
          adherent_email_subscription_history_referent_tag
        DROP
          FOREIGN KEY FK_6FFBE6E89C262DB3');
        $this->addSql('ALTER TABLE adherent_instance_quality DROP FOREIGN KEY FK_D63B17FA25F06C53');
        $this->addSql('ALTER TABLE adherent_instance_quality DROP FOREIGN KEY FK_D63B17FAA623BBD7');
        $this->addSql('ALTER TABLE adherent_instance_quality DROP FOREIGN KEY FK_D63B17FA9F2C3FAB');
        $this->addSql('ALTER TABLE adherent_instance_quality DROP FOREIGN KEY FK_D63B17FAAAA61A99');
        $this->addSql('ALTER TABLE adherent_mandate DROP FOREIGN KEY FK_9C0C3D6025F06C53');
        $this->addSql('ALTER TABLE adherent_mandate DROP FOREIGN KEY FK_9C0C3D60ED1A100B');
        $this->addSql('ALTER TABLE adherent_mandate DROP FOREIGN KEY FK_9C0C3D60AAA61A99');
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F9466E2221E');
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94FAF04979');
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94DB296AAD');
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F949F2C3FAB');
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94ED1A100B');
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F949C262DB3');
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94AAA61A99');
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94C7A72');
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94F74563E3');
        $this->addSql('ALTER TABLE adherent_messages DROP FOREIGN KEY FK_D187C183F675F31B');
        $this->addSql('ALTER TABLE adherent_messages DROP FOREIGN KEY FK_D187C183D395B25E');
        $this->addSql('ALTER TABLE adherent_referent_tag DROP FOREIGN KEY FK_79E8AFFD25F06C53');
        $this->addSql('ALTER TABLE adherent_referent_tag DROP FOREIGN KEY FK_79E8AFFD9C262DB3');
        $this->addSql('ALTER TABLE adherent_segment DROP FOREIGN KEY FK_9DF0C7EBF675F31B');
        $this->addSql('ALTER TABLE adherent_subscription_type DROP FOREIGN KEY FK_F93DC28A25F06C53');
        $this->addSql('ALTER TABLE adherent_subscription_type DROP FOREIGN KEY FK_F93DC28AB6596C08');
        $this->addSql('ALTER TABLE adherent_thematic_community DROP FOREIGN KEY FK_DAB0B4EC25F06C53');
        $this->addSql('ALTER TABLE adherent_thematic_community DROP FOREIGN KEY FK_DAB0B4EC1BE5825E');
        $this->addSql('ALTER TABLE adherent_zone DROP FOREIGN KEY FK_1C14D08525F06C53');
        $this->addSql('ALTER TABLE adherent_zone DROP FOREIGN KEY FK_1C14D0859F2C3FAB');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA39BF75CAD');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3DC184E71');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA31A912B27');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA339054338');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3E1B55931');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3E4A5D7A5');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA379DE69AA');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA39801977F');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA38828ED30');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA394E3BB99');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3A132C3C5');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3EA9FDD75');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3CC72679B');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3FCCAF6D5');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA379645AD5');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA37657F304');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA393494FA8');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3122E5FF4');
        $this->addSql('ALTER TABLE administrator_export_history DROP FOREIGN KEY FK_10499F014B09E92C');
        $this->addSql('ALTER TABLE application_request_running_mate DROP FOREIGN KEY FK_D1D6095625F06C53');
        $this->addSql('ALTER TABLE application_request_volunteer DROP FOREIGN KEY FK_1139657025F06C53');
        $this->addSql('ALTER TABLE article_proposal_theme DROP FOREIGN KEY FK_F6B9A2217294869C');
        $this->addSql('ALTER TABLE article_proposal_theme DROP FOREIGN KEY FK_F6B9A221B85948AF');
        $this->addSql('ALTER TABLE articles DROP FOREIGN KEY FK_BFDD316812469DE2');
        $this->addSql('ALTER TABLE articles DROP FOREIGN KEY FK_BFDD3168EA9FDD75');
        $this->addSql('ALTER TABLE assessor_requests DROP FOREIGN KEY FK_26BC800F3F90B30');
        $this->addSql('ALTER TABLE assessor_requests_vote_place_wishes DROP FOREIGN KEY FK_1517FC131BD1903D');
        $this->addSql('ALTER TABLE assessor_requests_vote_place_wishes DROP FOREIGN KEY FK_1517FC13F3F90B30');
        $this->addSql('ALTER TABLE assessor_role_association DROP FOREIGN KEY FK_B93395C2F3F90B30');
        $this->addSql('ALTER TABLE audience DROP FOREIGN KEY FK_FDCD94189F2C3FAB');
        $this->addSql('ALTER TABLE audience_segment DROP FOREIGN KEY FK_C5C2F52FD395B25E');
        $this->addSql('ALTER TABLE audience_segment DROP FOREIGN KEY FK_C5C2F52FF675F31B');
        $this->addSql('ALTER TABLE audience_snapshot DROP FOREIGN KEY FK_BA99FEBB9F2C3FAB');
        $this->addSql('ALTER TABLE audience_snapshot_zone DROP FOREIGN KEY FK_10882DC0ACA633A8');
        $this->addSql('ALTER TABLE audience_snapshot_zone DROP FOREIGN KEY FK_10882DC09F2C3FAB');
        $this->addSql('ALTER TABLE audience_zone DROP FOREIGN KEY FK_A719804F848CC616');
        $this->addSql('ALTER TABLE audience_zone DROP FOREIGN KEY FK_A719804F9F2C3FAB');
        $this->addSql('ALTER TABLE board_member DROP FOREIGN KEY FK_DCFABEDF25F06C53');
        $this->addSql('ALTER TABLE board_member_roles DROP FOREIGN KEY FK_1DD1E043C7BA2FD5');
        $this->addSql('ALTER TABLE board_member_roles DROP FOREIGN KEY FK_1DD1E043D60322AC');
        $this->addSql('ALTER TABLE candidate_managed_area DROP FOREIGN KEY FK_C604D2EA9F2C3FAB');
        $this->addSql('ALTER TABLE cause DROP FOREIGN KEY FK_F0DA7FBFF675F31B');
        $this->addSql('ALTER TABLE cause DROP FOREIGN KEY FK_F0DA7FBFC2A46A23');
        $this->addSql('ALTER TABLE cause DROP FOREIGN KEY FK_F0DA7FBF38C2B2DC');
        $this->addSql('ALTER TABLE cause_follower DROP FOREIGN KEY FK_6F9A854466E2221E');
        $this->addSql('ALTER TABLE cause_follower DROP FOREIGN KEY FK_6F9A85449F2C3FAB');
        $this->addSql('ALTER TABLE cause_follower DROP FOREIGN KEY FK_6F9A854425F06C53');
        $this->addSql('ALTER TABLE cause_quick_action DROP FOREIGN KEY FK_DC1B329B66E2221E');
        $this->addSql('ALTER TABLE certification_request DROP FOREIGN KEY FK_6E7481A925F06C53');
        $this->addSql('ALTER TABLE certification_request DROP FOREIGN KEY FK_6E7481A92FFD4FD3');
        $this->addSql('ALTER TABLE certification_request DROP FOREIGN KEY FK_6E7481A96EA98020');
        $this->addSql('ALTER TABLE
          certification_request
        CHANGE
          ocr_payload ocr_payload LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE chez_vous_cities DROP FOREIGN KEY FK_A42D9BEDAE80F5DF');
        $this->addSql('ALTER TABLE chez_vous_departments DROP FOREIGN KEY FK_29E7DD5798260155');
        $this->addSql('ALTER TABLE chez_vous_markers DROP FOREIGN KEY FK_452F890F8BAC62AF');
        $this->addSql('ALTER TABLE chez_vous_measures DROP FOREIGN KEY FK_E6E8973E8BAC62AF');
        $this->addSql('ALTER TABLE chez_vous_measures DROP FOREIGN KEY FK_E6E8973EC54C8C93');
        $this->addSql('ALTER TABLE cities DROP FOREIGN KEY FK_D95DB16BAE80F5DF');
        $this->addSql('ALTER TABLE clarifications DROP FOREIGN KEY FK_2FAB8972EA9FDD75');
        $this->addSql('ALTER TABLE cms_block DROP FOREIGN KEY FK_AD680C0E9DF5350C');
        $this->addSql('ALTER TABLE cms_block DROP FOREIGN KEY FK_AD680C0ECF1918FF');
        $this->addSql('ALTER TABLE coalition_follower DROP FOREIGN KEY FK_DFF370E2C2A46A23');
        $this->addSql('ALTER TABLE coalition_follower DROP FOREIGN KEY FK_DFF370E225F06C53');
        $this->addSql('ALTER TABLE committee_candidacy DROP FOREIGN KEY FK_9A044544E891720');
        $this->addSql('ALTER TABLE committee_candidacy DROP FOREIGN KEY FK_9A04454FCC6DA91');
        $this->addSql('ALTER TABLE committee_candidacy DROP FOREIGN KEY FK_9A04454FC1537C1');
        $this->addSql('ALTER TABLE committee_candidacy_invitation DROP FOREIGN KEY FK_368B016159B22434');
        $this->addSql('ALTER TABLE committee_candidacy_invitation DROP FOREIGN KEY FK_368B01611FB354CD');
        $this->addSql('ALTER TABLE committee_election DROP FOREIGN KEY FK_2CA406E5ED1A100B');
        $this->addSql('ALTER TABLE committee_election DROP FOREIGN KEY FK_2CA406E5FAC7D83F');
        $this->addSql('ALTER TABLE committee_feed_item DROP FOREIGN KEY FK_4F1CDC80ED1A100B');
        $this->addSql('ALTER TABLE committee_feed_item DROP FOREIGN KEY FK_4F1CDC80F675F31B');
        $this->addSql('ALTER TABLE committee_feed_item DROP FOREIGN KEY FK_4F1CDC8071F7E88B');
        $this->addSql('ALTER TABLE committee_feed_item_user_documents DROP FOREIGN KEY FK_D269D0AABEF808A3');
        $this->addSql('ALTER TABLE committee_feed_item_user_documents DROP FOREIGN KEY FK_D269D0AA6A24B1A2');
        $this->addSql('ALTER TABLE committee_membership_history_referent_tag DROP FOREIGN KEY FK_B6A8C718123C64CE');
        $this->addSql('ALTER TABLE committee_membership_history_referent_tag DROP FOREIGN KEY FK_B6A8C7189C262DB3');
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBC3BF0CCB3');
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBC5C34CBC4');
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBC50FA8329');
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBCA8E1562');
        $this->addSql('ALTER TABLE committee_merge_histories_merged_memberships DROP FOREIGN KEY FK_CB8E336F9379ED92');
        $this->addSql('ALTER TABLE committee_merge_histories_merged_memberships DROP FOREIGN KEY FK_CB8E336FFCC6DA91');
        $this->addSql('ALTER TABLE committee_provisional_supervisor DROP FOREIGN KEY FK_E394C3D425F06C53');
        $this->addSql('ALTER TABLE committee_provisional_supervisor DROP FOREIGN KEY FK_E394C3D4ED1A100B');
        $this->addSql('ALTER TABLE committee_referent_tag DROP FOREIGN KEY FK_285EB1C5ED1A100B');
        $this->addSql('ALTER TABLE committee_referent_tag DROP FOREIGN KEY FK_285EB1C59C262DB3');
        $this->addSql('ALTER TABLE committee_zone DROP FOREIGN KEY FK_37C5F224ED1A100B');
        $this->addSql('ALTER TABLE committee_zone DROP FOREIGN KEY FK_37C5F2249F2C3FAB');
        $this->addSql('ALTER TABLE committees DROP FOREIGN KEY FK_A36198C6B4D2A5D1');
        $this->addSql('ALTER TABLE committees_membership_histories DROP FOREIGN KEY FK_4BBAE2C7ED1A100B');
        $this->addSql('ALTER TABLE committees_memberships DROP FOREIGN KEY FK_E7A6490E25F06C53');
        $this->addSql('ALTER TABLE committees_memberships DROP FOREIGN KEY FK_E7A6490EED1A100B');
        $this->addSql('ALTER TABLE
          consular_district
        CHANGE
          points points LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE consular_managed_area DROP FOREIGN KEY FK_7937A51292CA96FD');
        $this->addSql('ALTER TABLE custom_search_results DROP FOREIGN KEY FK_38973E54EA9FDD75');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A98260155');
        $this->addSql('ALTER TABLE deputy_managed_users_message DROP FOREIGN KEY FK_5AC419DDB08FA272');
        $this->addSql('ALTER TABLE deputy_managed_users_message DROP FOREIGN KEY FK_5AC419DD25F06C53');
        $this->addSql('ALTER TABLE designation_referent_tag DROP FOREIGN KEY FK_7538F35AFAC7D83F');
        $this->addSql('ALTER TABLE designation_referent_tag DROP FOREIGN KEY FK_7538F35A9C262DB3');
        $this->addSql('ALTER TABLE device_zone DROP FOREIGN KEY FK_29D2153D94A4C7D4');
        $this->addSql('ALTER TABLE device_zone DROP FOREIGN KEY FK_29D2153D9F2C3FAB');
        $this->addSql('ALTER TABLE districts DROP FOREIGN KEY FK_68E318DC80E32C3E');
        $this->addSql('ALTER TABLE districts DROP FOREIGN KEY FK_68E318DC9C262DB3');
        $this->addSql('ALTER TABLE donation_donation_tag DROP FOREIGN KEY FK_F2D7087F4DC1279C');
        $this->addSql('ALTER TABLE donation_donation_tag DROP FOREIGN KEY FK_F2D7087F790547EA');
        $this->addSql('ALTER TABLE donation_transactions DROP FOREIGN KEY FK_89D6D36B4DC1279C');
        $this->addSql('ALTER TABLE donations DROP FOREIGN KEY FK_CDE98962831BACAF');
        $this->addSql('ALTER TABLE donator_donator_tag DROP FOREIGN KEY FK_6BAEC28C831BACAF');
        $this->addSql('ALTER TABLE donator_donator_tag DROP FOREIGN KEY FK_6BAEC28C71F026E6');
        $this->addSql('ALTER TABLE donator_kinship DROP FOREIGN KEY FK_E542211D831BACAF');
        $this->addSql('ALTER TABLE donator_kinship DROP FOREIGN KEY FK_E542211D4162C001');
        $this->addSql('ALTER TABLE donators DROP FOREIGN KEY FK_A902FDD725F06C53');
        $this->addSql('ALTER TABLE donators DROP FOREIGN KEY FK_A902FDD7DE59CB1A');
        $this->addSql('ALTER TABLE donators DROP FOREIGN KEY FK_A902FDD7ABF665A8');
        $this->addSql('ALTER TABLE elected_representative DROP FOREIGN KEY FK_BF51F0FD25F06C53');
        $this->addSql('ALTER TABLE elected_representative_label DROP FOREIGN KEY FK_D8143704D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_mandate DROP FOREIGN KEY FK_386091469F2C3FAB');
        $this->addSql('ALTER TABLE elected_representative_mandate DROP FOREIGN KEY FK_38609146283AB2A9');
        $this->addSql('ALTER TABLE elected_representative_mandate DROP FOREIGN KEY FK_38609146D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_political_function DROP FOREIGN KEY FK_303BAF41D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_political_function DROP FOREIGN KEY FK_303BAF416C1129CD');
        $this->addSql('ALTER TABLE elected_representative_social_network_link DROP FOREIGN KEY FK_231377B5D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_sponsorship DROP FOREIGN KEY FK_CA6D486D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_user_list_definition DROP FOREIGN KEY FK_A9C53A24D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_user_list_definition DROP FOREIGN KEY FK_A9C53A24F74563E3');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition_history
        DROP
          FOREIGN KEY FK_1ECF7566D38DA5D3');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition_history
        DROP
          FOREIGN KEY FK_1ECF7566F74563E3');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition_history
        DROP
          FOREIGN KEY FK_1ECF756625F06C53');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition_history
        DROP
          FOREIGN KEY FK_1ECF75664B09E92C');
        $this->addSql('ALTER TABLE elected_representative_zone DROP FOREIGN KEY FK_C52FC4A712469DE2');
        $this->addSql('ALTER TABLE elected_representative_zone_parent DROP FOREIGN KEY FK_CECA906FDD62C21B');
        $this->addSql('ALTER TABLE elected_representative_zone_parent DROP FOREIGN KEY FK_CECA906F727ACA70');
        $this->addSql('ALTER TABLE elected_representative_zone_referent_tag DROP FOREIGN KEY FK_D2B7A8C5BE31A103');
        $this->addSql('ALTER TABLE elected_representative_zone_referent_tag DROP FOREIGN KEY FK_D2B7A8C59C262DB3');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D18BAC62AF');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1E449D110');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1B29FABBC');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1E4A014FA');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1781FEED9');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1354DEDE5');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D15EC54712');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1F543170A');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1EBF42685');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1B86B270B');
        $this->addSql('ALTER TABLE election_city_contact DROP FOREIGN KEY FK_D04AFB68BAC62AF');
        $this->addSql('ALTER TABLE election_city_partner DROP FOREIGN KEY FK_704D77988BAC62AF');
        $this->addSql('ALTER TABLE election_rounds DROP FOREIGN KEY FK_37C02EA0A708DAFF');
        $this->addSql('ALTER TABLE email_templates DROP FOREIGN KEY FK_6023E2A5F675F31B');
        $this->addSql('ALTER TABLE event_referent_tag DROP FOREIGN KEY FK_D3C8F5BE71F7E88B');
        $this->addSql('ALTER TABLE event_referent_tag DROP FOREIGN KEY FK_D3C8F5BE9C262DB3');
        $this->addSql('ALTER TABLE event_user_documents DROP FOREIGN KEY FK_7D14491F71F7E88B');
        $this->addSql('ALTER TABLE event_user_documents DROP FOREIGN KEY FK_7D14491F6A24B1A2');
        $this->addSql('ALTER TABLE event_zone DROP FOREIGN KEY FK_BF208CAC3B1C4B73');
        $this->addSql('ALTER TABLE event_zone DROP FOREIGN KEY FK_BF208CAC9F2C3FAB');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A876C4DDA');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AED1A100B');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AC2A46A23');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A66E2221E');
        $this->addSql('ALTER TABLE events_categories DROP FOREIGN KEY FK_EF0AF3E9A267D842');
        $this->addSql('ALTER TABLE events_invitations DROP FOREIGN KEY FK_B94D5AAD71F7E88B');
        $this->addSql('ALTER TABLE events_registrations DROP FOREIGN KEY FK_EEFA30C071F7E88B');
        $this->addSql('ALTER TABLE
          failed_login_attempt
        CHANGE
          extra extra LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE filesystem_file DROP FOREIGN KEY FK_47F0AE28B03A8386');
        $this->addSql('ALTER TABLE filesystem_file DROP FOREIGN KEY FK_47F0AE28896DBBDE');
        $this->addSql('ALTER TABLE filesystem_file DROP FOREIGN KEY FK_47F0AE28727ACA70');
        $this->addSql('ALTER TABLE filesystem_file_permission DROP FOREIGN KEY FK_BD623E4C93CB796C');
        $this->addSql('ALTER TABLE formation_axes DROP FOREIGN KEY FK_7E652CB6D96C566B');
        $this->addSql('ALTER TABLE formation_axes DROP FOREIGN KEY FK_7E652CB6EA9FDD75');
        $this->addSql('ALTER TABLE formation_files DROP FOREIGN KEY FK_70BEDE2CAFC2B591');
        $this->addSql('ALTER TABLE formation_modules DROP FOREIGN KEY FK_6B4806AC2E30CD41');
        $this->addSql('ALTER TABLE formation_modules DROP FOREIGN KEY FK_6B4806ACEA9FDD75');
        $this->addSql('ALTER TABLE geo_borough DROP FOREIGN KEY FK_144958748BAC62AF');
        $this->addSql('ALTER TABLE geo_borough DROP FOREIGN KEY FK_1449587480E32C3E');
        $this->addSql('ALTER TABLE geo_canton DROP FOREIGN KEY FK_F04FC05FAE80F5DF');
        $this->addSql('ALTER TABLE geo_canton DROP FOREIGN KEY FK_F04FC05F80E32C3E');
        $this->addSql('ALTER TABLE geo_city DROP FOREIGN KEY FK_297C2D34AE80F5DF');
        $this->addSql('ALTER TABLE geo_city DROP FOREIGN KEY FK_297C2D346D3B1930');
        $this->addSql('ALTER TABLE geo_city DROP FOREIGN KEY FK_297C2D349D25CF90');
        $this->addSql('ALTER TABLE geo_city DROP FOREIGN KEY FK_297C2D3480E32C3E');
        $this->addSql('ALTER TABLE geo_city_canton DROP FOREIGN KEY FK_A4AB64718BAC62AF');
        $this->addSql('ALTER TABLE geo_city_canton DROP FOREIGN KEY FK_A4AB64718D070D0B');
        $this->addSql('ALTER TABLE geo_city_community DROP FOREIGN KEY FK_E5805E0880E32C3E');
        $this->addSql('ALTER TABLE geo_city_community_department DROP FOREIGN KEY FK_1E2D6D066D3B1930');
        $this->addSql('ALTER TABLE geo_city_community_department DROP FOREIGN KEY FK_1E2D6D06AE80F5DF');
        $this->addSql('ALTER TABLE geo_city_district DROP FOREIGN KEY FK_5C4191F8BAC62AF');
        $this->addSql('ALTER TABLE geo_city_district DROP FOREIGN KEY FK_5C4191FB08FA272');
        $this->addSql('ALTER TABLE geo_consular_district DROP FOREIGN KEY FK_BBFC552F72D24D35');
        $this->addSql('ALTER TABLE geo_consular_district DROP FOREIGN KEY FK_BBFC552F80E32C3E');
        $this->addSql('ALTER TABLE geo_country DROP FOREIGN KEY FK_E465446472D24D35');
        $this->addSql('ALTER TABLE geo_country DROP FOREIGN KEY FK_E465446480E32C3E');
        $this->addSql('ALTER TABLE geo_custom_zone DROP FOREIGN KEY FK_ABE4DB5A80E32C3E');
        $this->addSql('ALTER TABLE geo_department DROP FOREIGN KEY FK_B460660498260155');
        $this->addSql('ALTER TABLE geo_department DROP FOREIGN KEY FK_B460660480E32C3E');
        $this->addSql('ALTER TABLE geo_district DROP FOREIGN KEY FK_DF782326AE80F5DF');
        $this->addSql('ALTER TABLE geo_district DROP FOREIGN KEY FK_DF78232680E32C3E');
        $this->addSql('ALTER TABLE geo_foreign_district DROP FOREIGN KEY FK_973BE1F198755666');
        $this->addSql('ALTER TABLE geo_foreign_district DROP FOREIGN KEY FK_973BE1F180E32C3E');
        $this->addSql('ALTER TABLE geo_region DROP FOREIGN KEY FK_A4B3C808F92F3E70');
        $this->addSql('ALTER TABLE geo_region DROP FOREIGN KEY FK_A4B3C80880E32C3E');
        $this->addSql('ALTER TABLE geo_zone DROP FOREIGN KEY FK_A4CCEF0780E32C3E');
        $this->addSql('ALTER TABLE geo_zone_parent DROP FOREIGN KEY FK_8E49B9DDD62C21B');
        $this->addSql('ALTER TABLE geo_zone_parent DROP FOREIGN KEY FK_8E49B9D727ACA70');
        $this->addSql('ALTER TABLE home_blocks DROP FOREIGN KEY FK_3EE9FCC5EA9FDD75');
        $this->addSql('ALTER TABLE interactive_invitation_has_choices DROP FOREIGN KEY FK_31A811A2A35D7AF0');
        $this->addSql('ALTER TABLE interactive_invitation_has_choices DROP FOREIGN KEY FK_31A811A2998666D1');
        $this->addSql('ALTER TABLE jecoute_choice DROP FOREIGN KEY FK_80BD898B1E27F6BF');
        $this->addSql('ALTER TABLE jecoute_data_answer DROP FOREIGN KEY FK_12FB393EA6DF29BA');
        $this->addSql('ALTER TABLE jecoute_data_answer DROP FOREIGN KEY FK_12FB393E3C5110AB');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices DROP FOREIGN KEY FK_10DF117259C0831');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices DROP FOREIGN KEY FK_10DF117998666D1');
        $this->addSql('ALTER TABLE jecoute_data_survey DROP FOREIGN KEY FK_6579E8E7F675F31B');
        $this->addSql('ALTER TABLE jecoute_data_survey DROP FOREIGN KEY FK_6579E8E7B3FE509D');
        $this->addSql('ALTER TABLE jecoute_managed_areas DROP FOREIGN KEY FK_DF8531749F2C3FAB');
        $this->addSql('ALTER TABLE jecoute_news DROP FOREIGN KEY FK_34362099F2C3FAB');
        $this->addSql('ALTER TABLE jecoute_news DROP FOREIGN KEY FK_3436209B03A8386');
        $this->addSql('ALTER TABLE jecoute_news DROP FOREIGN KEY FK_3436209F675F31B');
        $this->addSql('ALTER TABLE jecoute_region DROP FOREIGN KEY FK_4E74226F9F2C3FAB');
        $this->addSql('ALTER TABLE jecoute_region DROP FOREIGN KEY FK_4E74226FF675F31B');
        $this->addSql('ALTER TABLE jecoute_region DROP FOREIGN KEY FK_4E74226F4B09E92C');
        $this->addSql('ALTER TABLE jecoute_riposte DROP FOREIGN KEY FK_17E1064BB03A8386');
        $this->addSql('ALTER TABLE jecoute_riposte DROP FOREIGN KEY FK_17E1064BF675F31B');
        $this->addSql('ALTER TABLE jecoute_suggested_question DROP FOREIGN KEY FK_8280E9DABF396750');
        $this->addSql('ALTER TABLE jecoute_survey DROP FOREIGN KEY FK_EC4948E5F675F31B');
        $this->addSql('ALTER TABLE jecoute_survey DROP FOREIGN KEY FK_EC4948E59F2C3FAB');
        $this->addSql('ALTER TABLE jecoute_survey DROP FOREIGN KEY FK_EC4948E54B09E92C');
        $this->addSql('ALTER TABLE jecoute_survey_question DROP FOREIGN KEY FK_A2FBFA81B3FE509D');
        $this->addSql('ALTER TABLE jecoute_survey_question DROP FOREIGN KEY FK_A2FBFA811E27F6BF');
        $this->addSql('ALTER TABLE jemarche_data_survey DROP FOREIGN KEY FK_8DF5D81894A4C7D4');
        $this->addSql('ALTER TABLE jemarche_data_survey DROP FOREIGN KEY FK_8DF5D8183C5110AB');
        $this->addSql('ALTER TABLE legislative_candidates DROP FOREIGN KEY FK_AE55AF9B23F5C396');
        $this->addSql('ALTER TABLE legislative_candidates DROP FOREIGN KEY FK_AE55AF9BEA9FDD75');
        $this->addSql('ALTER TABLE list_total_result DROP FOREIGN KEY FK_A19B071E3DAE168B');
        $this->addSql('ALTER TABLE list_total_result DROP FOREIGN KEY FK_A19B071E45EB7186');
        $this->addSql('ALTER TABLE lre_area DROP FOREIGN KEY FK_8D3B8F189C262DB3');
        $this->addSql('ALTER TABLE mailchimp_campaign DROP FOREIGN KEY FK_CFABD3094BD2A4C0');
        $this->addSql('ALTER TABLE mailchimp_campaign DROP FOREIGN KEY FK_CFABD309537A1329');
        $this->addSql('ALTER TABLE mailchimp_campaign_mailchimp_segment DROP FOREIGN KEY FK_901CE107828112CC');
        $this->addSql('ALTER TABLE mailchimp_campaign_mailchimp_segment DROP FOREIGN KEY FK_901CE107D21E482E');
        $this->addSql('ALTER TABLE ministry_list_total_result DROP FOREIGN KEY FK_99D1332580711B75');
        $this->addSql('ALTER TABLE ministry_vote_result DROP FOREIGN KEY FK_B9F11DAEFCBF5E32');
        $this->addSql('ALTER TABLE ministry_vote_result DROP FOREIGN KEY FK_B9F11DAE8BAC62AF');
        $this->addSql('ALTER TABLE ministry_vote_result DROP FOREIGN KEY FK_B9F11DAEB03A8386');
        $this->addSql('ALTER TABLE ministry_vote_result DROP FOREIGN KEY FK_B9F11DAE896DBBDE');
        $this->addSql('ALTER TABLE mooc DROP FOREIGN KEY FK_9D5D3B55684DD106');
        $this->addSql('ALTER TABLE mooc DROP FOREIGN KEY FK_9D5D3B5543C8160D');
        $this->addSql('ALTER TABLE mooc_chapter DROP FOREIGN KEY FK_A3EDA0D1255EEB87');
        $this->addSql('ALTER TABLE mooc_element_attachment_file DROP FOREIGN KEY FK_88759A26B1828C9D');
        $this->addSql('ALTER TABLE mooc_element_attachment_file DROP FOREIGN KEY FK_88759A265B5E2CEA');
        $this->addSql('ALTER TABLE mooc_element_attachment_link DROP FOREIGN KEY FK_324635C7B1828C9D');
        $this->addSql('ALTER TABLE mooc_element_attachment_link DROP FOREIGN KEY FK_324635C7653157F7');
        $this->addSql('ALTER TABLE mooc_elements DROP FOREIGN KEY FK_691284C5579F4768');
        $this->addSql('ALTER TABLE mooc_elements DROP FOREIGN KEY FK_691284C53DA5256D');
        $this->addSql('ALTER TABLE municipal_manager_role_association_cities DROP FOREIGN KEY FK_A713D9C2D96891C');
        $this->addSql('ALTER TABLE municipal_manager_role_association_cities DROP FOREIGN KEY FK_A713D9C28BAC62AF');
        $this->addSql('ALTER TABLE municipal_manager_supervisor_role DROP FOREIGN KEY FK_F304FF35E47E35');
        $this->addSql('ALTER TABLE my_team_delegate_access_committee DROP FOREIGN KEY FK_C52A163FFD98FA7A');
        $this->addSql('ALTER TABLE my_team_delegate_access_committee DROP FOREIGN KEY FK_C52A163FED1A100B');
        $this->addSql('ALTER TABLE my_team_delegated_access DROP FOREIGN KEY FK_421C13B98825BEFA');
        $this->addSql('ALTER TABLE my_team_delegated_access DROP FOREIGN KEY FK_421C13B9B7E7AE18');
        $this->addSql('ALTER TABLE national_council_candidacy DROP FOREIGN KEY FK_31A7A205A708DAFF');
        $this->addSql('ALTER TABLE national_council_candidacy DROP FOREIGN KEY FK_31A7A205FC1537C1');
        $this->addSql('ALTER TABLE national_council_candidacy DROP FOREIGN KEY FK_31A7A20525F06C53');
        $this->addSql('ALTER TABLE national_council_election DROP FOREIGN KEY FK_F3809347FAC7D83F');
        $this->addSql('ALTER TABLE oauth_access_tokens DROP FOREIGN KEY FK_CA42527C19EB6921');
        $this->addSql('ALTER TABLE oauth_access_tokens DROP FOREIGN KEY FK_CA42527CA76ED395');
        $this->addSql('ALTER TABLE oauth_access_tokens DROP FOREIGN KEY FK_CA42527C94A4C7D4');
        $this->addSql('ALTER TABLE
          oauth_access_tokens
        CHANGE
          scopes scopes LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE oauth_auth_codes DROP FOREIGN KEY FK_BB493F8319EB6921');
        $this->addSql('ALTER TABLE oauth_auth_codes DROP FOREIGN KEY FK_BB493F83A76ED395');
        $this->addSql('ALTER TABLE oauth_auth_codes DROP FOREIGN KEY FK_BB493F8394A4C7D4');
        $this->addSql('ALTER TABLE
          oauth_auth_codes
        CHANGE
          scopes scopes LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE
          oauth_clients
        CHANGE
          redirect_uris redirect_uris LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE oauth_refresh_tokens DROP FOREIGN KEY FK_5AB6872CCB2688');
        $this->addSql('ALTER TABLE order_articles DROP FOREIGN KEY FK_5E25D3D9EA9FDD75');
        $this->addSql('ALTER TABLE order_section_order_article DROP FOREIGN KEY FK_A956D4E4C14E7BC9');
        $this->addSql('ALTER TABLE order_section_order_article DROP FOREIGN KEY FK_A956D4E46BF91E2F');
        $this->addSql('ALTER TABLE organizational_chart_item DROP FOREIGN KEY FK_29C1CBACA977936C');
        $this->addSql('ALTER TABLE organizational_chart_item DROP FOREIGN KEY FK_29C1CBAC727ACA70');
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E5755B42DC0F');
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E575EA9FDD75');
        $this->addSql('ALTER TABLE pap_building DROP FOREIGN KEY FK_112ABBE1F5B7AF75');
        $this->addSql('ALTER TABLE pap_building DROP FOREIGN KEY FK_112ABBE148ED5CAD');
        $this->addSql('ALTER TABLE pap_building_block DROP FOREIGN KEY FK_61470C814D2A7E12');
        $this->addSql('ALTER TABLE pap_building_block DROP FOREIGN KEY FK_61470C8185C9D733');
        $this->addSql('ALTER TABLE pap_building_block DROP FOREIGN KEY FK_61470C81DF6CFDC9');
        $this->addSql('ALTER TABLE pap_building_block_statistics DROP FOREIGN KEY FK_8B79BF6032618357');
        $this->addSql('ALTER TABLE pap_building_block_statistics DROP FOREIGN KEY FK_8B79BF60F639F774');
        $this->addSql('ALTER TABLE pap_building_statistics DROP FOREIGN KEY FK_B6FB4E7B4D2A7E12');
        $this->addSql('ALTER TABLE pap_building_statistics DROP FOREIGN KEY FK_B6FB4E7BF639F774');
        $this->addSql('ALTER TABLE pap_building_statistics DROP FOREIGN KEY FK_B6FB4E7BDCDF6621');
        $this->addSql('ALTER TABLE pap_campaign DROP FOREIGN KEY FK_EF50C8E8B3FE509D');
        $this->addSql('ALTER TABLE pap_campaign DROP FOREIGN KEY FK_EF50C8E84B09E92C');
        $this->addSql('ALTER TABLE pap_campaign_history DROP FOREIGN KEY FK_5A3F26F7CC0DE6E1');
        $this->addSql('ALTER TABLE pap_campaign_history DROP FOREIGN KEY FK_5A3F26F725F06C53');
        $this->addSql('ALTER TABLE pap_campaign_history DROP FOREIGN KEY FK_5A3F26F7F639F774');
        $this->addSql('ALTER TABLE pap_campaign_history DROP FOREIGN KEY FK_5A3F26F74D2A7E12');
        $this->addSql('ALTER TABLE pap_campaign_history DROP FOREIGN KEY FK_5A3F26F73C5110AB');
        $this->addSql('ALTER TABLE pap_floor DROP FOREIGN KEY FK_633C3C6432618357');
        $this->addSql('ALTER TABLE pap_floor DROP FOREIGN KEY FK_633C3C6485C9D733');
        $this->addSql('ALTER TABLE pap_floor DROP FOREIGN KEY FK_633C3C64DF6CFDC9');
        $this->addSql('ALTER TABLE pap_floor_statistics DROP FOREIGN KEY FK_853B68C8854679E2');
        $this->addSql('ALTER TABLE pap_floor_statistics DROP FOREIGN KEY FK_853B68C8F639F774');
        $this->addSql('ALTER TABLE pap_voter DROP FOREIGN KEY FK_FBF5A013F5B7AF75');
        $this->addSql('ALTER TABLE phoning_campaign DROP FOREIGN KEY FK_C3882BA4296CD8AE');
        $this->addSql('ALTER TABLE phoning_campaign DROP FOREIGN KEY FK_C3882BA4848CC616');
        $this->addSql('ALTER TABLE phoning_campaign DROP FOREIGN KEY FK_C3882BA4B3FE509D');
        $this->addSql('ALTER TABLE phoning_campaign DROP FOREIGN KEY FK_C3882BA49DF5350C');
        $this->addSql('ALTER TABLE phoning_campaign DROP FOREIGN KEY FK_C3882BA4CF1918FF');
        $this->addSql('ALTER TABLE phoning_campaign DROP FOREIGN KEY FK_C3882BA485C9D733');
        $this->addSql('ALTER TABLE phoning_campaign DROP FOREIGN KEY FK_C3882BA4DF6CFDC9');
        $this->addSql('ALTER TABLE phoning_campaign_history DROP FOREIGN KEY FK_EC191198A5626C52');
        $this->addSql('ALTER TABLE phoning_campaign_history DROP FOREIGN KEY FK_EC19119825F06C53');
        $this->addSql('ALTER TABLE phoning_campaign_history DROP FOREIGN KEY FK_EC191198F639F774');
        $this->addSql('ALTER TABLE phoning_campaign_history DROP FOREIGN KEY FK_EC1911983C5110AB');
        $this->addSql('ALTER TABLE political_committee DROP FOREIGN KEY FK_39FAEE95AAA61A99');
        $this->addSql('ALTER TABLE political_committee_feed_item DROP FOREIGN KEY FK_54369E83C7A72');
        $this->addSql('ALTER TABLE political_committee_feed_item DROP FOREIGN KEY FK_54369E83F675F31B');
        $this->addSql('ALTER TABLE political_committee_membership DROP FOREIGN KEY FK_FD85437B25F06C53');
        $this->addSql('ALTER TABLE political_committee_membership DROP FOREIGN KEY FK_FD85437BC7A72');
        $this->addSql('ALTER TABLE political_committee_quality DROP FOREIGN KEY FK_243D6D3A78632915');
        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY FK_84BCFA45F675F31B');
        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY FK_84BCFA459F2C3FAB');
        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY FK_84BCFA454B09E92C');
        $this->addSql('ALTER TABLE poll_choice DROP FOREIGN KEY FK_2DAE19C93C947C0F');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE998666D1');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE25F06C53');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE94A4C7D4');
        $this->addSql('ALTER TABLE procuration_proxies_to_election_rounds DROP FOREIGN KEY FK_D075F5A9E15E419B');
        $this->addSql('ALTER TABLE procuration_proxies_to_election_rounds DROP FOREIGN KEY FK_D075F5A9FCBF5E32');
        $this->addSql('ALTER TABLE procuration_requests DROP FOREIGN KEY FK_9769FD842F1B6663');
        $this->addSql('ALTER TABLE procuration_requests DROP FOREIGN KEY FK_9769FD84888FDEEE');
        $this->addSql('ALTER TABLE procuration_requests_to_election_rounds DROP FOREIGN KEY FK_A47BBD53128D9C53');
        $this->addSql('ALTER TABLE procuration_requests_to_election_rounds DROP FOREIGN KEY FK_A47BBD53FCBF5E32');
        $this->addSql('ALTER TABLE programmatic_foundation_measure DROP FOREIGN KEY FK_213A5F1EF0ED738A');
        $this->addSql('ALTER TABLE programmatic_foundation_measure_tag DROP FOREIGN KEY FK_F004297F5DA37D00');
        $this->addSql('ALTER TABLE programmatic_foundation_measure_tag DROP FOREIGN KEY FK_F004297FBAD26311');
        $this->addSql('ALTER TABLE programmatic_foundation_project DROP FOREIGN KEY FK_8E8E96D55DA37D00');
        $this->addSql('ALTER TABLE programmatic_foundation_project_tag DROP FOREIGN KEY FK_9F63872166D1F9C');
        $this->addSql('ALTER TABLE programmatic_foundation_project_tag DROP FOREIGN KEY FK_9F63872BAD26311');
        $this->addSql('ALTER TABLE programmatic_foundation_sub_approach DROP FOREIGN KEY FK_735C1D0115140614');
        $this->addSql('ALTER TABLE projection_managed_users_zone DROP FOREIGN KEY FK_E4D4ADCDC679DD78');
        $this->addSql('ALTER TABLE projection_managed_users_zone DROP FOREIGN KEY FK_E4D4ADCD9F2C3FAB');
        $this->addSql('ALTER TABLE proposal_proposal_theme DROP FOREIGN KEY FK_6B80CE41F4792058');
        $this->addSql('ALTER TABLE proposal_proposal_theme DROP FOREIGN KEY FK_6B80CE41B85948AF');
        $this->addSql('ALTER TABLE proposals DROP FOREIGN KEY FK_A5BA3A8FEA9FDD75');
        $this->addSql('ALTER TABLE push_token DROP FOREIGN KEY FK_51BC138125F06C53');
        $this->addSql('ALTER TABLE push_token DROP FOREIGN KEY FK_51BC138194A4C7D4');
        $this->addSql('ALTER TABLE qr_code DROP FOREIGN KEY FK_7D8B1FB5B03A8386');
        $this->addSql('ALTER TABLE referent DROP FOREIGN KEY FK_FE9AAC6CEA9FDD75');
        $this->addSql('ALTER TABLE referent_areas DROP FOREIGN KEY FK_75CEBC6C35E47E35');
        $this->addSql('ALTER TABLE referent_areas DROP FOREIGN KEY FK_75CEBC6CBD0F409C');
        $this->addSql('ALTER TABLE referent_managed_areas_tags DROP FOREIGN KEY FK_8BE84DD56B99CC25');
        $this->addSql('ALTER TABLE referent_managed_areas_tags DROP FOREIGN KEY FK_8BE84DD59C262DB3');
        $this->addSql('ALTER TABLE referent_managed_users_message DROP FOREIGN KEY FK_1E41AC6125F06C53');
        $this->addSql('ALTER TABLE referent_person_link DROP FOREIGN KEY FK_BC75A60A810B5A42');
        $this->addSql('ALTER TABLE referent_person_link DROP FOREIGN KEY FK_BC75A60A35E47E35');
        $this->addSql('ALTER TABLE referent_person_link DROP FOREIGN KEY FK_BC75A60A25F06C53');
        $this->addSql('ALTER TABLE referent_person_link_committee DROP FOREIGN KEY FK_1C97B2A5B3E4DE86');
        $this->addSql('ALTER TABLE referent_person_link_committee DROP FOREIGN KEY FK_1C97B2A5ED1A100B');
        $this->addSql('ALTER TABLE referent_space_access_information DROP FOREIGN KEY FK_CD8FDF4825F06C53');
        $this->addSql('ALTER TABLE referent_tags DROP FOREIGN KEY FK_135D29D99F2C3FAB');
        $this->addSql('ALTER TABLE referent_team_member DROP FOREIGN KEY FK_6C006717597D3FE');
        $this->addSql('ALTER TABLE referent_team_member DROP FOREIGN KEY FK_6C0067135E47E35');
        $this->addSql('ALTER TABLE referent_team_member_committee DROP FOREIGN KEY FK_EC89860BFE4CA267');
        $this->addSql('ALTER TABLE referent_team_member_committee DROP FOREIGN KEY FK_EC89860BED1A100B');
        $this->addSql('ALTER TABLE referent_user_filter_referent_tag DROP FOREIGN KEY FK_F2BB20FEEFAB50C4');
        $this->addSql('ALTER TABLE referent_user_filter_referent_tag DROP FOREIGN KEY FK_F2BB20FE9C262DB3');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745F675F31B');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745ED1A100B');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA74583B12DAC');
        $this->addSql('ALTER TABLE republican_silence_referent_tag DROP FOREIGN KEY FK_543DED2612359909');
        $this->addSql('ALTER TABLE republican_silence_referent_tag DROP FOREIGN KEY FK_543DED269C262DB3');
        $this->addSql('ALTER TABLE running_mate_request_application_request_tag DROP FOREIGN KEY FK_9D534FCFCEDF4387');
        $this->addSql('ALTER TABLE running_mate_request_application_request_tag DROP FOREIGN KEY FK_9D534FCF9644FEDA');
        $this->addSql('ALTER TABLE running_mate_request_referent_tag DROP FOREIGN KEY FK_53AB4FABCEDF4387');
        $this->addSql('ALTER TABLE running_mate_request_referent_tag DROP FOREIGN KEY FK_53AB4FAB9C262DB3');
        $this->addSql('ALTER TABLE running_mate_request_theme DROP FOREIGN KEY FK_A7326227CEDF4387');
        $this->addSql('ALTER TABLE running_mate_request_theme DROP FOREIGN KEY FK_A732622759027487');
        $this->addSql('ALTER TABLE saved_board_members DROP FOREIGN KEY FK_32865A32FDCCD727');
        $this->addSql('ALTER TABLE saved_board_members DROP FOREIGN KEY FK_32865A324821D202');
        $this->addSql('ALTER TABLE senator_area DROP FOREIGN KEY FK_D229BBF7AEC89CE1');
        $this->addSql('ALTER TABLE senatorial_candidate_areas_tags DROP FOREIGN KEY FK_F83208FAA7BF84E8');
        $this->addSql('ALTER TABLE senatorial_candidate_areas_tags DROP FOREIGN KEY FK_F83208FA9C262DB3');
        $this->addSql('ALTER TABLE sms_campaign DROP FOREIGN KEY FK_79E333DC848CC616');
        $this->addSql('ALTER TABLE sms_campaign DROP FOREIGN KEY FK_79E333DC4B09E92C');
        $this->addSql('ALTER TABLE social_shares DROP FOREIGN KEY FK_8E1413A085040FAD');
        $this->addSql('ALTER TABLE social_shares DROP FOREIGN KEY FK_8E1413A0EA9FDD75');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F9DF5350C');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FCF1918FF');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F85C9D733');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FDF6CFDC9');
        $this->addSql('ALTER TABLE team_member DROP FOREIGN KEY FK_6FFBDA1296CD8AE');
        $this->addSql('ALTER TABLE team_member DROP FOREIGN KEY FK_6FFBDA125F06C53');
        $this->addSql('ALTER TABLE team_member_history DROP FOREIGN KEY FK_1F330628296CD8AE');
        $this->addSql('ALTER TABLE team_member_history DROP FOREIGN KEY FK_1F33062825F06C53');
        $this->addSql('ALTER TABLE team_member_history DROP FOREIGN KEY FK_1F3306284B09E92C');
        $this->addSql('ALTER TABLE team_member_history DROP FOREIGN KEY FK_1F33062846E746A6');
        $this->addSql('ALTER TABLE territorial_council DROP FOREIGN KEY FK_B6DCA2A5B4D2A5D1');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B6A708DAFF');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B61FB354CD');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B6FC1537C1');
        $this->addSql('ALTER TABLE territorial_council_candidacy_invitation DROP FOREIGN KEY FK_DA86009A59B22434');
        $this->addSql('ALTER TABLE territorial_council_candidacy_invitation DROP FOREIGN KEY FK_DA86009A1FB354CD');
        $this->addSql('ALTER TABLE territorial_council_convocation DROP FOREIGN KEY FK_A9919BF0AAA61A99');
        $this->addSql('ALTER TABLE territorial_council_convocation DROP FOREIGN KEY FK_A9919BF0C7A72');
        $this->addSql('ALTER TABLE territorial_council_convocation DROP FOREIGN KEY FK_A9919BF0B03A8386');
        $this->addSql('ALTER TABLE territorial_council_election DROP FOREIGN KEY FK_14CBC36BAAA61A99');
        $this->addSql('ALTER TABLE territorial_council_election DROP FOREIGN KEY FK_14CBC36B8649F5F1');
        $this->addSql('ALTER TABLE territorial_council_election DROP FOREIGN KEY FK_14CBC36BFAC7D83F');
        $this->addSql('ALTER TABLE territorial_council_election_poll_choice DROP FOREIGN KEY FK_63EBCF6B8649F5F1');
        $this->addSql('ALTER TABLE territorial_council_election_poll_vote DROP FOREIGN KEY FK_BCDA0C15998666D1');
        $this->addSql('ALTER TABLE territorial_council_election_poll_vote DROP FOREIGN KEY FK_BCDA0C151FB354CD');
        $this->addSql('ALTER TABLE territorial_council_feed_item DROP FOREIGN KEY FK_45241D62AAA61A99');
        $this->addSql('ALTER TABLE territorial_council_feed_item DROP FOREIGN KEY FK_45241D62F675F31B');
        $this->addSql('ALTER TABLE territorial_council_membership DROP FOREIGN KEY FK_2A99831625F06C53');
        $this->addSql('ALTER TABLE territorial_council_membership DROP FOREIGN KEY FK_2A998316AAA61A99');
        $this->addSql('ALTER TABLE territorial_council_membership_log DROP FOREIGN KEY FK_2F6D242025F06C53');
        $this->addSql('ALTER TABLE territorial_council_official_report DROP FOREIGN KEY FK_8D80D385C7A72');
        $this->addSql('ALTER TABLE territorial_council_official_report DROP FOREIGN KEY FK_8D80D385F675F31B');
        $this->addSql('ALTER TABLE territorial_council_official_report DROP FOREIGN KEY FK_8D80D385B03A8386');
        $this->addSql('ALTER TABLE territorial_council_official_report DROP FOREIGN KEY FK_8D80D385896DBBDE');
        $this->addSql('ALTER TABLE territorial_council_official_report_document DROP FOREIGN KEY FK_78C1161DB03A8386');
        $this->addSql('ALTER TABLE territorial_council_official_report_document DROP FOREIGN KEY FK_78C1161D4BD2A4C0');
        $this->addSql('ALTER TABLE territorial_council_quality DROP FOREIGN KEY FK_C018E022E797FAB0');
        $this->addSql('ALTER TABLE territorial_council_referent_tag DROP FOREIGN KEY FK_78DBEB90AAA61A99');
        $this->addSql('ALTER TABLE territorial_council_referent_tag DROP FOREIGN KEY FK_78DBEB909C262DB3');
        $this->addSql('ALTER TABLE territorial_council_zone DROP FOREIGN KEY FK_9467B41EAAA61A99');
        $this->addSql('ALTER TABLE territorial_council_zone DROP FOREIGN KEY FK_9467B41E9F2C3FAB');
        $this->addSql('ALTER TABLE thematic_community_membership DROP FOREIGN KEY FK_22B6AC05FDA7B0BF');
        $this->addSql('ALTER TABLE thematic_community_membership DROP FOREIGN KEY FK_22B6AC0525F06C53');
        $this->addSql('ALTER TABLE thematic_community_membership DROP FOREIGN KEY FK_22B6AC05E7A1254A');
        $this->addSql('ALTER TABLE
          thematic_community_membership_user_list_definition
        DROP
          FOREIGN KEY FK_58815EB9403AE2A5');
        $this->addSql('ALTER TABLE
          thematic_community_membership_user_list_definition
        DROP
          FOREIGN KEY FK_58815EB9F74563E3');
        $this->addSql('ALTER TABLE timeline_manifesto_translations DROP FOREIGN KEY FK_F7BD6C172C2AC5D3');
        $this->addSql('ALTER TABLE timeline_manifestos DROP FOREIGN KEY FK_C6ED4403EA9FDD75');
        $this->addSql('ALTER TABLE timeline_measure_translations DROP FOREIGN KEY FK_5C9EB6072C2AC5D3');
        $this->addSql('ALTER TABLE timeline_measures DROP FOREIGN KEY FK_BA475ED737E924');
        $this->addSql('ALTER TABLE timeline_measures_profiles DROP FOREIGN KEY FK_B83D81AE5DA37D00');
        $this->addSql('ALTER TABLE timeline_measures_profiles DROP FOREIGN KEY FK_B83D81AECCFA12B8');
        $this->addSql('ALTER TABLE timeline_profile_translations DROP FOREIGN KEY FK_41B3A6DA2C2AC5D3');
        $this->addSql('ALTER TABLE timeline_theme_translations DROP FOREIGN KEY FK_F81F72932C2AC5D3');
        $this->addSql('ALTER TABLE timeline_themes DROP FOREIGN KEY FK_8ADDB8F6EA9FDD75');
        $this->addSql('ALTER TABLE timeline_themes_measures DROP FOREIGN KEY FK_EB8A7B0C5DA37D00');
        $this->addSql('ALTER TABLE timeline_themes_measures DROP FOREIGN KEY FK_EB8A7B0C59027487');
        $this->addSql('ALTER TABLE ton_macron_friend_invitation_has_choices DROP FOREIGN KEY FK_BB3BCAEEA35D7AF0');
        $this->addSql('ALTER TABLE ton_macron_friend_invitation_has_choices DROP FOREIGN KEY FK_BB3BCAEE998666D1');
        $this->addSql('ALTER TABLE unregistration_referent_tag DROP FOREIGN KEY FK_59B7AC414D824CA');
        $this->addSql('ALTER TABLE unregistration_referent_tag DROP FOREIGN KEY FK_59B7AC49C262DB3');
        $this->addSql('ALTER TABLE unregistrations DROP FOREIGN KEY FK_F9E4AA0C5B30B80B');
        $this->addSql('ALTER TABLE user_authorizations DROP FOREIGN KEY FK_40448230A76ED395');
        $this->addSql('ALTER TABLE user_authorizations DROP FOREIGN KEY FK_4044823019EB6921');
        $this->addSql('ALTER TABLE
          user_authorizations
        CHANGE
          scopes scopes LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE volunteer_request_application_request_tag DROP FOREIGN KEY FK_6F3FA269B8D6887');
        $this->addSql('ALTER TABLE volunteer_request_application_request_tag DROP FOREIGN KEY FK_6F3FA2699644FEDA');
        $this->addSql('ALTER TABLE volunteer_request_referent_tag DROP FOREIGN KEY FK_DA291742B8D6887');
        $this->addSql('ALTER TABLE volunteer_request_referent_tag DROP FOREIGN KEY FK_DA2917429C262DB3');
        $this->addSql('ALTER TABLE volunteer_request_technical_skill DROP FOREIGN KEY FK_7F8C5C1EB8D6887');
        $this->addSql('ALTER TABLE volunteer_request_technical_skill DROP FOREIGN KEY FK_7F8C5C1EE98F0EFD');
        $this->addSql('ALTER TABLE volunteer_request_theme DROP FOREIGN KEY FK_5427AF53B8D6887');
        $this->addSql('ALTER TABLE volunteer_request_theme DROP FOREIGN KEY FK_5427AF5359027487');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349FCBF5E32');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349B03A8386');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349896DBBDE');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB3498BAC62AF');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349F3F90B30');
        $this->addSql('ALTER TABLE vote_result_list DROP FOREIGN KEY FK_677ED502DB567AF4');
        $this->addSql('ALTER TABLE vote_result_list_collection DROP FOREIGN KEY FK_9C1DD9638BAC62AF');
        $this->addSql('ALTER TABLE vote_result_list_collection DROP FOREIGN KEY FK_9C1DD963FCBF5E32');
        $this->addSql('ALTER TABLE voting_platform_candidate DROP FOREIGN KEY FK_3F426D6D5F0A9B94');
        $this->addSql('ALTER TABLE voting_platform_candidate DROP FOREIGN KEY FK_3F426D6D25F06C53');
        $this->addSql('ALTER TABLE voting_platform_candidate_group DROP FOREIGN KEY FK_2C1A353AC1E98F21');
        $this->addSql('ALTER TABLE voting_platform_candidate_group_result DROP FOREIGN KEY FK_7249D5375F0A9B94');
        $this->addSql('ALTER TABLE voting_platform_candidate_group_result DROP FOREIGN KEY FK_7249D537B5BA5CC5');
        $this->addSql('ALTER TABLE
          voting_platform_candidate_group_result
        CHANGE
          total_mentions total_mentions LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE voting_platform_election DROP FOREIGN KEY FK_4E144C94FAC7D83F');
        $this->addSql('ALTER TABLE voting_platform_election_entity DROP FOREIGN KEY FK_7AAD259FED1A100B');
        $this->addSql('ALTER TABLE voting_platform_election_entity DROP FOREIGN KEY FK_7AAD259FAAA61A99');
        $this->addSql('ALTER TABLE voting_platform_election_entity DROP FOREIGN KEY FK_7AAD259FA708DAFF');
        $this->addSql('ALTER TABLE voting_platform_election_pool DROP FOREIGN KEY FK_7225D6EFA708DAFF');
        $this->addSql('ALTER TABLE voting_platform_election_pool_result DROP FOREIGN KEY FK_13C1C73FC1E98F21');
        $this->addSql('ALTER TABLE voting_platform_election_pool_result DROP FOREIGN KEY FK_13C1C73F8FFC0F0B');
        $this->addSql('ALTER TABLE voting_platform_election_result DROP FOREIGN KEY FK_67EFA0E4A708DAFF');
        $this->addSql('ALTER TABLE voting_platform_election_round DROP FOREIGN KEY FK_F15D87B7A708DAFF');
        $this->addSql('ALTER TABLE voting_platform_election_round_election_pool DROP FOREIGN KEY FK_E6665F19FCBF5E32');
        $this->addSql('ALTER TABLE voting_platform_election_round_election_pool DROP FOREIGN KEY FK_E6665F19C1E98F21');
        $this->addSql('ALTER TABLE voting_platform_election_round_result DROP FOREIGN KEY FK_F2670966FCBF5E32');
        $this->addSql('ALTER TABLE voting_platform_election_round_result DROP FOREIGN KEY FK_F267096619FCFB29');
        $this->addSql('ALTER TABLE voting_platform_vote DROP FOREIGN KEY FK_DCBB2B7BEBB4B8AD');
        $this->addSql('ALTER TABLE voting_platform_vote DROP FOREIGN KEY FK_DCBB2B7BFCBF5E32');
        $this->addSql('ALTER TABLE voting_platform_vote_choice DROP FOREIGN KEY FK_B009F31145EB7186');
        $this->addSql('ALTER TABLE voting_platform_vote_choice DROP FOREIGN KEY FK_B009F3115F0A9B94');
        $this->addSql('ALTER TABLE voting_platform_vote_choice DROP FOREIGN KEY FK_B009F311C1E98F21');
        $this->addSql('ALTER TABLE voting_platform_vote_result DROP FOREIGN KEY FK_62C86890FCBF5E32');
        $this->addSql('ALTER TABLE voting_platform_voter DROP FOREIGN KEY FK_AB02EC0225F06C53');
        $this->addSql('ALTER TABLE voting_platform_voters_list DROP FOREIGN KEY FK_3C73500DA708DAFF');
        $this->addSql('ALTER TABLE voting_platform_voters_list_voter DROP FOREIGN KEY FK_7CC26956FB0C8C84');
        $this->addSql('ALTER TABLE voting_platform_voters_list_voter DROP FOREIGN KEY FK_7CC26956EBB4B8AD');
        $this->addSql('ALTER TABLE web_hooks DROP FOREIGN KEY FK_CDB836AD19EB6921');
        $this->addSql('ALTER TABLE
          web_hooks
        CHANGE
          callbacks callbacks LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');

        $this->addSql('DROP TABLE adherent_activation_keys');

        $this->addSql('DROP TABLE adherent_adherent_tag');

        $this->addSql('DROP TABLE adherent_certification_histories');

        $this->addSql('DROP TABLE adherent_change_email_token');

        $this->addSql('DROP TABLE adherent_charter');

        $this->addSql('DROP TABLE adherent_commitment');

        $this->addSql('DROP TABLE adherent_email_subscribe_token');

        $this->addSql('DROP TABLE adherent_email_subscription_histories');

        $this->addSql('DROP TABLE adherent_email_subscription_history_referent_tag');

        $this->addSql('DROP TABLE adherent_instance_quality');

        $this->addSql('DROP TABLE adherent_mandate');

        $this->addSql('DROP TABLE adherent_message_filters');

        $this->addSql('DROP TABLE adherent_messages');

        $this->addSql('DROP TABLE adherent_referent_tag');

        $this->addSql('DROP TABLE adherent_reset_password_tokens');

        $this->addSql('DROP TABLE adherent_segment');

        $this->addSql('DROP TABLE adherent_subscription_type');

        $this->addSql('DROP TABLE adherent_tags');

        $this->addSql('DROP TABLE adherent_thematic_community');

        $this->addSql('DROP TABLE adherent_zone');

        $this->addSql('DROP TABLE adherents');

        $this->addSql('DROP TABLE administrator_export_history');

        $this->addSql('DROP TABLE administrators');

        $this->addSql('DROP TABLE algolia_candidature');

        $this->addSql('DROP TABLE application_request_running_mate');

        $this->addSql('DROP TABLE application_request_tag');

        $this->addSql('DROP TABLE application_request_technical_skill');

        $this->addSql('DROP TABLE application_request_theme');

        $this->addSql('DROP TABLE application_request_volunteer');

        $this->addSql('DROP TABLE article_proposal_theme');

        $this->addSql('DROP TABLE articles');

        $this->addSql('DROP TABLE articles_categories');

        $this->addSql('DROP TABLE assessor_managed_areas');

        $this->addSql('DROP TABLE assessor_requests');

        $this->addSql('DROP TABLE assessor_requests_vote_place_wishes');

        $this->addSql('DROP TABLE assessor_role_association');

        $this->addSql('DROP TABLE audience');

        $this->addSql('DROP TABLE audience_segment');

        $this->addSql('DROP TABLE audience_snapshot');

        $this->addSql('DROP TABLE audience_snapshot_zone');

        $this->addSql('DROP TABLE audience_zone');

        $this->addSql('DROP TABLE banned_adherent');

        $this->addSql('DROP TABLE biography_executive_office_member');

        $this->addSql('DROP TABLE board_member');

        $this->addSql('DROP TABLE board_member_roles');

        $this->addSql('DROP TABLE candidate_managed_area');

        $this->addSql('DROP TABLE cause');

        $this->addSql('DROP TABLE cause_follower');

        $this->addSql('DROP TABLE cause_quick_action');

        $this->addSql('DROP TABLE certification_request');

        $this->addSql('DROP TABLE chez_vous_cities');

        $this->addSql('DROP TABLE chez_vous_departments');

        $this->addSql('DROP TABLE chez_vous_markers');

        $this->addSql('DROP TABLE chez_vous_measure_types');

        $this->addSql('DROP TABLE chez_vous_measures');

        $this->addSql('DROP TABLE chez_vous_regions');

        $this->addSql('DROP TABLE cities');

        $this->addSql('DROP TABLE clarifications');

        $this->addSql('DROP TABLE cms_block');

        $this->addSql('DROP TABLE coalition');

        $this->addSql('DROP TABLE coalition_follower');

        $this->addSql('DROP TABLE coalition_moderator_role_association');

        $this->addSql('DROP TABLE committee_candidacies_group');

        $this->addSql('DROP TABLE committee_candidacy');

        $this->addSql('DROP TABLE committee_candidacy_invitation');

        $this->addSql('DROP TABLE committee_election');

        $this->addSql('DROP TABLE committee_feed_item');

        $this->addSql('DROP TABLE committee_feed_item_user_documents');

        $this->addSql('DROP TABLE committee_membership_history_referent_tag');

        $this->addSql('DROP TABLE committee_merge_histories');

        $this->addSql('DROP TABLE committee_merge_histories_merged_memberships');

        $this->addSql('DROP TABLE committee_provisional_supervisor');

        $this->addSql('DROP TABLE committee_referent_tag');

        $this->addSql('DROP TABLE committee_zone');

        $this->addSql('DROP TABLE committees');

        $this->addSql('DROP TABLE committees_membership_histories');

        $this->addSql('DROP TABLE committees_memberships');

        $this->addSql('DROP TABLE consular_district');

        $this->addSql('DROP TABLE consular_managed_area');

        $this->addSql('DROP TABLE coordinator_managed_areas');

        $this->addSql('DROP TABLE custom_search_results');

        $this->addSql('DROP TABLE department');

        $this->addSql('DROP TABLE deputy_managed_users_message');

        $this->addSql('DROP TABLE designation');

        $this->addSql('DROP TABLE designation_referent_tag');

        $this->addSql('DROP TABLE device_zone');

        $this->addSql('DROP TABLE devices');

        $this->addSql('DROP TABLE districts');

        $this->addSql('DROP TABLE donation_donation_tag');

        $this->addSql('DROP TABLE donation_tags');

        $this->addSql('DROP TABLE donation_transactions');

        $this->addSql('DROP TABLE donations');

        $this->addSql('DROP TABLE donator_donator_tag');

        $this->addSql('DROP TABLE donator_identifier');

        $this->addSql('DROP TABLE donator_kinship');

        $this->addSql('DROP TABLE donator_tags');

        $this->addSql('DROP TABLE donators');

        $this->addSql('DROP TABLE elected_representative');

        $this->addSql('DROP TABLE elected_representative_label');

        $this->addSql('DROP TABLE elected_representative_mandate');

        $this->addSql('DROP TABLE elected_representative_political_function');

        $this->addSql('DROP TABLE elected_representative_social_network_link');

        $this->addSql('DROP TABLE elected_representative_sponsorship');

        $this->addSql('DROP TABLE elected_representative_user_list_definition');

        $this->addSql('DROP TABLE elected_representative_user_list_definition_history');

        $this->addSql('DROP TABLE elected_representative_zone');

        $this->addSql('DROP TABLE elected_representative_zone_category');

        $this->addSql('DROP TABLE elected_representative_zone_parent');

        $this->addSql('DROP TABLE elected_representative_zone_referent_tag');

        $this->addSql('DROP TABLE election_city_candidate');

        $this->addSql('DROP TABLE election_city_card');

        $this->addSql('DROP TABLE election_city_contact');

        $this->addSql('DROP TABLE election_city_manager');

        $this->addSql('DROP TABLE election_city_partner');

        $this->addSql('DROP TABLE election_city_prevision');

        $this->addSql('DROP TABLE election_rounds');

        $this->addSql('DROP TABLE elections');

        $this->addSql('DROP TABLE email_templates');

        $this->addSql('DROP TABLE emails');

        $this->addSql('DROP TABLE epci');

        $this->addSql('DROP TABLE event_group_category');

        $this->addSql('DROP TABLE event_referent_tag');

        $this->addSql('DROP TABLE event_user_documents');

        $this->addSql('DROP TABLE event_zone');

        $this->addSql('DROP TABLE events');

        $this->addSql('DROP TABLE events_categories');

        $this->addSql('DROP TABLE events_invitations');

        $this->addSql('DROP TABLE events_registrations');

        $this->addSql('DROP TABLE facebook_profiles');

        $this->addSql('DROP TABLE facebook_videos');

        $this->addSql('DROP TABLE failed_login_attempt');

        $this->addSql('DROP TABLE filesystem_file');

        $this->addSql('DROP TABLE filesystem_file_permission');

        $this->addSql('DROP TABLE formation_axes');

        $this->addSql('DROP TABLE formation_files');

        $this->addSql('DROP TABLE formation_modules');

        $this->addSql('DROP TABLE formation_paths');

        $this->addSql('DROP TABLE geo_borough');

        $this->addSql('DROP TABLE geo_canton');

        $this->addSql('DROP TABLE geo_city');

        $this->addSql('DROP TABLE geo_city_canton');

        $this->addSql('DROP TABLE geo_city_community');

        $this->addSql('DROP TABLE geo_city_community_department');

        $this->addSql('DROP TABLE geo_city_district');

        $this->addSql('DROP TABLE geo_consular_district');

        $this->addSql('DROP TABLE geo_country');

        $this->addSql('DROP TABLE geo_custom_zone');

        $this->addSql('DROP TABLE geo_data');

        $this->addSql('DROP TABLE geo_department');

        $this->addSql('DROP TABLE geo_district');

        $this->addSql('DROP TABLE geo_foreign_district');

        $this->addSql('DROP TABLE geo_region');

        $this->addSql('DROP TABLE geo_zone');

        $this->addSql('DROP TABLE geo_zone_parent');

        $this->addSql('DROP TABLE home_blocks');

        $this->addSql('DROP TABLE image');

        $this->addSql('DROP TABLE instance_quality');

        $this->addSql('DROP TABLE institutional_events_categories');

        $this->addSql('DROP TABLE interactive_choices');

        $this->addSql('DROP TABLE interactive_invitation_has_choices');

        $this->addSql('DROP TABLE interactive_invitations');

        $this->addSql('DROP TABLE internal_api_application');

        $this->addSql('DROP TABLE invitations');

        $this->addSql('DROP TABLE je_marche_reports');

        $this->addSql('DROP TABLE jecoute_choice');

        $this->addSql('DROP TABLE jecoute_data_answer');

        $this->addSql('DROP TABLE jecoute_data_answer_selected_choices');

        $this->addSql('DROP TABLE jecoute_data_survey');

        $this->addSql('DROP TABLE jecoute_managed_areas');

        $this->addSql('DROP TABLE jecoute_news');

        $this->addSql('DROP TABLE jecoute_question');

        $this->addSql('DROP TABLE jecoute_region');

        $this->addSql('DROP TABLE jecoute_riposte');

        $this->addSql('DROP TABLE jecoute_suggested_question');

        $this->addSql('DROP TABLE jecoute_survey');

        $this->addSql('DROP TABLE jecoute_survey_question');

        $this->addSql('DROP TABLE jemarche_data_survey');

        $this->addSql('DROP TABLE legislative_candidates');

        $this->addSql('DROP TABLE legislative_district_zones');

        $this->addSql('DROP TABLE list_total_result');

        $this->addSql('DROP TABLE live_links');

        $this->addSql('DROP TABLE lre_area');

        $this->addSql('DROP TABLE mailchimp_campaign');

        $this->addSql('DROP TABLE mailchimp_campaign_mailchimp_segment');

        $this->addSql('DROP TABLE mailchimp_campaign_report');

        $this->addSql('DROP TABLE mailchimp_segment');

        $this->addSql('DROP TABLE medias');

        $this->addSql('DROP TABLE ministry_list_total_result');

        $this->addSql('DROP TABLE ministry_vote_result');

        $this->addSql('DROP TABLE mooc');

        $this->addSql('DROP TABLE mooc_attachment_file');

        $this->addSql('DROP TABLE mooc_attachment_link');

        $this->addSql('DROP TABLE mooc_chapter');

        $this->addSql('DROP TABLE mooc_element_attachment_file');

        $this->addSql('DROP TABLE mooc_element_attachment_link');

        $this->addSql('DROP TABLE mooc_elements');

        $this->addSql('DROP TABLE municipal_chief_areas');

        $this->addSql('DROP TABLE municipal_manager_role_association');

        $this->addSql('DROP TABLE municipal_manager_role_association_cities');

        $this->addSql('DROP TABLE municipal_manager_supervisor_role');

        $this->addSql('DROP TABLE my_team_delegate_access_committee');

        $this->addSql('DROP TABLE my_team_delegated_access');

        $this->addSql('DROP TABLE national_council_candidacies_group');

        $this->addSql('DROP TABLE national_council_candidacy');

        $this->addSql('DROP TABLE national_council_election');

        $this->addSql('DROP TABLE newsletter_invitations');

        $this->addSql('DROP TABLE newsletter_subscriptions');

        $this->addSql('DROP TABLE notification');

        $this->addSql('DROP TABLE oauth_access_tokens');

        $this->addSql('DROP TABLE oauth_auth_codes');

        $this->addSql('DROP TABLE oauth_clients');

        $this->addSql('DROP TABLE oauth_refresh_tokens');

        $this->addSql('DROP TABLE order_articles');

        $this->addSql('DROP TABLE order_section_order_article');

        $this->addSql('DROP TABLE order_sections');

        $this->addSql('DROP TABLE organizational_chart_item');

        $this->addSql('DROP TABLE pages');

        $this->addSql('DROP TABLE pap_address');

        $this->addSql('DROP TABLE pap_building');

        $this->addSql('DROP TABLE pap_building_block');

        $this->addSql('DROP TABLE pap_building_block_statistics');

        $this->addSql('DROP TABLE pap_building_statistics');

        $this->addSql('DROP TABLE pap_campaign');

        $this->addSql('DROP TABLE pap_campaign_history');

        $this->addSql('DROP TABLE pap_floor');

        $this->addSql('DROP TABLE pap_floor_statistics');

        $this->addSql('DROP TABLE pap_voter');

        $this->addSql('DROP TABLE phoning_campaign');

        $this->addSql('DROP TABLE phoning_campaign_history');

        $this->addSql('DROP TABLE political_committee');

        $this->addSql('DROP TABLE political_committee_feed_item');

        $this->addSql('DROP TABLE political_committee_membership');

        $this->addSql('DROP TABLE political_committee_quality');

        $this->addSql('DROP TABLE poll');

        $this->addSql('DROP TABLE poll_choice');

        $this->addSql('DROP TABLE poll_vote');

        $this->addSql('DROP TABLE procuration_managed_areas');

        $this->addSql('DROP TABLE procuration_proxies');

        $this->addSql('DROP TABLE procuration_proxies_to_election_rounds');

        $this->addSql('DROP TABLE procuration_requests');

        $this->addSql('DROP TABLE procuration_requests_to_election_rounds');

        $this->addSql('DROP TABLE programmatic_foundation_approach');

        $this->addSql('DROP TABLE programmatic_foundation_measure');

        $this->addSql('DROP TABLE programmatic_foundation_measure_tag');

        $this->addSql('DROP TABLE programmatic_foundation_project');

        $this->addSql('DROP TABLE programmatic_foundation_project_tag');

        $this->addSql('DROP TABLE programmatic_foundation_sub_approach');

        $this->addSql('DROP TABLE programmatic_foundation_tag');

        $this->addSql('DROP TABLE projection_managed_users');

        $this->addSql('DROP TABLE projection_managed_users_zone');

        $this->addSql('DROP TABLE proposal_proposal_theme');

        $this->addSql('DROP TABLE proposals');

        $this->addSql('DROP TABLE proposals_themes');

        $this->addSql('DROP TABLE push_token');

        $this->addSql('DROP TABLE qr_code');

        $this->addSql('DROP TABLE redirections');

        $this->addSql('DROP TABLE referent');

        $this->addSql('DROP TABLE referent_area');

        $this->addSql('DROP TABLE referent_areas');

        $this->addSql('DROP TABLE referent_managed_areas');

        $this->addSql('DROP TABLE referent_managed_areas_tags');

        $this->addSql('DROP TABLE referent_managed_users_message');

        $this->addSql('DROP TABLE referent_person_link');

        $this->addSql('DROP TABLE referent_person_link_committee');

        $this->addSql('DROP TABLE referent_space_access_information');

        $this->addSql('DROP TABLE referent_tags');

        $this->addSql('DROP TABLE referent_team_member');

        $this->addSql('DROP TABLE referent_team_member_committee');

        $this->addSql('DROP TABLE referent_user_filter_referent_tag');

        $this->addSql('DROP TABLE region');

        $this->addSql('DROP TABLE reports');

        $this->addSql('DROP TABLE republican_silence');

        $this->addSql('DROP TABLE republican_silence_referent_tag');

        $this->addSql('DROP TABLE roles');

        $this->addSql('DROP TABLE running_mate_request_application_request_tag');

        $this->addSql('DROP TABLE running_mate_request_referent_tag');

        $this->addSql('DROP TABLE running_mate_request_theme');

        $this->addSql('DROP TABLE saved_board_members');

        $this->addSql('DROP TABLE scope');

        $this->addSql('DROP TABLE senator_area');

        $this->addSql('DROP TABLE senatorial_candidate_areas');

        $this->addSql('DROP TABLE senatorial_candidate_areas_tags');

        $this->addSql('DROP TABLE sms_campaign');

        $this->addSql('DROP TABLE sms_stop_history');

        $this->addSql('DROP TABLE social_share_categories');

        $this->addSql('DROP TABLE social_shares');

        $this->addSql('DROP TABLE subscription_type');

        $this->addSql('DROP TABLE team');

        $this->addSql('DROP TABLE team_member');

        $this->addSql('DROP TABLE team_member_history');

        $this->addSql('DROP TABLE territorial_council');

        $this->addSql('DROP TABLE territorial_council_candidacies_group');

        $this->addSql('DROP TABLE territorial_council_candidacy');

        $this->addSql('DROP TABLE territorial_council_candidacy_invitation');

        $this->addSql('DROP TABLE territorial_council_convocation');

        $this->addSql('DROP TABLE territorial_council_election');

        $this->addSql('DROP TABLE territorial_council_election_poll');

        $this->addSql('DROP TABLE territorial_council_election_poll_choice');

        $this->addSql('DROP TABLE territorial_council_election_poll_vote');

        $this->addSql('DROP TABLE territorial_council_feed_item');

        $this->addSql('DROP TABLE territorial_council_membership');

        $this->addSql('DROP TABLE territorial_council_membership_log');

        $this->addSql('DROP TABLE territorial_council_official_report');

        $this->addSql('DROP TABLE territorial_council_official_report_document');

        $this->addSql('DROP TABLE territorial_council_quality');

        $this->addSql('DROP TABLE territorial_council_referent_tag');

        $this->addSql('DROP TABLE territorial_council_zone');

        $this->addSql('DROP TABLE thematic_community');

        $this->addSql('DROP TABLE thematic_community_contact');

        $this->addSql('DROP TABLE thematic_community_membership');

        $this->addSql('DROP TABLE thematic_community_membership_user_list_definition');

        $this->addSql('DROP TABLE timeline_manifesto_translations');

        $this->addSql('DROP TABLE timeline_manifestos');

        $this->addSql('DROP TABLE timeline_measure_translations');

        $this->addSql('DROP TABLE timeline_measures');

        $this->addSql('DROP TABLE timeline_measures_profiles');

        $this->addSql('DROP TABLE timeline_profile_translations');

        $this->addSql('DROP TABLE timeline_profiles');

        $this->addSql('DROP TABLE timeline_theme_translations');

        $this->addSql('DROP TABLE timeline_themes');

        $this->addSql('DROP TABLE timeline_themes_measures');

        $this->addSql('DROP TABLE ton_macron_choices');

        $this->addSql('DROP TABLE ton_macron_friend_invitation_has_choices');

        $this->addSql('DROP TABLE ton_macron_friend_invitations');

        $this->addSql('DROP TABLE unregistration_referent_tag');

        $this->addSql('DROP TABLE unregistrations');

        $this->addSql('DROP TABLE user_authorizations');

        $this->addSql('DROP TABLE user_documents');

        $this->addSql('DROP TABLE user_list_definition');

        $this->addSql('DROP TABLE volunteer_request_application_request_tag');

        $this->addSql('DROP TABLE volunteer_request_referent_tag');

        $this->addSql('DROP TABLE volunteer_request_technical_skill');

        $this->addSql('DROP TABLE volunteer_request_theme');

        $this->addSql('DROP TABLE vote_place');

        $this->addSql('DROP TABLE vote_result');

        $this->addSql('DROP TABLE vote_result_list');

        $this->addSql('DROP TABLE vote_result_list_collection');

        $this->addSql('DROP TABLE voting_platform_candidate');

        $this->addSql('DROP TABLE voting_platform_candidate_group');

        $this->addSql('DROP TABLE voting_platform_candidate_group_result');

        $this->addSql('DROP TABLE voting_platform_election');

        $this->addSql('DROP TABLE voting_platform_election_entity');

        $this->addSql('DROP TABLE voting_platform_election_pool');

        $this->addSql('DROP TABLE voting_platform_election_pool_result');

        $this->addSql('DROP TABLE voting_platform_election_result');

        $this->addSql('DROP TABLE voting_platform_election_round');

        $this->addSql('DROP TABLE voting_platform_election_round_election_pool');

        $this->addSql('DROP TABLE voting_platform_election_round_result');

        $this->addSql('DROP TABLE voting_platform_vote');

        $this->addSql('DROP TABLE voting_platform_vote_choice');

        $this->addSql('DROP TABLE voting_platform_vote_result');

        $this->addSql('DROP TABLE voting_platform_voter');

        $this->addSql('DROP TABLE voting_platform_voters_list');

        $this->addSql('DROP TABLE voting_platform_voters_list_voter');

        $this->addSql('DROP TABLE web_hooks');
    }
}
