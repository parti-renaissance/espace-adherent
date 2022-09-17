<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220917153647 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherents
        ADD
          exclusive_membership TINYINT(1) DEFAULT \'0\' NOT NULL,
        ADD
          territoire_progres_membership TINYINT(1) DEFAULT \'0\' NOT NULL,
        ADD
          agir_membership TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherents
        DROP
          exclusive_membership,
        DROP
          territoire_progres_membership,
        DROP
          agir_membership');
    }
}
