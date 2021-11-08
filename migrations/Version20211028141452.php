<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211028141452 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE phoning_campaign DROP FOREIGN KEY FK_C3882BA44B09E92C');
        $this->addSql('DROP INDEX IDX_C3882BA44B09E92C ON phoning_campaign');
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          updated_by_administrator_id INT DEFAULT NULL,
        ADD
          created_by_adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL,
        CHANGE
          administrator_id created_by_administrator_id INT DEFAULT NULL');
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
        $this->addSql('CREATE INDEX IDX_C3882BA49DF5350C ON phoning_campaign (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_C3882BA4CF1918FF ON phoning_campaign (updated_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_C3882BA485C9D733 ON phoning_campaign (created_by_adherent_id)');
        $this->addSql('CREATE INDEX IDX_C3882BA4DF6CFDC9 ON phoning_campaign (updated_by_adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE phoning_campaign DROP FOREIGN KEY FK_C3882BA49DF5350C');
        $this->addSql('ALTER TABLE phoning_campaign DROP FOREIGN KEY FK_C3882BA4CF1918FF');
        $this->addSql('ALTER TABLE phoning_campaign DROP FOREIGN KEY FK_C3882BA485C9D733');
        $this->addSql('ALTER TABLE phoning_campaign DROP FOREIGN KEY FK_C3882BA4DF6CFDC9');
        $this->addSql('DROP INDEX IDX_C3882BA49DF5350C ON phoning_campaign');
        $this->addSql('DROP INDEX IDX_C3882BA4CF1918FF ON phoning_campaign');
        $this->addSql('DROP INDEX IDX_C3882BA485C9D733 ON phoning_campaign');
        $this->addSql('DROP INDEX IDX_C3882BA4DF6CFDC9 ON phoning_campaign');
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          administrator_id INT DEFAULT NULL,
        DROP
          created_by_administrator_id,
        DROP
          updated_by_administrator_id,
        DROP
          created_by_adherent_id,
        DROP
          updated_by_adherent_id');
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          CONSTRAINT FK_C3882BA44B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_C3882BA44B09E92C ON phoning_campaign (administrator_id)');
    }
}
