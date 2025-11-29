<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230215084303 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          committees
        ADD
          created_by_administrator_id INT DEFAULT NULL,
        ADD
          updated_by_administrator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          committees
        ADD
          CONSTRAINT FK_A36198C69DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          committees
        ADD
          CONSTRAINT FK_A36198C6CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_A36198C69DF5350C ON committees (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_A36198C6CF1918FF ON committees (updated_by_administrator_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees DROP FOREIGN KEY FK_A36198C69DF5350C');
        $this->addSql('ALTER TABLE committees DROP FOREIGN KEY FK_A36198C6CF1918FF');
        $this->addSql('DROP INDEX IDX_A36198C69DF5350C ON committees');
        $this->addSql('DROP INDEX IDX_A36198C6CF1918FF ON committees');
        $this->addSql('ALTER TABLE committees DROP created_by_administrator_id, DROP updated_by_administrator_id');
    }
}
