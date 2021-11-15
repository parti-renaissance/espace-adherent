<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211116165507 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pap_building (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          address_id INT UNSIGNED NOT NULL,
          UNIQUE INDEX UNIQ_112ABBE1F5B7AF75 (address_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pap_building_block (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) NOT NULL,
          status VARCHAR(10) NOT NULL,
          building_id INT UNSIGNED NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          created_by_id INT UNSIGNED DEFAULT NULL,
          updated_by_id INT UNSIGNED DEFAULT NULL,
          INDEX IDX_61470C814D2A7E12 (building_id),
          INDEX IDX_61470C81B03A8386 (created_by_id),
          INDEX IDX_61470C81896DBBDE (updated_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pap_floor (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          number VARCHAR(255) NOT NULL,
          status VARCHAR(10) NOT NULL,
          building_block_id INT UNSIGNED NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          created_by_id INT UNSIGNED DEFAULT NULL,
          updated_by_id INT UNSIGNED DEFAULT NULL,
          INDEX IDX_633C3C6432618357 (building_block_id),
          INDEX IDX_633C3C64B03A8386 (created_by_id),
          INDEX IDX_633C3C64896DBBDE (updated_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          pap_building
        ADD
          CONSTRAINT FK_112ABBE1F5B7AF75 FOREIGN KEY (address_id) REFERENCES pap_address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_building_block
        ADD
          CONSTRAINT FK_61470C814D2A7E12 FOREIGN KEY (building_id) REFERENCES pap_building (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_building_block
        ADD
          CONSTRAINT FK_61470C81B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_building_block
        ADD
          CONSTRAINT FK_61470C81896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_floor
        ADD
          CONSTRAINT FK_633C3C6432618357 FOREIGN KEY (building_block_id) REFERENCES pap_building_block (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_floor
        ADD
          CONSTRAINT FK_633C3C64B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_floor
        ADD
          CONSTRAINT FK_633C3C64896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_building_block DROP FOREIGN KEY FK_61470C814D2A7E12');
        $this->addSql('ALTER TABLE pap_floor DROP FOREIGN KEY FK_633C3C6432618357');
        $this->addSql('DROP TABLE pap_building');
        $this->addSql('DROP TABLE pap_building_block');
        $this->addSql('DROP TABLE pap_floor');
    }
}
