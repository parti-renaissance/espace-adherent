<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230207174546 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_formation DROP FOREIGN KEY FK_2D97408B93CB796C');
        $this->addSql('DROP INDEX UNIQ_2D97408B2B36786B ON adherent_formation');
        $this->addSql('DROP INDEX UNIQ_2D97408B93CB796C ON adherent_formation');
        $this->addSql('ALTER TABLE
          adherent_formation
        ADD
          created_by_administrator_id INT DEFAULT NULL,
        ADD
          updated_by_administrator_id INT DEFAULT NULL,
        ADD
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          zone_id INT UNSIGNED DEFAULT NULL,
        ADD
          content_type VARCHAR(255) NOT NULL,
        ADD
          file_path VARCHAR(255) DEFAULT NULL,
        ADD
          link VARCHAR(255) DEFAULT NULL,
        ADD
          valid TINYINT(1) DEFAULT \'0\' NOT NULL,
        ADD
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
        ADD
          created_at DATETIME NOT NULL,
        ADD
          updated_at DATETIME NOT NULL,
        ADD
          visibility VARCHAR(30) NOT NULL,
        CHANGE
          id id INT UNSIGNED AUTO_INCREMENT NOT NULL,
        CHANGE
          file_id created_by_adherent_id INT UNSIGNED DEFAULT NULL,
        CHANGE
          visible published TINYINT(1) DEFAULT \'0\' NOT NULL,
        CHANGE
          downloads_count print_count SMALLINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          adherent_formation
        ADD
          CONSTRAINT FK_2D97408B9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_formation
        ADD
          CONSTRAINT FK_2D97408BCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_formation
        ADD
          CONSTRAINT FK_2D97408B85C9D733 FOREIGN KEY (created_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_formation
        ADD
          CONSTRAINT FK_2D97408BDF6CFDC9 FOREIGN KEY (updated_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_formation
        ADD
          CONSTRAINT FK_2D97408B9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2D97408BD17F50A6 ON adherent_formation (uuid)');
        $this->addSql('CREATE INDEX IDX_2D97408B9DF5350C ON adherent_formation (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_2D97408BCF1918FF ON adherent_formation (updated_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_2D97408B85C9D733 ON adherent_formation (created_by_adherent_id)');
        $this->addSql('CREATE INDEX IDX_2D97408BDF6CFDC9 ON adherent_formation (updated_by_adherent_id)');
        $this->addSql('CREATE INDEX IDX_2D97408B9F2C3FAB ON adherent_formation (zone_id)');
        $this->addSql('DROP TABLE adherent_formation_file');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_formation_file (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          path VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          extension VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX adherent_formation_file_slug_extension (slug, extension),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE adherent_formation DROP FOREIGN KEY FK_2D97408B9DF5350C');
        $this->addSql('ALTER TABLE adherent_formation DROP FOREIGN KEY FK_2D97408BCF1918FF');
        $this->addSql('ALTER TABLE adherent_formation DROP FOREIGN KEY FK_2D97408B85C9D733');
        $this->addSql('ALTER TABLE adherent_formation DROP FOREIGN KEY FK_2D97408BDF6CFDC9');
        $this->addSql('ALTER TABLE adherent_formation DROP FOREIGN KEY FK_2D97408B9F2C3FAB');
        $this->addSql('DROP INDEX UNIQ_2D97408BD17F50A6 ON adherent_formation');
        $this->addSql('DROP INDEX IDX_2D97408B9DF5350C ON adherent_formation');
        $this->addSql('DROP INDEX IDX_2D97408BCF1918FF ON adherent_formation');
        $this->addSql('DROP INDEX IDX_2D97408B85C9D733 ON adherent_formation');
        $this->addSql('DROP INDEX IDX_2D97408BDF6CFDC9 ON adherent_formation');
        $this->addSql('DROP INDEX IDX_2D97408B9F2C3FAB ON adherent_formation');
        $this->addSql('ALTER TABLE
          adherent_formation
        ADD
          file_id INT UNSIGNED DEFAULT NULL,
        ADD
          visible TINYINT(1) DEFAULT \'0\' NOT NULL,
        DROP
          created_by_administrator_id,
        DROP
          updated_by_administrator_id,
        DROP
          created_by_adherent_id,
        DROP
          updated_by_adherent_id,
        DROP
          zone_id,
        DROP
          content_type,
        DROP
          file_path,
        DROP
          link,
        DROP
          published,
        DROP
          valid,
        DROP
          uuid,
        DROP
          created_at,
        DROP
          updated_at,
        DROP
          visibility,
        CHANGE
          id id BIGINT AUTO_INCREMENT NOT NULL,
        CHANGE
          print_count downloads_count SMALLINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          adherent_formation
        ADD
          CONSTRAINT FK_2D97408B93CB796C FOREIGN KEY (file_id) REFERENCES adherent_formation_file (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2D97408B2B36786B ON adherent_formation (title)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2D97408B93CB796C ON adherent_formation (file_id)');
    }
}
