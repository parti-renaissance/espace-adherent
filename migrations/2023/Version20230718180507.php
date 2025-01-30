<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230718180507 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_mandate
        ADD
          created_by_administrator_id INT DEFAULT NULL,
        ADD
          updated_by_administrator_id INT DEFAULT NULL,
        ADD
          created_by_adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          zone_id INT UNSIGNED DEFAULT NULL,
        ADD
          created_at DATETIME DEFAULT NULL,
        ADD
          updated_at DATETIME DEFAULT NULL,
        ADD
          mandate_type VARCHAR(255) DEFAULT NULL,
        ADD
          delegation VARCHAR(255) DEFAULT NULL,
        CHANGE
          gender gender VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherent_mandate
        ADD
          CONSTRAINT FK_9C0C3D609DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_mandate
        ADD
          CONSTRAINT FK_9C0C3D60CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_mandate
        ADD
          CONSTRAINT FK_9C0C3D6085C9D733 FOREIGN KEY (created_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_mandate
        ADD
          CONSTRAINT FK_9C0C3D60DF6CFDC9 FOREIGN KEY (updated_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_mandate
        ADD
          CONSTRAINT FK_9C0C3D609F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_9C0C3D609DF5350C ON adherent_mandate (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_9C0C3D60CF1918FF ON adherent_mandate (updated_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_9C0C3D6085C9D733 ON adherent_mandate (created_by_adherent_id)');
        $this->addSql('CREATE INDEX IDX_9C0C3D60DF6CFDC9 ON adherent_mandate (updated_by_adherent_id)');
        $this->addSql('CREATE INDEX IDX_9C0C3D609F2C3FAB ON adherent_mandate (zone_id)');

        $this->addSql('UPDATE adherent_mandate SET created_at = COALESCE(begin_at, NOW())');
        $this->addSql('UPDATE adherent_mandate SET updated_at = COALESCE(begin_at, NOW())');

        $this->addSql('ALTER TABLE
          adherent_mandate
        CHANGE
          created_at created_at DATETIME NOT NULL,
        CHANGE
          updated_at updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_mandate DROP FOREIGN KEY FK_9C0C3D609DF5350C');
        $this->addSql('ALTER TABLE adherent_mandate DROP FOREIGN KEY FK_9C0C3D60CF1918FF');
        $this->addSql('ALTER TABLE adherent_mandate DROP FOREIGN KEY FK_9C0C3D6085C9D733');
        $this->addSql('ALTER TABLE adherent_mandate DROP FOREIGN KEY FK_9C0C3D60DF6CFDC9');
        $this->addSql('ALTER TABLE adherent_mandate DROP FOREIGN KEY FK_9C0C3D609F2C3FAB');
        $this->addSql('DROP INDEX IDX_9C0C3D609DF5350C ON adherent_mandate');
        $this->addSql('DROP INDEX IDX_9C0C3D60CF1918FF ON adherent_mandate');
        $this->addSql('DROP INDEX IDX_9C0C3D6085C9D733 ON adherent_mandate');
        $this->addSql('DROP INDEX IDX_9C0C3D60DF6CFDC9 ON adherent_mandate');
        $this->addSql('DROP INDEX IDX_9C0C3D609F2C3FAB ON adherent_mandate');
        $this->addSql('ALTER TABLE
          adherent_mandate
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
          created_at,
        DROP
          updated_at,
        DROP
          mandate_type,
        DROP
          delegation,
        CHANGE
          gender gender VARCHAR(6) NOT NULL');
    }
}
