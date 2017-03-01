<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170302005646 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE je_marche_reports CHANGE reaction reaction LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE je_marche_reports CHANGE reaction reaction LONGTEXT NOT NULL COLLATE utf8_unicode_ci');
    }
}
