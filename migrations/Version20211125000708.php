<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211125000708 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pap_building_block_statistics (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          building_block_id INT UNSIGNED NOT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          status VARCHAR(25) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_8B79BF6032618357 (building_block_id),
          INDEX IDX_8B79BF60F639F774 (campaign_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pap_building_statistics (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          building_id INT UNSIGNED NOT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          last_passage_done_by_id INT UNSIGNED DEFAULT NULL,
          status VARCHAR(25) NOT NULL,
          last_passage DATETIME DEFAULT NULL,
          nb_voters SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          nb_doors SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          nb_surveys SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_B6FB4E7B4D2A7E12 (building_id),
          INDEX IDX_B6FB4E7BF639F774 (campaign_id),
          INDEX IDX_B6FB4E7BDCDF6621 (last_passage_done_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
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
        $this->addSql('DROP TABLE pap_bulding_block_statistics');
        $this->addSql('DROP TABLE pap_bulding_statistics');
        $this->addSql('ALTER TABLE
          pap_building
        ADD
          current_campaign_id INT UNSIGNED DEFAULT NULL,
        ADD
          type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_building
        ADD
          CONSTRAINT FK_112ABBE148ED5CAD FOREIGN KEY (current_campaign_id) REFERENCES pap_campaign (id)');
        $this->addSql('CREATE INDEX IDX_112ABBE148ED5CAD ON pap_building (current_campaign_id)');
        $this->addSql('ALTER TABLE pap_floor CHANGE number number SMALLINT UNSIGNED NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pap_bulding_block_statistics (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          building_block_id INT UNSIGNED NOT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          status VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_DB09D48732618357 (building_block_id),
          INDEX IDX_DB09D487F639F774 (campaign_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE pap_bulding_statistics (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          building_id INT UNSIGNED NOT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          last_passage_done_by_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          status VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_passage DATETIME DEFAULT NULL,
          nb_voters SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          nb_doors SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          nb_surveys SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_2984AD934D2A7E12 (building_id),
          INDEX IDX_2984AD93DCDF6621 (last_passage_done_by_id),
          INDEX IDX_2984AD93F639F774 (campaign_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          pap_bulding_block_statistics
        ADD
          CONSTRAINT FK_DB09D48732618357 FOREIGN KEY (building_block_id) REFERENCES pap_building_block (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_bulding_block_statistics
        ADD
          CONSTRAINT FK_DB09D487F639F774 FOREIGN KEY (campaign_id) REFERENCES pap_campaign (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_bulding_statistics
        ADD
          CONSTRAINT FK_2984AD934D2A7E12 FOREIGN KEY (building_id) REFERENCES pap_building (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_bulding_statistics
        ADD
          CONSTRAINT FK_2984AD93DCDF6621 FOREIGN KEY (last_passage_done_by_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_bulding_statistics
        ADD
          CONSTRAINT FK_2984AD93F639F774 FOREIGN KEY (campaign_id) REFERENCES pap_campaign (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('DROP TABLE pap_building_block_statistics');
        $this->addSql('DROP TABLE pap_building_statistics');
        $this->addSql('ALTER TABLE pap_building DROP FOREIGN KEY FK_112ABBE148ED5CAD');
        $this->addSql('DROP INDEX IDX_112ABBE148ED5CAD ON pap_building');
        $this->addSql('ALTER TABLE pap_building DROP current_campaign_id, DROP type');
        $this->addSql('ALTER TABLE
          pap_floor
        CHANGE
          number number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
