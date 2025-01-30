<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230103112607 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherents
        ADD
          created_by_administrator_id INT DEFAULT NULL,
        ADD
          updated_by_administrator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA39DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_562C7DA39DF5350C ON adherents (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_562C7DA3CF1918FF ON adherents (updated_by_administrator_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA39DF5350C');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3CF1918FF');
        $this->addSql('DROP INDEX IDX_562C7DA39DF5350C ON adherents');
        $this->addSql('DROP INDEX IDX_562C7DA3CF1918FF ON adherents');
        $this->addSql('ALTER TABLE adherents DROP created_by_administrator_id, DROP updated_by_administrator_id');
    }
}
