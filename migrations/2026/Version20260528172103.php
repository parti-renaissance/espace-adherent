<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528172103 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                ADD
                  created_by_administrator_id INT DEFAULT NULL,
                ADD
                  updated_by_administrator_id INT DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                ADD
                  CONSTRAINT FK_C33255579DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                ADD
                  CONSTRAINT FK_C3325557CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('CREATE INDEX IDX_C33255579DF5350C ON national_event_inscription (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_C3325557CF1918FF ON national_event_inscription (updated_by_administrator_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event_inscription DROP FOREIGN KEY FK_C33255579DF5350C');
        $this->addSql('ALTER TABLE national_event_inscription DROP FOREIGN KEY FK_C3325557CF1918FF');
        $this->addSql('DROP INDEX IDX_C33255579DF5350C ON national_event_inscription');
        $this->addSql('DROP INDEX IDX_C3325557CF1918FF ON national_event_inscription');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                DROP
                  created_by_administrator_id,
                DROP
                  updated_by_administrator_id
            SQL);
    }
}
