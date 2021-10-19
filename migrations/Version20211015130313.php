<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211015130313 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F4B09E92C');
        $this->addSql('DROP INDEX IDX_C4E0A61F4B09E92C ON team');
        $this->addSql('ALTER TABLE
          team
        ADD
          updated_by_administrator_id INT DEFAULT NULL,
        CHANGE
          administrator_id created_by_administrator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          team
        ADD
          CONSTRAINT FK_C4E0A61F9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          team
        ADD
          CONSTRAINT FK_C4E0A61FCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_C4E0A61F9DF5350C ON team (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_C4E0A61FCF1918FF ON team (updated_by_administrator_id)');

        $this->addSql('ALTER TABLE
          team
        ADD
          created_by_adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          team
        ADD
          CONSTRAINT FK_C4E0A61F85C9D733 FOREIGN KEY (created_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          team
        ADD
          CONSTRAINT FK_C4E0A61FDF6CFDC9 FOREIGN KEY (updated_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_C4E0A61F85C9D733 ON team (created_by_adherent_id)');
        $this->addSql('CREATE INDEX IDX_C4E0A61FDF6CFDC9 ON team (updated_by_adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F9DF5350C');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FCF1918FF');
        $this->addSql('DROP INDEX IDX_C4E0A61F9DF5350C ON team');
        $this->addSql('DROP INDEX IDX_C4E0A61FCF1918FF ON team');
        $this->addSql('ALTER TABLE
          team
        ADD
          administrator_id INT DEFAULT NULL,
        DROP
          created_by_administrator_id,
        DROP
          updated_by_administrator_id');
        $this->addSql('ALTER TABLE
          team
        ADD
          CONSTRAINT FK_C4E0A61F4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_C4E0A61F4B09E92C ON team (administrator_id)');

        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F85C9D733');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FDF6CFDC9');
        $this->addSql('DROP INDEX IDX_C4E0A61F85C9D733 ON team');
        $this->addSql('DROP INDEX IDX_C4E0A61FDF6CFDC9 ON team');
        $this->addSql('ALTER TABLE team DROP created_by_adherent_id, DROP updated_by_adherent_id');
    }
}
