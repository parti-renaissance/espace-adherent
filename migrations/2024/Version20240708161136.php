<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240708161136 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94C7A72');
        $this->addSql('ALTER TABLE adherent_mandate DROP FOREIGN KEY FK_9C0C3D60AAA61A99');
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94AAA61A99');
        $this->addSql('ALTER TABLE voting_platform_election_entity DROP FOREIGN KEY FK_7AAD259FAAA61A99');
        $this->addSql('ALTER TABLE adherent_instance_quality DROP FOREIGN KEY FK_D63B17FA25F06C53');
        $this->addSql('ALTER TABLE adherent_instance_quality DROP FOREIGN KEY FK_D63B17FA9F2C3FAB');
        $this->addSql('ALTER TABLE adherent_instance_quality DROP FOREIGN KEY FK_D63B17FAA623BBD7');
        $this->addSql('ALTER TABLE adherent_instance_quality DROP FOREIGN KEY FK_D63B17FAAAA61A99');
        $this->addSql('ALTER TABLE national_council_candidacy DROP FOREIGN KEY FK_31A7A20525F06C53');
        $this->addSql('ALTER TABLE national_council_candidacy DROP FOREIGN KEY FK_31A7A205A708DAFF');
        $this->addSql('ALTER TABLE national_council_candidacy DROP FOREIGN KEY FK_31A7A205FC1537C1');
        $this->addSql('ALTER TABLE national_council_election DROP FOREIGN KEY FK_F3809347FAC7D83F');
        $this->addSql('ALTER TABLE political_committee DROP FOREIGN KEY FK_39FAEE95AAA61A99');
        $this->addSql('ALTER TABLE political_committee_feed_item DROP FOREIGN KEY FK_54369E83C7A72');
        $this->addSql('ALTER TABLE political_committee_feed_item DROP FOREIGN KEY FK_54369E83F675F31B');
        $this->addSql('ALTER TABLE political_committee_membership DROP FOREIGN KEY FK_FD85437B25F06C53');
        $this->addSql('ALTER TABLE political_committee_membership DROP FOREIGN KEY FK_FD85437BC7A72');
        $this->addSql('ALTER TABLE political_committee_quality DROP FOREIGN KEY FK_243D6D3A78632915');
        $this->addSql('ALTER TABLE territorial_council DROP FOREIGN KEY FK_B6DCA2A5B4D2A5D1');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B61FB354CD');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B6A708DAFF');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B6FC1537C1');
        $this->addSql('ALTER TABLE territorial_council_candidacy_invitation DROP FOREIGN KEY FK_DA86009A1FB354CD');
        $this->addSql('ALTER TABLE territorial_council_candidacy_invitation DROP FOREIGN KEY FK_DA86009A59B22434');
        $this->addSql('ALTER TABLE territorial_council_convocation DROP FOREIGN KEY FK_A9919BF0AAA61A99');
        $this->addSql('ALTER TABLE territorial_council_convocation DROP FOREIGN KEY FK_A9919BF0B03A8386');
        $this->addSql('ALTER TABLE territorial_council_convocation DROP FOREIGN KEY FK_A9919BF0C7A72');
        $this->addSql('ALTER TABLE territorial_council_election DROP FOREIGN KEY FK_14CBC36B8649F5F1');
        $this->addSql('ALTER TABLE territorial_council_election DROP FOREIGN KEY FK_14CBC36BAAA61A99');
        $this->addSql('ALTER TABLE territorial_council_election DROP FOREIGN KEY FK_14CBC36BFAC7D83F');
        $this->addSql('ALTER TABLE territorial_council_election_poll_choice DROP FOREIGN KEY FK_63EBCF6B8649F5F1');
        $this->addSql('ALTER TABLE territorial_council_election_poll_vote DROP FOREIGN KEY FK_BCDA0C151FB354CD');
        $this->addSql('ALTER TABLE territorial_council_election_poll_vote DROP FOREIGN KEY FK_BCDA0C15998666D1');
        $this->addSql('ALTER TABLE territorial_council_feed_item DROP FOREIGN KEY FK_45241D62AAA61A99');
        $this->addSql('ALTER TABLE territorial_council_feed_item DROP FOREIGN KEY FK_45241D62F675F31B');
        $this->addSql('ALTER TABLE territorial_council_membership DROP FOREIGN KEY FK_2A99831625F06C53');
        $this->addSql('ALTER TABLE territorial_council_membership DROP FOREIGN KEY FK_2A998316AAA61A99');
        $this->addSql('ALTER TABLE territorial_council_membership_log DROP FOREIGN KEY FK_2F6D242025F06C53');
        $this->addSql('ALTER TABLE territorial_council_official_report DROP FOREIGN KEY FK_8D80D385896DBBDE');
        $this->addSql('ALTER TABLE territorial_council_official_report DROP FOREIGN KEY FK_8D80D385B03A8386');
        $this->addSql('ALTER TABLE territorial_council_official_report DROP FOREIGN KEY FK_8D80D385C7A72');
        $this->addSql('ALTER TABLE territorial_council_official_report DROP FOREIGN KEY FK_8D80D385F675F31B');
        $this->addSql('ALTER TABLE territorial_council_official_report_document DROP FOREIGN KEY FK_78C1161D4BD2A4C0');
        $this->addSql('ALTER TABLE territorial_council_official_report_document DROP FOREIGN KEY FK_78C1161DB03A8386');
        $this->addSql('ALTER TABLE territorial_council_quality DROP FOREIGN KEY FK_C018E022E797FAB0');
        $this->addSql('ALTER TABLE territorial_council_referent_tag DROP FOREIGN KEY FK_78DBEB909C262DB3');
        $this->addSql('ALTER TABLE territorial_council_referent_tag DROP FOREIGN KEY FK_78DBEB90AAA61A99');
        $this->addSql('ALTER TABLE territorial_council_zone DROP FOREIGN KEY FK_9467B41E9F2C3FAB');
        $this->addSql('ALTER TABLE territorial_council_zone DROP FOREIGN KEY FK_9467B41EAAA61A99');
        $this->addSql('DROP TABLE adherent_instance_quality');
        $this->addSql('DROP TABLE algolia_candidature');
        $this->addSql('DROP TABLE instance_quality');
        $this->addSql('DROP TABLE national_council_candidacies_group');
        $this->addSql('DROP TABLE national_council_candidacy');
        $this->addSql('DROP TABLE national_council_election');
        $this->addSql('DROP TABLE political_committee');
        $this->addSql('DROP TABLE political_committee_feed_item');
        $this->addSql('DROP TABLE political_committee_membership');
        $this->addSql('DROP TABLE political_committee_quality');
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
        $this->addSql('DROP INDEX IDX_9C0C3D60AAA61A99 ON adherent_mandate');
        $this->addSql('ALTER TABLE adherent_mandate DROP territorial_council_id, DROP is_additionally_elected');
        $this->addSql('DROP INDEX IDX_28CA9F94AAA61A99 ON adherent_message_filters');
        $this->addSql('DROP INDEX IDX_28CA9F94C7A72 ON adherent_message_filters');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        DROP
          territorial_council_id,
        DROP
          political_committee_id,
        DROP
          qualities');
        $this->addSql('DROP INDEX IDX_7AAD259FAAA61A99 ON voting_platform_election_entity');
        $this->addSql('ALTER TABLE voting_platform_election_entity DROP territorial_council_id');
    }

    public function down(Schema $schema): void
    {
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
          UNIQUE INDEX UNIQ_D63B17FAD17F50A6 (uuid),
          INDEX IDX_D63B17FA25F06C53 (adherent_id),
          INDEX IDX_D63B17FA9F2C3FAB (zone_id),
          INDEX IDX_D63B17FAA623BBD7 (instance_quality_id),
          INDEX IDX_D63B17FAAAA61A99 (territorial_council_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE algolia_candidature (
          id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE instance_quality (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          scopes LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          custom TINYINT(1) DEFAULT 1 NOT NULL,
          UNIQUE INDEX UNIQ_BB26C6D377153098 (code),
          UNIQUE INDEX UNIQ_BB26C6D3D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE national_council_candidacies_group (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE national_council_candidacy (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          election_id INT UNSIGNED NOT NULL,
          candidacies_group_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          gender VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          biography LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          quality VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          STATUS VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          faith_statement LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_public_faith_statement TINYINT(1) DEFAULT 0 NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_31A7A205D17F50A6 (uuid),
          INDEX IDX_31A7A20525F06C53 (adherent_id),
          INDEX IDX_31A7A205A708DAFF (election_id),
          INDEX IDX_31A7A205FC1537C1 (candidacies_group_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE national_council_election (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          designation_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_F3809347D17F50A6 (uuid),
          INDEX IDX_F3809347FAC7D83F (designation_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE political_committee (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          territorial_council_id INT UNSIGNED NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          is_active TINYINT(1) DEFAULT 0 NOT NULL,
          UNIQUE INDEX UNIQ_39FAEE955E237E06 (name),
          UNIQUE INDEX UNIQ_39FAEE95AAA61A99 (territorial_council_id),
          UNIQUE INDEX UNIQ_39FAEE95D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE political_committee_feed_item (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          political_committee_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          is_locked TINYINT(1) DEFAULT 0 NOT NULL,
          UNIQUE INDEX UNIQ_54369E83D17F50A6 (uuid),
          INDEX IDX_54369E83C7A72 (political_committee_id),
          INDEX IDX_54369E83F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE political_committee_membership (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          political_committee_id INT UNSIGNED NOT NULL,
          joined_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          is_additional TINYINT(1) DEFAULT 0 NOT NULL,
          UNIQUE INDEX UNIQ_FD85437B25F06C53 (adherent_id),
          UNIQUE INDEX UNIQ_FD85437BD17F50A6 (uuid),
          INDEX IDX_FD85437BC7A72 (political_committee_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE political_committee_quality (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          political_committee_membership_id INT UNSIGNED NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          joined_at DATETIME NOT NULL,
          INDEX IDX_243D6D3A78632915 (
            political_committee_membership_id
          ),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE territorial_council (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          current_designation_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          codes VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          mailchimp_id INT DEFAULT NULL,
          is_active TINYINT(1) DEFAULT 1 NOT NULL,
          UNIQUE INDEX UNIQ_B6DCA2A5E5ADC14D (codes),
          UNIQUE INDEX UNIQ_B6DCA2A55E237E06 (name),
          UNIQUE INDEX UNIQ_B6DCA2A5D17F50A6 (uuid),
          INDEX IDX_B6DCA2A5B4D2A5D1 (current_designation_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE territorial_council_candidacies_group (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE territorial_council_candidacy (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          election_id INT UNSIGNED NOT NULL,
          membership_id INT UNSIGNED NOT NULL,
          candidacies_group_id INT UNSIGNED DEFAULT NULL,
          gender VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          biography LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          faith_statement LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_public_faith_statement TINYINT(1) DEFAULT 0 NOT NULL,
          quality VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          STATUS VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_39885B6D17F50A6 (uuid),
          INDEX IDX_39885B61FB354CD (membership_id),
          INDEX IDX_39885B6A708DAFF (election_id),
          INDEX IDX_39885B6FC1537C1 (candidacies_group_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE territorial_council_candidacy_invitation (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          membership_id INT UNSIGNED NOT NULL,
          candidacy_id INT UNSIGNED NOT NULL,
          STATUS VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          accepted_at DATETIME DEFAULT NULL,
          declined_at DATETIME DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_DA86009AD17F50A6 (uuid),
          INDEX IDX_DA86009A1FB354CD (membership_id),
          INDEX IDX_DA86009A59B22434 (candidacy_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE territorial_council_convocation (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          territorial_council_id INT UNSIGNED DEFAULT NULL,
          political_committee_id INT UNSIGNED DEFAULT NULL,
          created_by_id INT UNSIGNED DEFAULT NULL,
          meeting_start_date DATETIME NOT NULL,
          meeting_end_date DATETIME NOT NULL,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          MODE VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
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
          address_additional_address VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_A9919BF0D17F50A6 (uuid),
          INDEX IDX_A9919BF0AAA61A99 (territorial_council_id),
          INDEX IDX_A9919BF0B03A8386 (created_by_id),
          INDEX IDX_A9919BF0C7A72 (political_committee_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
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
          address_additional_address VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_14CBC36BD17F50A6 (uuid),
          UNIQUE INDEX UNIQ_14CBC36B8649F5F1 (election_poll_id),
          INDEX IDX_14CBC36BAAA61A99 (territorial_council_id),
          INDEX IDX_14CBC36BFAC7D83F (designation_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE territorial_council_election_poll (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          gender VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_E0D7231ED17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE territorial_council_election_poll_choice (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          election_poll_id INT UNSIGNED NOT NULL,
          value VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_63EBCF6BD17F50A6 (uuid),
          INDEX IDX_63EBCF6B8649F5F1 (election_poll_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE territorial_council_election_poll_vote (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          choice_id INT UNSIGNED DEFAULT NULL,
          membership_id INT UNSIGNED NOT NULL,
          created_at DATETIME NOT NULL,
          INDEX IDX_BCDA0C151FB354CD (membership_id),
          INDEX IDX_BCDA0C15998666D1 (choice_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE territorial_council_feed_item (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          territorial_council_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          is_locked TINYINT(1) DEFAULT 0 NOT NULL,
          UNIQUE INDEX UNIQ_45241D62D17F50A6 (uuid),
          INDEX IDX_45241D62AAA61A99 (territorial_council_id),
          INDEX IDX_45241D62F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE territorial_council_membership (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          territorial_council_id INT UNSIGNED NOT NULL,
          joined_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_2A99831625F06C53 (adherent_id),
          UNIQUE INDEX UNIQ_2A998316D17F50A6 (uuid),
          INDEX IDX_2A998316AAA61A99 (territorial_council_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE territorial_council_membership_log (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          TYPE VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description VARCHAR(500) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          quality_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          actual_territorial_council VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          actual_quality_names LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          found_territorial_councils LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          created_at DATETIME NOT NULL,
          is_resolved TINYINT(1) DEFAULT 0 NOT NULL,
          INDEX IDX_2F6D242025F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
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
          UNIQUE INDEX UNIQ_8D80D385D17F50A6 (uuid),
          INDEX IDX_8D80D385896DBBDE (updated_by_id),
          INDEX IDX_8D80D385B03A8386 (created_by_id),
          INDEX IDX_8D80D385C7A72 (political_committee_id),
          INDEX IDX_8D80D385F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
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
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
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
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE territorial_council_referent_tag (
          territorial_council_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_78DBEB909C262DB3 (referent_tag_id),
          INDEX IDX_78DBEB90AAA61A99 (territorial_council_id),
          PRIMARY KEY(
            territorial_council_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE territorial_council_zone (
          territorial_council_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_9467B41E9F2C3FAB (zone_id),
          INDEX IDX_9467B41EAAA61A99 (territorial_council_id),
          PRIMARY KEY(territorial_council_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          adherent_instance_quality
        ADD
          CONSTRAINT FK_D63B17FA25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_instance_quality
        ADD
          CONSTRAINT FK_D63B17FA9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          adherent_instance_quality
        ADD
          CONSTRAINT FK_D63B17FAA623BBD7 FOREIGN KEY (instance_quality_id) REFERENCES instance_quality (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          adherent_instance_quality
        ADD
          CONSTRAINT FK_D63B17FAAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          national_council_candidacy
        ADD
          CONSTRAINT FK_31A7A20525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          national_council_candidacy
        ADD
          CONSTRAINT FK_31A7A205A708DAFF FOREIGN KEY (election_id) REFERENCES national_council_election (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          national_council_candidacy
        ADD
          CONSTRAINT FK_31A7A205FC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES national_council_candidacies_group (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          national_council_election
        ADD
          CONSTRAINT FK_F3809347FAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          political_committee
        ADD
          CONSTRAINT FK_39FAEE95AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          political_committee_feed_item
        ADD
          CONSTRAINT FK_54369E83C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          political_committee_feed_item
        ADD
          CONSTRAINT FK_54369E83F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          political_committee_membership
        ADD
          CONSTRAINT FK_FD85437B25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          political_committee_membership
        ADD
          CONSTRAINT FK_FD85437BC7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          political_committee_quality
        ADD
          CONSTRAINT FK_243D6D3A78632915 FOREIGN KEY (
            political_committee_membership_id
          ) REFERENCES political_committee_membership (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council
        ADD
          CONSTRAINT FK_B6DCA2A5B4D2A5D1 FOREIGN KEY (current_designation_id) REFERENCES designation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy
        ADD
          CONSTRAINT FK_39885B61FB354CD FOREIGN KEY (membership_id) REFERENCES territorial_council_membership (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy
        ADD
          CONSTRAINT FK_39885B6A708DAFF FOREIGN KEY (election_id) REFERENCES territorial_council_election (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy
        ADD
          CONSTRAINT FK_39885B6FC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES territorial_council_candidacies_group (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy_invitation
        ADD
          CONSTRAINT FK_DA86009A1FB354CD FOREIGN KEY (membership_id) REFERENCES territorial_council_membership (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy_invitation
        ADD
          CONSTRAINT FK_DA86009A59B22434 FOREIGN KEY (candidacy_id) REFERENCES territorial_council_candidacy (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_convocation
        ADD
          CONSTRAINT FK_A9919BF0AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          territorial_council_convocation
        ADD
          CONSTRAINT FK_A9919BF0B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          territorial_council_convocation
        ADD
          CONSTRAINT FK_A9919BF0C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          territorial_council_election
        ADD
          CONSTRAINT FK_14CBC36B8649F5F1 FOREIGN KEY (election_poll_id) REFERENCES territorial_council_election_poll (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_election
        ADD
          CONSTRAINT FK_14CBC36BAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          territorial_council_election
        ADD
          CONSTRAINT FK_14CBC36BFAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          territorial_council_election_poll_choice
        ADD
          CONSTRAINT FK_63EBCF6B8649F5F1 FOREIGN KEY (election_poll_id) REFERENCES territorial_council_election_poll (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_election_poll_vote
        ADD
          CONSTRAINT FK_BCDA0C151FB354CD FOREIGN KEY (membership_id) REFERENCES territorial_council_membership (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_election_poll_vote
        ADD
          CONSTRAINT FK_BCDA0C15998666D1 FOREIGN KEY (choice_id) REFERENCES territorial_council_election_poll_choice (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          territorial_council_feed_item
        ADD
          CONSTRAINT FK_45241D62AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_feed_item
        ADD
          CONSTRAINT FK_45241D62F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          territorial_council_membership
        ADD
          CONSTRAINT FK_2A99831625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_membership
        ADD
          CONSTRAINT FK_2A998316AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_membership_log
        ADD
          CONSTRAINT FK_2F6D242025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_official_report
        ADD
          CONSTRAINT FK_8D80D385896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          territorial_council_official_report
        ADD
          CONSTRAINT FK_8D80D385B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          territorial_council_official_report
        ADD
          CONSTRAINT FK_8D80D385C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_official_report
        ADD
          CONSTRAINT FK_8D80D385F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          territorial_council_official_report_document
        ADD
          CONSTRAINT FK_78C1161D4BD2A4C0 FOREIGN KEY (report_id) REFERENCES territorial_council_official_report (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          territorial_council_official_report_document
        ADD
          CONSTRAINT FK_78C1161DB03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          territorial_council_quality
        ADD
          CONSTRAINT FK_C018E022E797FAB0 FOREIGN KEY (
            territorial_council_membership_id
          ) REFERENCES territorial_council_membership (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_referent_tag
        ADD
          CONSTRAINT FK_78DBEB909C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_referent_tag
        ADD
          CONSTRAINT FK_78DBEB90AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_zone
        ADD
          CONSTRAINT FK_9467B41E9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_zone
        ADD
          CONSTRAINT FK_9467B41EAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_mandate
        ADD
          territorial_council_id INT UNSIGNED DEFAULT NULL,
        ADD
          is_additionally_elected TINYINT(1) DEFAULT 0');
        $this->addSql('ALTER TABLE
          adherent_mandate
        ADD
          CONSTRAINT FK_9C0C3D60AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9C0C3D60AAA61A99 ON adherent_mandate (territorial_council_id)');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          territorial_council_id INT UNSIGNED DEFAULT NULL,
        ADD
          political_committee_id INT UNSIGNED DEFAULT NULL,
        ADD
          qualities LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F94AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F94C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_28CA9F94AAA61A99 ON adherent_message_filters (territorial_council_id)');
        $this->addSql('CREATE INDEX IDX_28CA9F94C7A72 ON adherent_message_filters (political_committee_id)');
        $this->addSql('ALTER TABLE
          voting_platform_election_entity
        ADD
          territorial_council_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          voting_platform_election_entity
        ADD
          CONSTRAINT FK_7AAD259FAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_7AAD259FAAA61A99 ON voting_platform_election_entity (territorial_council_id)');
    }
}
