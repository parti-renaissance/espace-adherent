<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170522172234 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE donations ADD frequency SMALLINT NOT NULL');
        $this->addSql('UPDATE donations SET frequency = 1');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE donations DROP frequency');
    }
}
