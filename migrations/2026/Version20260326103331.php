<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260326103331 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  tally_form
                ADD
                  created_by_administrator_id INT DEFAULT NULL,
                ADD
                  updated_by_administrator_id INT DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  tally_form
                ADD
                  CONSTRAINT FK_79C06D569DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  tally_form
                ADD
                  CONSTRAINT FK_79C06D56CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('CREATE INDEX IDX_79C06D569DF5350C ON tally_form (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_79C06D56CF1918FF ON tally_form (updated_by_administrator_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tally_form DROP FOREIGN KEY FK_79C06D569DF5350C');
        $this->addSql('ALTER TABLE tally_form DROP FOREIGN KEY FK_79C06D56CF1918FF');
        $this->addSql('DROP INDEX IDX_79C06D569DF5350C ON tally_form');
        $this->addSql('DROP INDEX IDX_79C06D56CF1918FF ON tally_form');
        $this->addSql('ALTER TABLE tally_form DROP created_by_administrator_id, DROP updated_by_administrator_id');
    }
}
