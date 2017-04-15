<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170301230652 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE je_marche_reports CHANGE not_convinced not_convinced SMALLINT DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE je_marche_reports CHANGE not_convinced not_convinced SMALLINT NOT NULL');
    }
}
