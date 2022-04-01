<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220331154532 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          home_blocks
        ADD
          created_by_administrator_id INT DEFAULT NULL,
        ADD
          updated_by_administrator_id INT DEFAULT NULL,
        ADD
          created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE
          home_blocks
        ADD
          CONSTRAINT FK_3EE9FCC59DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          home_blocks
        ADD
          CONSTRAINT FK_3EE9FCC5CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_3EE9FCC59DF5350C ON home_blocks (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_3EE9FCC5CF1918FF ON home_blocks (updated_by_administrator_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE home_blocks DROP FOREIGN KEY FK_3EE9FCC59DF5350C');
        $this->addSql('ALTER TABLE home_blocks DROP FOREIGN KEY FK_3EE9FCC5CF1918FF');
        $this->addSql('DROP INDEX IDX_3EE9FCC59DF5350C ON home_blocks');
        $this->addSql('DROP INDEX IDX_3EE9FCC5CF1918FF ON home_blocks');
        $this->addSql('ALTER TABLE
          home_blocks
        DROP
          created_by_administrator_id,
        DROP
          updated_by_administrator_id,
        DROP
          created_at');
    }
}
