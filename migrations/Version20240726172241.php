<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240726172241 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3A132C3C5');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3DC184E71');
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F949C262DB3');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA393494FA8');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3FCCAF6D5');
        $this->addSql('ALTER TABLE
          adherent_email_subscription_history_referent_tag
        DROP
          FOREIGN KEY FK_6FFBE6E88FCB8132');
        $this->addSql('ALTER TABLE
          adherent_email_subscription_history_referent_tag
        DROP
          FOREIGN KEY FK_6FFBE6E89C262DB3');
        $this->addSql('ALTER TABLE adherent_referent_tag DROP FOREIGN KEY FK_79E8AFFD25F06C53');
        $this->addSql('ALTER TABLE adherent_referent_tag DROP FOREIGN KEY FK_79E8AFFD9C262DB3');
        $this->addSql('ALTER TABLE committee_membership_history_referent_tag DROP FOREIGN KEY FK_B6A8C718123C64CE');
        $this->addSql('ALTER TABLE committee_membership_history_referent_tag DROP FOREIGN KEY FK_B6A8C7189C262DB3');
        $this->addSql('ALTER TABLE committee_referent_tag DROP FOREIGN KEY FK_285EB1C59C262DB3');
        $this->addSql('ALTER TABLE committee_referent_tag DROP FOREIGN KEY FK_285EB1C5ED1A100B');
        $this->addSql('ALTER TABLE designation_referent_tag DROP FOREIGN KEY FK_7538F35A9C262DB3');
        $this->addSql('ALTER TABLE designation_referent_tag DROP FOREIGN KEY FK_7538F35AFAC7D83F');
        $this->addSql('ALTER TABLE districts DROP FOREIGN KEY FK_68E318DC80E32C3E');
        $this->addSql('ALTER TABLE districts DROP FOREIGN KEY FK_68E318DC9C262DB3');
        $this->addSql('ALTER TABLE elected_representative_zone_referent_tag DROP FOREIGN KEY FK_D2B7A8C59C262DB3');
        $this->addSql('ALTER TABLE elected_representative_zone_referent_tag DROP FOREIGN KEY FK_D2B7A8C5BE31A103');
        $this->addSql('ALTER TABLE event_referent_tag DROP FOREIGN KEY FK_D3C8F5BE71F7E88B');
        $this->addSql('ALTER TABLE event_referent_tag DROP FOREIGN KEY FK_D3C8F5BE9C262DB3');
        $this->addSql('ALTER TABLE organizational_chart_item DROP FOREIGN KEY FK_29C1CBAC727ACA70');
        $this->addSql('ALTER TABLE organizational_chart_item DROP FOREIGN KEY FK_29C1CBACA977936C');
        $this->addSql('ALTER TABLE referent DROP FOREIGN KEY FK_FE9AAC6CEA9FDD75');
        $this->addSql('ALTER TABLE referent_areas DROP FOREIGN KEY FK_75CEBC6C35E47E35');
        $this->addSql('ALTER TABLE referent_areas DROP FOREIGN KEY FK_75CEBC6CBD0F409C');
        $this->addSql('ALTER TABLE referent_managed_areas_tags DROP FOREIGN KEY FK_8BE84DD56B99CC25');
        $this->addSql('ALTER TABLE referent_managed_areas_tags DROP FOREIGN KEY FK_8BE84DD59C262DB3');
        $this->addSql('ALTER TABLE referent_person_link DROP FOREIGN KEY FK_BC75A60A25F06C53');
        $this->addSql('ALTER TABLE referent_person_link DROP FOREIGN KEY FK_BC75A60A35E47E35');
        $this->addSql('ALTER TABLE referent_person_link DROP FOREIGN KEY FK_BC75A60A810B5A42');
        $this->addSql('ALTER TABLE referent_person_link_committee DROP FOREIGN KEY FK_1C97B2A5B3E4DE86');
        $this->addSql('ALTER TABLE referent_person_link_committee DROP FOREIGN KEY FK_1C97B2A5ED1A100B');
        $this->addSql('ALTER TABLE referent_space_access_information DROP FOREIGN KEY FK_CD8FDF4825F06C53');
        $this->addSql('ALTER TABLE referent_tags DROP FOREIGN KEY FK_135D29D99F2C3FAB');
        $this->addSql('ALTER TABLE referent_team_member DROP FOREIGN KEY FK_6C0067135E47E35');
        $this->addSql('ALTER TABLE referent_team_member DROP FOREIGN KEY FK_6C006717597D3FE');
        $this->addSql('ALTER TABLE referent_team_member_committee DROP FOREIGN KEY FK_EC89860BED1A100B');
        $this->addSql('ALTER TABLE referent_team_member_committee DROP FOREIGN KEY FK_EC89860BFE4CA267');
        $this->addSql('ALTER TABLE referent_user_filter_referent_tag DROP FOREIGN KEY FK_F2BB20FE9C262DB3');
        $this->addSql('ALTER TABLE referent_user_filter_referent_tag DROP FOREIGN KEY FK_F2BB20FEEFAB50C4');
        $this->addSql('ALTER TABLE running_mate_request_referent_tag DROP FOREIGN KEY FK_53AB4FAB9C262DB3');
        $this->addSql('ALTER TABLE running_mate_request_referent_tag DROP FOREIGN KEY FK_53AB4FABCEDF4387');
        $this->addSql('ALTER TABLE senator_area DROP FOREIGN KEY FK_D229BBF7AEC89CE1');
        $this->addSql('ALTER TABLE senatorial_candidate_areas_tags DROP FOREIGN KEY FK_F83208FA9C262DB3');
        $this->addSql('ALTER TABLE senatorial_candidate_areas_tags DROP FOREIGN KEY FK_F83208FAA7BF84E8');
        $this->addSql('ALTER TABLE unregistration_referent_tag DROP FOREIGN KEY FK_59B7AC414D824CA');
        $this->addSql('ALTER TABLE unregistration_referent_tag DROP FOREIGN KEY FK_59B7AC49C262DB3');
        $this->addSql('ALTER TABLE volunteer_request_referent_tag DROP FOREIGN KEY FK_DA2917429C262DB3');
        $this->addSql('ALTER TABLE volunteer_request_referent_tag DROP FOREIGN KEY FK_DA291742B8D6887');
        $this->addSql('DROP TABLE adherent_email_subscription_history_referent_tag');
        $this->addSql('DROP TABLE adherent_referent_tag');
        $this->addSql('DROP TABLE committee_membership_history_referent_tag');
        $this->addSql('DROP TABLE committee_referent_tag');
        $this->addSql('DROP TABLE designation_referent_tag');
        $this->addSql('DROP TABLE districts');
        $this->addSql('DROP TABLE elected_representative_zone_referent_tag');
        $this->addSql('DROP TABLE event_referent_tag');
        $this->addSql('DROP TABLE organizational_chart_item');
        $this->addSql('DROP TABLE referent');
        $this->addSql('DROP TABLE referent_area');
        $this->addSql('DROP TABLE referent_areas');
        $this->addSql('DROP TABLE referent_managed_areas');
        $this->addSql('DROP TABLE referent_managed_areas_tags');
        $this->addSql('DROP TABLE referent_person_link');
        $this->addSql('DROP TABLE referent_person_link_committee');
        $this->addSql('DROP TABLE referent_space_access_information');
        $this->addSql('DROP TABLE referent_tags');
        $this->addSql('DROP TABLE referent_team_member');
        $this->addSql('DROP TABLE referent_team_member_committee');
        $this->addSql('DROP TABLE referent_user_filter_referent_tag');
        $this->addSql('DROP TABLE running_mate_request_referent_tag');
        $this->addSql('DROP TABLE senator_area');
        $this->addSql('DROP TABLE senatorial_candidate_areas');
        $this->addSql('DROP TABLE senatorial_candidate_areas_tags');
        $this->addSql('DROP TABLE unregistration_referent_tag');
        $this->addSql('DROP TABLE volunteer_request_referent_tag');
        $this->addSql('DROP INDEX IDX_28CA9F949C262DB3 ON adherent_message_filters');
        $this->addSql('ALTER TABLE adherent_message_filters DROP referent_tag_id');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA31A912B27');
        $this->addSql('DROP INDEX UNIQ_562C7DA31A912B27 ON adherents');
        $this->addSql('DROP INDEX UNIQ_562C7DA393494FA8 ON adherents');
        $this->addSql('DROP INDEX UNIQ_562C7DA3A132C3C5 ON adherents');
        $this->addSql('DROP INDEX UNIQ_562C7DA3DC184E71 ON adherents');
        $this->addSql('DROP INDEX UNIQ_562C7DA3FCCAF6D5 ON adherents');
        $this->addSql('ALTER TABLE
          adherents
        DROP
          managed_area_id,
        DROP
          coordinator_committee_area_id,
        DROP
          senator_area_id,
        DROP
          managed_district_id,
        DROP
          senatorial_candidate_managed_area_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_email_subscription_history_referent_tag (
          email_subscription_history_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_6FFBE6E88FCB8132 (email_subscription_history_id),
          INDEX IDX_6FFBE6E89C262DB3 (referent_tag_id),
          PRIMARY KEY(
            email_subscription_history_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE adherent_referent_tag (
          adherent_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_79E8AFFD25F06C53 (adherent_id),
          INDEX IDX_79E8AFFD9C262DB3 (referent_tag_id),
          PRIMARY KEY(adherent_id, referent_tag_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
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
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE committee_referent_tag (
          committee_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_285EB1C59C262DB3 (referent_tag_id),
          INDEX IDX_285EB1C5ED1A100B (committee_id),
          PRIMARY KEY(committee_id, referent_tag_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE designation_referent_tag (
          designation_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_7538F35A9C262DB3 (referent_tag_id),
          INDEX IDX_7538F35AFAC7D83F (designation_id),
          PRIMARY KEY(designation_id, referent_tag_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE districts (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          geo_data_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED DEFAULT NULL,
          countries LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          code VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          number SMALLINT UNSIGNED NOT NULL,
          name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          department_code VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX district_department_code_number (department_code, number),
          UNIQUE INDEX UNIQ_68E318DC77153098 (code),
          UNIQUE INDEX UNIQ_68E318DC80E32C3E (geo_data_id),
          UNIQUE INDEX UNIQ_68E318DC9C262DB3 (referent_tag_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE elected_representative_zone_referent_tag (
          elected_representative_zone_id INT NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_D2B7A8C59C262DB3 (referent_tag_id),
          INDEX IDX_D2B7A8C5BE31A103 (elected_representative_zone_id),
          PRIMARY KEY(
            elected_representative_zone_id,
            referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE event_referent_tag (
          event_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_D3C8F5BE71F7E88B (event_id),
          INDEX IDX_D3C8F5BE9C262DB3 (referent_tag_id),
          PRIMARY KEY(event_id, referent_tag_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE organizational_chart_item (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          tree_root INT UNSIGNED DEFAULT NULL,
          parent_id INT UNSIGNED DEFAULT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          lft INT NOT NULL,
          lvl INT NOT NULL,
          rgt INT NOT NULL,
          TYPE VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_29C1CBAC727ACA70 (parent_id),
          INDEX IDX_29C1CBACA977936C (tree_root),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
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
          STATUS VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT \'DISABLED\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          display_media TINYINT(1) NOT NULL,
          INDEX IDX_FE9AAC6CEA9FDD75 (media_id),
          UNIQUE INDEX UNIQ_FE9AAC6C989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE referent_area (
          id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL,
          area_code VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          area_type VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          keywords LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_AB758097B5501F87 (area_code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE referent_areas (
          referent_id SMALLINT UNSIGNED NOT NULL,
          area_id SMALLINT UNSIGNED NOT NULL,
          INDEX IDX_75CEBC6C35E47E35 (referent_id),
          INDEX IDX_75CEBC6CBD0F409C (area_id),
          PRIMARY KEY(referent_id, area_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE referent_managed_areas (
          id INT AUTO_INCREMENT NOT NULL,
          marker_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          marker_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE referent_managed_areas_tags (
          referent_managed_area_id INT NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_8BE84DD56B99CC25 (referent_managed_area_id),
          INDEX IDX_8BE84DD59C262DB3 (referent_tag_id),
          PRIMARY KEY(
            referent_managed_area_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
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
          is_jecoute_manager TINYINT(1) DEFAULT 0 NOT NULL,
          co_referent VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          restricted_cities LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          INDEX IDX_BC75A60A25F06C53 (adherent_id),
          INDEX IDX_BC75A60A35E47E35 (referent_id),
          INDEX IDX_BC75A60A810B5A42 (
            person_organizational_chart_item_id
          ),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE referent_person_link_committee (
          referent_person_link_id INT UNSIGNED NOT NULL,
          committee_id INT UNSIGNED NOT NULL,
          INDEX IDX_1C97B2A5B3E4DE86 (referent_person_link_id),
          INDEX IDX_1C97B2A5ED1A100B (committee_id),
          PRIMARY KEY(
            referent_person_link_id, committee_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE referent_space_access_information (
          id INT AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          previous_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          last_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          UNIQUE INDEX UNIQ_CD8FDF4825F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE referent_tags (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          code VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          external_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          TYPE VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_135D29D99F2C3FAB (zone_id),
          UNIQUE INDEX UNIQ_135D29D95E237E06 (name),
          UNIQUE INDEX UNIQ_135D29D977153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE referent_team_member (
          id INT AUTO_INCREMENT NOT NULL,
          member_id INT UNSIGNED NOT NULL,
          referent_id INT UNSIGNED NOT NULL,
          limited TINYINT(1) DEFAULT 0 NOT NULL,
          restricted_cities LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          INDEX IDX_6C0067135E47E35 (referent_id),
          UNIQUE INDEX UNIQ_6C006717597D3FE (member_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE referent_team_member_committee (
          referent_team_member_id INT NOT NULL,
          committee_id INT UNSIGNED NOT NULL,
          INDEX IDX_EC89860BED1A100B (committee_id),
          INDEX IDX_EC89860BFE4CA267 (referent_team_member_id),
          PRIMARY KEY(
            referent_team_member_id, committee_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE referent_user_filter_referent_tag (
          referent_user_filter_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_F2BB20FE9C262DB3 (referent_tag_id),
          INDEX IDX_F2BB20FEEFAB50C4 (referent_user_filter_id),
          PRIMARY KEY(
            referent_user_filter_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE running_mate_request_referent_tag (
          running_mate_request_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_53AB4FAB9C262DB3 (referent_tag_id),
          INDEX IDX_53AB4FABCEDF4387 (running_mate_request_id),
          PRIMARY KEY(
            running_mate_request_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE senator_area (
          id INT AUTO_INCREMENT NOT NULL,
          department_tag_id INT UNSIGNED DEFAULT NULL,
          INDEX IDX_D229BBF7AEC89CE1 (department_tag_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE senatorial_candidate_areas (
          id INT AUTO_INCREMENT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE senatorial_candidate_areas_tags (
          senatorial_candidate_area_id INT NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_F83208FA9C262DB3 (referent_tag_id),
          INDEX IDX_F83208FAA7BF84E8 (senatorial_candidate_area_id),
          PRIMARY KEY(
            senatorial_candidate_area_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE unregistration_referent_tag (
          unregistration_id INT NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_59B7AC414D824CA (unregistration_id),
          INDEX IDX_59B7AC49C262DB3 (referent_tag_id),
          PRIMARY KEY(
            unregistration_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE volunteer_request_referent_tag (
          volunteer_request_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_DA2917429C262DB3 (referent_tag_id),
          INDEX IDX_DA291742B8D6887 (volunteer_request_id),
          PRIMARY KEY(
            volunteer_request_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          adherent_email_subscription_history_referent_tag
        ADD
          CONSTRAINT FK_6FFBE6E88FCB8132 FOREIGN KEY (email_subscription_history_id) REFERENCES adherent_email_subscription_histories (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_email_subscription_history_referent_tag
        ADD
          CONSTRAINT FK_6FFBE6E89C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_referent_tag
        ADD
          CONSTRAINT FK_79E8AFFD25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_referent_tag
        ADD
          CONSTRAINT FK_79E8AFFD9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_membership_history_referent_tag
        ADD
          CONSTRAINT FK_B6A8C718123C64CE FOREIGN KEY (
            committee_membership_history_id
          ) REFERENCES committees_membership_histories (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_membership_history_referent_tag
        ADD
          CONSTRAINT FK_B6A8C7189C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_referent_tag
        ADD
          CONSTRAINT FK_285EB1C59C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_referent_tag
        ADD
          CONSTRAINT FK_285EB1C5ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          designation_referent_tag
        ADD
          CONSTRAINT FK_7538F35A9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          designation_referent_tag
        ADD
          CONSTRAINT FK_7538F35AFAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          districts
        ADD
          CONSTRAINT FK_68E318DC80E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          districts
        ADD
          CONSTRAINT FK_68E318DC9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          elected_representative_zone_referent_tag
        ADD
          CONSTRAINT FK_D2B7A8C59C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_zone_referent_tag
        ADD
          CONSTRAINT FK_D2B7A8C5BE31A103 FOREIGN KEY (elected_representative_zone_id) REFERENCES elected_representative_zone (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          event_referent_tag
        ADD
          CONSTRAINT FK_D3C8F5BE71F7E88B FOREIGN KEY (event_id) REFERENCES EVENTS (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          event_referent_tag
        ADD
          CONSTRAINT FK_D3C8F5BE9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          organizational_chart_item
        ADD
          CONSTRAINT FK_29C1CBAC727ACA70 FOREIGN KEY (parent_id) REFERENCES organizational_chart_item (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          organizational_chart_item
        ADD
          CONSTRAINT FK_29C1CBACA977936C FOREIGN KEY (tree_root) REFERENCES organizational_chart_item (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent
        ADD
          CONSTRAINT FK_FE9AAC6CEA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          referent_areas
        ADD
          CONSTRAINT FK_75CEBC6C35E47E35 FOREIGN KEY (referent_id) REFERENCES referent (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          referent_areas
        ADD
          CONSTRAINT FK_75CEBC6CBD0F409C FOREIGN KEY (area_id) REFERENCES referent_area (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          referent_managed_areas_tags
        ADD
          CONSTRAINT FK_8BE84DD56B99CC25 FOREIGN KEY (referent_managed_area_id) REFERENCES referent_managed_areas (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_managed_areas_tags
        ADD
          CONSTRAINT FK_8BE84DD59C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          referent_person_link
        ADD
          CONSTRAINT FK_BC75A60A25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          referent_person_link
        ADD
          CONSTRAINT FK_BC75A60A35E47E35 FOREIGN KEY (referent_id) REFERENCES referent (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_person_link
        ADD
          CONSTRAINT FK_BC75A60A810B5A42 FOREIGN KEY (
            person_organizational_chart_item_id
          ) REFERENCES organizational_chart_item (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_person_link_committee
        ADD
          CONSTRAINT FK_1C97B2A5B3E4DE86 FOREIGN KEY (referent_person_link_id) REFERENCES referent_person_link (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_person_link_committee
        ADD
          CONSTRAINT FK_1C97B2A5ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_space_access_information
        ADD
          CONSTRAINT FK_CD8FDF4825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_tags
        ADD
          CONSTRAINT FK_135D29D99F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          referent_team_member
        ADD
          CONSTRAINT FK_6C0067135E47E35 FOREIGN KEY (referent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_team_member
        ADD
          CONSTRAINT FK_6C006717597D3FE FOREIGN KEY (member_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_team_member_committee
        ADD
          CONSTRAINT FK_EC89860BED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_team_member_committee
        ADD
          CONSTRAINT FK_EC89860BFE4CA267 FOREIGN KEY (referent_team_member_id) REFERENCES referent_team_member (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_user_filter_referent_tag
        ADD
          CONSTRAINT FK_F2BB20FE9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          referent_user_filter_referent_tag
        ADD
          CONSTRAINT FK_F2BB20FEEFAB50C4 FOREIGN KEY (referent_user_filter_id) REFERENCES adherent_message_filters (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          running_mate_request_referent_tag
        ADD
          CONSTRAINT FK_53AB4FAB9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          running_mate_request_referent_tag
        ADD
          CONSTRAINT FK_53AB4FABCEDF4387 FOREIGN KEY (running_mate_request_id) REFERENCES application_request_running_mate (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          senator_area
        ADD
          CONSTRAINT FK_D229BBF7AEC89CE1 FOREIGN KEY (department_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          senatorial_candidate_areas_tags
        ADD
          CONSTRAINT FK_F83208FA9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          senatorial_candidate_areas_tags
        ADD
          CONSTRAINT FK_F83208FAA7BF84E8 FOREIGN KEY (senatorial_candidate_area_id) REFERENCES senatorial_candidate_areas (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          unregistration_referent_tag
        ADD
          CONSTRAINT FK_59B7AC414D824CA FOREIGN KEY (unregistration_id) REFERENCES unregistrations (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          unregistration_referent_tag
        ADD
          CONSTRAINT FK_59B7AC49C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_referent_tag
        ADD
          CONSTRAINT FK_DA2917429C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_referent_tag
        ADD
          CONSTRAINT FK_DA291742B8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherent_message_filters ADD referent_tag_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F949C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_28CA9F949C262DB3 ON adherent_message_filters (referent_tag_id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          managed_area_id INT DEFAULT NULL,
        ADD
          coordinator_committee_area_id INT DEFAULT NULL,
        ADD
          senator_area_id INT DEFAULT NULL,
        ADD
          managed_district_id INT UNSIGNED DEFAULT NULL,
        ADD
          senatorial_candidate_managed_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA31A912B27 FOREIGN KEY (coordinator_committee_area_id) REFERENCES coordinator_managed_areas (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA393494FA8 FOREIGN KEY (senator_area_id) REFERENCES senator_area (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3A132C3C5 FOREIGN KEY (managed_district_id) REFERENCES districts (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3DC184E71 FOREIGN KEY (managed_area_id) REFERENCES referent_managed_areas (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3FCCAF6D5 FOREIGN KEY (
            senatorial_candidate_managed_area_id
          ) REFERENCES senatorial_candidate_areas (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA31A912B27 ON adherents (coordinator_committee_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA393494FA8 ON adherents (senator_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3A132C3C5 ON adherents (managed_district_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3DC184E71 ON adherents (managed_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3FCCAF6D5 ON adherents (senatorial_candidate_managed_area_id)');
    }
}
