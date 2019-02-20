<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170424164217 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE je_marche_reports CHANGE not_convinced not_convinced SMALLINT UNSIGNED DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE je_marche_reports CHANGE not_convinced not_convinced SMALLINT DEFAULT NULL');
    }
}
