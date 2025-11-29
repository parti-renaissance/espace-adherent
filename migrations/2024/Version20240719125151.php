<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240719125151 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3E1B55931');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3E4A5D7A5');
        $this->addSql('ALTER TABLE assessor_requests DROP FOREIGN KEY FK_26BC800F3F90B30');
        $this->addSql('ALTER TABLE assessor_requests_vote_place_wishes DROP FOREIGN KEY FK_1517FC131BD1903D');
        $this->addSql('ALTER TABLE assessor_requests_vote_place_wishes DROP FOREIGN KEY FK_1517FC13F3F90B30');
        $this->addSql('ALTER TABLE assessor_role_association DROP FOREIGN KEY FK_B93395C2F3F90B30');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1354DEDE5');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D15EC54712');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1781FEED9');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D18BAC62AF');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1B29FABBC');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1B86B270B');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1E449D110');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1E4A014FA');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1EBF42685');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1F543170A');
        $this->addSql('ALTER TABLE election_city_contact DROP FOREIGN KEY FK_D04AFB68BAC62AF');
        $this->addSql('ALTER TABLE election_city_partner DROP FOREIGN KEY FK_704D77988BAC62AF');
        $this->addSql('ALTER TABLE election_vote_place DROP FOREIGN KEY FK_880DE20D9F2C3FAB');
        $this->addSql('ALTER TABLE list_total_result DROP FOREIGN KEY FK_A19B071E3DAE168B');
        $this->addSql('ALTER TABLE list_total_result DROP FOREIGN KEY FK_A19B071E45EB7186');
        $this->addSql('ALTER TABLE ministry_list_total_result DROP FOREIGN KEY FK_99D1332580711B75');
        $this->addSql('ALTER TABLE ministry_vote_result DROP FOREIGN KEY FK_B9F11DAE896DBBDE');
        $this->addSql('ALTER TABLE ministry_vote_result DROP FOREIGN KEY FK_B9F11DAE8BAC62AF');
        $this->addSql('ALTER TABLE ministry_vote_result DROP FOREIGN KEY FK_B9F11DAEB03A8386');
        $this->addSql('ALTER TABLE ministry_vote_result DROP FOREIGN KEY FK_B9F11DAEFCBF5E32');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349896DBBDE');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB3498BAC62AF');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349B03A8386');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349F3F90B30');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349FCBF5E32');
        $this->addSql('ALTER TABLE vote_result_list DROP FOREIGN KEY FK_677ED502DB567AF4');
        $this->addSql('ALTER TABLE vote_result_list_collection DROP FOREIGN KEY FK_9C1DD9638BAC62AF');
        $this->addSql('ALTER TABLE vote_result_list_collection DROP FOREIGN KEY FK_9C1DD963FCBF5E32');
        $this->addSql('DROP TABLE assessor_managed_areas');
        $this->addSql('DROP TABLE assessor_requests');
        $this->addSql('DROP TABLE assessor_requests_vote_place_wishes');
        $this->addSql('DROP TABLE assessor_role_association');
        $this->addSql('DROP TABLE election_city_candidate');
        $this->addSql('DROP TABLE election_city_card');
        $this->addSql('DROP TABLE election_city_contact');
        $this->addSql('DROP TABLE election_city_manager');
        $this->addSql('DROP TABLE election_city_partner');
        $this->addSql('DROP TABLE election_city_prevision');
        $this->addSql('DROP TABLE election_vote_place');
        $this->addSql('DROP TABLE list_total_result');
        $this->addSql('DROP TABLE ministry_list_total_result');
        $this->addSql('DROP TABLE ministry_vote_result');
        $this->addSql('DROP TABLE vote_result');
        $this->addSql('DROP TABLE vote_result_list');
        $this->addSql('DROP TABLE vote_result_list_collection');
        $this->addSql('DROP INDEX UNIQ_562C7DA3E4A5D7A5 ON adherents');
        $this->addSql('DROP INDEX UNIQ_562C7DA3E1B55931 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP assessor_managed_area_id, DROP assessor_role_id');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        CHANGE
          political_affiliation political_affiliation VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE assessor_managed_areas (
          id INT AUTO_INCREMENT NOT NULL,
          codes LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE assessor_requests (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          vote_place_id INT UNSIGNED DEFAULT NULL,
          gender VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          birthdate DATE NOT NULL,
          birth_city VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          address VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          vote_city VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          office_number VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
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
          reachable TINYINT(1) DEFAULT 0 NOT NULL,
          voter_number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          election_rounds LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT \'FR\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_26BC800D17F50A6 (uuid),
          INDEX IDX_26BC800F3F90B30 (vote_place_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE assessor_requests_vote_place_wishes (
          assessor_request_id INT UNSIGNED NOT NULL,
          vote_place_id INT UNSIGNED NOT NULL,
          INDEX IDX_1517FC131BD1903D (assessor_request_id),
          INDEX IDX_1517FC13F3F90B30 (vote_place_id),
          PRIMARY KEY(
            assessor_request_id, vote_place_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE assessor_role_association (
          id INT AUTO_INCREMENT NOT NULL,
          vote_place_id INT UNSIGNED DEFAULT NULL,
          UNIQUE INDEX UNIQ_B93395C2F3F90B30 (vote_place_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE election_city_candidate (
          id INT AUTO_INCREMENT NOT NULL,
          gender VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          political_scheme VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          alliances VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          agreement TINYINT(1) DEFAULT 0 NOT NULL,
          eligible_advisers_count INT DEFAULT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          profile VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          investiture_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
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
          risk TINYINT(1) DEFAULT 0 NOT NULL,
          UNIQUE INDEX UNIQ_EB01E8D18BAC62AF (city_id),
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
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE election_city_contact (
          id INT AUTO_INCREMENT NOT NULL,
          city_id INT NOT NULL,
          `function` VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          caller VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          done TINYINT(1) DEFAULT 0 NOT NULL,
          COMMENT LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_D04AFB68BAC62AF (city_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE election_city_manager (
          id INT AUTO_INCREMENT NOT NULL,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE election_city_partner (
          id INT AUTO_INCREMENT NOT NULL,
          city_id INT NOT NULL,
          label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          consensus VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_704D77988BAC62AF (city_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE election_city_prevision (
          id INT AUTO_INCREMENT NOT NULL,
          strategy VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          alliances VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          allies VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          validated_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE election_vote_place (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          alias VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          code VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          nb_addresses INT UNSIGNED DEFAULT 0 NOT NULL,
          nb_voters INT UNSIGNED DEFAULT 0 NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          address_address VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_postal_code VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_insee VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_region VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_geocodable_hash VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_additional_address VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_880DE20DD17F50A6 (uuid),
          UNIQUE INDEX UNIQ_880DE20D77153098 (code),
          INDEX IDX_880DE20D9F2C3FAB (zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE list_total_result (
          id INT AUTO_INCREMENT NOT NULL,
          list_id INT DEFAULT NULL,
          vote_result_id INT NOT NULL,
          total INT DEFAULT 0 NOT NULL,
          INDEX IDX_A19B071E3DAE168B (list_id),
          INDEX IDX_A19B071E45EB7186 (vote_result_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
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
          outgoing_mayor TINYINT(1) DEFAULT 0 NOT NULL,
          INDEX IDX_99D1332580711B75 (ministry_vote_result_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
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
          UNIQUE INDEX ministry_vote_result_city_round_unique (city_id, election_round_id),
          INDEX IDX_B9F11DAE896DBBDE (updated_by_id),
          INDEX IDX_B9F11DAE8BAC62AF (city_id),
          INDEX IDX_B9F11DAEB03A8386 (created_by_id),
          INDEX IDX_B9F11DAEFCBF5E32 (election_round_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE vote_result (
          id INT AUTO_INCREMENT NOT NULL,
          vote_place_id INT UNSIGNED DEFAULT NULL,
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
          TYPE VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX city_vote_result_city_round_unique (city_id, election_round_id),
          UNIQUE INDEX vote_place_result_city_round_unique (
            vote_place_id, election_round_id
          ),
          INDEX IDX_1F8DB349896DBBDE (updated_by_id),
          INDEX IDX_1F8DB3498BAC62AF (city_id),
          INDEX IDX_1F8DB349B03A8386 (created_by_id),
          INDEX IDX_1F8DB349F3F90B30 (vote_place_id),
          INDEX IDX_1F8DB349FCBF5E32 (election_round_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
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
          outgoing_mayor TINYINT(1) DEFAULT 0 NOT NULL,
          INDEX IDX_677ED502DB567AF4 (list_collection_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE vote_result_list_collection (
          id INT AUTO_INCREMENT NOT NULL,
          city_id INT UNSIGNED DEFAULT NULL,
          election_round_id INT DEFAULT NULL,
          INDEX IDX_9C1DD9638BAC62AF (city_id),
          INDEX IDX_9C1DD963FCBF5E32 (election_round_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          assessor_requests
        ADD
          CONSTRAINT FK_26BC800F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES election_vote_place (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          assessor_requests_vote_place_wishes
        ADD
          CONSTRAINT FK_1517FC131BD1903D FOREIGN KEY (assessor_request_id) REFERENCES assessor_requests (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          assessor_requests_vote_place_wishes
        ADD
          CONSTRAINT FK_1517FC13F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES election_vote_place (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          assessor_role_association
        ADD
          CONSTRAINT FK_B93395C2F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES election_vote_place (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1354DEDE5 FOREIGN KEY (candidate_option_prevision_id) REFERENCES election_city_prevision (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D15EC54712 FOREIGN KEY (preparation_prevision_id) REFERENCES election_city_prevision (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1781FEED9 FOREIGN KEY (task_force_manager_id) REFERENCES election_city_manager (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D18BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1B29FABBC FOREIGN KEY (headquarters_manager_id) REFERENCES election_city_manager (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1B86B270B FOREIGN KEY (national_prevision_id) REFERENCES election_city_prevision (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1E449D110 FOREIGN KEY (first_candidate_id) REFERENCES election_city_candidate (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1E4A014FA FOREIGN KEY (politic_manager_id) REFERENCES election_city_manager (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1EBF42685 FOREIGN KEY (candidate_prevision_id) REFERENCES election_city_prevision (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_card
        ADD
          CONSTRAINT FK_EB01E8D1F543170A FOREIGN KEY (third_option_prevision_id) REFERENCES election_city_prevision (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_contact
        ADD
          CONSTRAINT FK_D04AFB68BAC62AF FOREIGN KEY (city_id) REFERENCES election_city_card (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_city_partner
        ADD
          CONSTRAINT FK_704D77988BAC62AF FOREIGN KEY (city_id) REFERENCES election_city_card (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          election_vote_place
        ADD
          CONSTRAINT FK_880DE20D9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          list_total_result
        ADD
          CONSTRAINT FK_A19B071E3DAE168B FOREIGN KEY (list_id) REFERENCES vote_result_list (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          list_total_result
        ADD
          CONSTRAINT FK_A19B071E45EB7186 FOREIGN KEY (vote_result_id) REFERENCES vote_result (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ministry_list_total_result
        ADD
          CONSTRAINT FK_99D1332580711B75 FOREIGN KEY (ministry_vote_result_id) REFERENCES ministry_vote_result (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          ministry_vote_result
        ADD
          CONSTRAINT FK_B9F11DAE896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          ministry_vote_result
        ADD
          CONSTRAINT FK_B9F11DAE8BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          ministry_vote_result
        ADD
          CONSTRAINT FK_B9F11DAEB03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          ministry_vote_result
        ADD
          CONSTRAINT FK_B9F11DAEFCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          vote_result
        ADD
          CONSTRAINT FK_1F8DB349896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          vote_result
        ADD
          CONSTRAINT FK_1F8DB3498BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          vote_result
        ADD
          CONSTRAINT FK_1F8DB349B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          vote_result
        ADD
          CONSTRAINT FK_1F8DB349F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES election_vote_place (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          vote_result
        ADD
          CONSTRAINT FK_1F8DB349FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          vote_result_list
        ADD
          CONSTRAINT FK_677ED502DB567AF4 FOREIGN KEY (list_collection_id) REFERENCES vote_result_list_collection (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          vote_result_list_collection
        ADD
          CONSTRAINT FK_9C1DD9638BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          vote_result_list_collection
        ADD
          CONSTRAINT FK_9C1DD963FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          assessor_managed_area_id INT DEFAULT NULL,
        ADD
          assessor_role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3E1B55931 FOREIGN KEY (assessor_managed_area_id) REFERENCES assessor_managed_areas (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3E4A5D7A5 FOREIGN KEY (assessor_role_id) REFERENCES assessor_role_association (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3E4A5D7A5 ON adherents (assessor_role_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3E1B55931 ON adherents (assessor_managed_area_id)');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        CHANGE
          political_affiliation political_affiliation VARCHAR(10) NOT NULL');
    }
}
