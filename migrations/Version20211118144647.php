<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211118144647 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pap_bulding_block_statistics (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          status VARCHAR(25) NOT NULL,
          building_block_id INT UNSIGNED NOT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_DB09D48732618357 (building_block_id),
          INDEX IDX_DB09D487F639F774 (campaign_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pap_bulding_statistics (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          status VARCHAR(25) NOT NULL,
          building_id INT UNSIGNED NOT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          last_passage_done_by_id INT UNSIGNED DEFAULT NULL,
          last_passage DATETIME DEFAULT NULL,
          nb_voters SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          nb_doors SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          nb_surveys SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_2984AD934D2A7E12 (building_id),
          INDEX IDX_2984AD93F639F774 (campaign_id),
          INDEX IDX_2984AD93DCDF6621 (last_passage_done_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pap_floor_statistics (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          status VARCHAR(25) NOT NULL,
          floor_id INT UNSIGNED NOT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_853B68C8854679E2 (floor_id),
          INDEX IDX_853B68C8F639F774 (campaign_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          pap_bulding_block_statistics
        ADD
          CONSTRAINT FK_DB09D48732618357 FOREIGN KEY (building_block_id) REFERENCES pap_building_block (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_bulding_block_statistics
        ADD
          CONSTRAINT FK_DB09D487F639F774 FOREIGN KEY (campaign_id) REFERENCES pap_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_bulding_statistics
        ADD
          CONSTRAINT FK_2984AD934D2A7E12 FOREIGN KEY (building_id) REFERENCES pap_building (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_bulding_statistics
        ADD
          CONSTRAINT FK_2984AD93F639F774 FOREIGN KEY (campaign_id) REFERENCES pap_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_bulding_statistics
        ADD
          CONSTRAINT FK_2984AD93DCDF6621 FOREIGN KEY (last_passage_done_by_id) REFERENCES adherents (id) ON DELETE
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
        $this->addSql('ALTER TABLE pap_building_block DROP status');
        $this->addSql('ALTER TABLE pap_floor DROP status');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE pap_bulding_block_statistics');
        $this->addSql('DROP TABLE pap_bulding_statistics');
        $this->addSql('DROP TABLE pap_floor_statistics');
        $this->addSql('ALTER TABLE
          pap_building_block
        ADD
          status VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE
          pap_floor
        ADD
          status VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
