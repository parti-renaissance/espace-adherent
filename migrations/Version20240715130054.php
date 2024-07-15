<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240715130054 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_proxies_to_election_rounds DROP FOREIGN KEY FK_D075F5A9E15E419B');
        $this->addSql('ALTER TABLE procuration_proxies_to_election_rounds DROP FOREIGN KEY FK_D075F5A9FCBF5E32');
        $this->addSql('ALTER TABLE procuration_proxy_zone DROP FOREIGN KEY FK_5AE815189F2C3FAB');
        $this->addSql('ALTER TABLE procuration_proxy_zone DROP FOREIGN KEY FK_5AE81518E15E419B');
        $this->addSql('ALTER TABLE procuration_requests DROP FOREIGN KEY FK_9769FD842F1B6663');
        $this->addSql('ALTER TABLE procuration_requests DROP FOREIGN KEY FK_9769FD84888FDEEE');
        $this->addSql('ALTER TABLE procuration_requests_to_election_rounds DROP FOREIGN KEY FK_A47BBD53128D9C53');
        $this->addSql('ALTER TABLE procuration_requests_to_election_rounds DROP FOREIGN KEY FK_A47BBD53FCBF5E32');
        $this->addSql('DROP TABLE procuration_proxies');
        $this->addSql('DROP TABLE procuration_proxies_to_election_rounds');
        $this->addSql('DROP TABLE procuration_proxy_zone');
        $this->addSql('DROP TABLE procuration_requests');
        $this->addSql('DROP TABLE procuration_requests_to_election_rounds');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE procuration_proxies (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          gender VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          first_names VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
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
          state VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          reachable TINYINT(1) DEFAULT 0 NOT NULL,
          voter_number VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          backup_other_vote_cities VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          disabled_reason VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          reminded_at DATETIME DEFAULT NULL,
          UNIQUE INDEX UNIQ_9B5E777AD17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE procuration_proxies_to_election_rounds (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          procuration_proxy_id INT UNSIGNED NOT NULL,
          election_round_id INT NOT NULL,
          french_request_available TINYINT(1) DEFAULT 1 NOT NULL,
          foreign_request_available TINYINT(1) DEFAULT 1 NOT NULL,
          INDEX IDX_D075F5A9E15E419B (procuration_proxy_id),
          INDEX IDX_D075F5A9FCBF5E32 (election_round_id),
          UNIQUE INDEX procuration_proxy_election_round_unique (
            procuration_proxy_id, election_round_id
          ),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE procuration_proxy_zone (
          procuration_proxy_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_5AE815189F2C3FAB (zone_id),
          INDEX IDX_5AE81518E15E419B (procuration_proxy_id),
          PRIMARY KEY(procuration_proxy_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE procuration_requests (
          id INT AUTO_INCREMENT NOT NULL,
          procuration_request_found_by_id INT UNSIGNED DEFAULT NULL,
          found_proxy_id INT UNSIGNED DEFAULT NULL,
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
          processed TINYINT(1) NOT NULL,
          processed_at DATETIME DEFAULT NULL,
          request_from_france TINYINT(1) DEFAULT 1 NOT NULL,
          state VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          reachable TINYINT(1) DEFAULT 0 NOT NULL,
          enabled TINYINT(1) DEFAULT 0 NOT NULL,
          disabled_reason VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          reminded_at DATETIME DEFAULT NULL,
          voter_number VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_9769FD842F1B6663 (found_proxy_id),
          INDEX IDX_9769FD84888FDEEE (
            procuration_request_found_by_id
          ),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE procuration_requests_to_election_rounds (
          procuration_request_id INT NOT NULL,
          election_round_id INT NOT NULL,
          INDEX IDX_A47BBD53128D9C53 (procuration_request_id),
          INDEX IDX_A47BBD53FCBF5E32 (election_round_id),
          PRIMARY KEY(
            procuration_request_id, election_round_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          procuration_proxies_to_election_rounds
        ADD
          CONSTRAINT FK_D075F5A9E15E419B FOREIGN KEY (procuration_proxy_id) REFERENCES procuration_proxies (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_proxies_to_election_rounds
        ADD
          CONSTRAINT FK_D075F5A9FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_proxy_zone
        ADD
          CONSTRAINT FK_5AE815189F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_proxy_zone
        ADD
          CONSTRAINT FK_5AE81518E15E419B FOREIGN KEY (procuration_proxy_id) REFERENCES procuration_proxies (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_requests
        ADD
          CONSTRAINT FK_9769FD842F1B6663 FOREIGN KEY (found_proxy_id) REFERENCES procuration_proxies (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          procuration_requests
        ADD
          CONSTRAINT FK_9769FD84888FDEEE FOREIGN KEY (
            procuration_request_found_by_id
          ) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_requests_to_election_rounds
        ADD
          CONSTRAINT FK_A47BBD53128D9C53 FOREIGN KEY (procuration_request_id) REFERENCES procuration_requests (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_requests_to_election_rounds
        ADD
          CONSTRAINT FK_A47BBD53FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
