<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170218191110 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE je_marche_reports ADD postal_code VARCHAR(11) NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE je_marche_reports DROP postal_code');
    }
}
