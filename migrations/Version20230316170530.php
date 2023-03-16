<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230316170530 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          elected_representative
        ADD
          last_contribution_id INT UNSIGNED DEFAULT NULL,
        DROP
          last_contribution_date');
        $this->addSql('ALTER TABLE
          elected_representative
        ADD
          CONSTRAINT FK_BF51F0FD14E51F8D FOREIGN KEY (last_contribution_id) REFERENCES elected_representative_contribution (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BF51F0FD14E51F8D ON elected_representative (last_contribution_id)');
        $this->addSql('ALTER TABLE
          elected_representative_contribution
        ADD
          start_date DATETIME DEFAULT NULL,
        ADD
          end_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative DROP FOREIGN KEY FK_BF51F0FD14E51F8D');
        $this->addSql('DROP INDEX UNIQ_BF51F0FD14E51F8D ON elected_representative');
        $this->addSql('ALTER TABLE
          elected_representative
        ADD
          last_contribution_date DATETIME DEFAULT NULL,
        DROP
          last_contribution_id');
        $this->addSql('ALTER TABLE elected_representative_contribution DROP start_date, DROP end_date');
    }
}
