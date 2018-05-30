<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180529102348 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE donation_transactions ADD type VARCHAR(255) NOT NULL DEFAULT \'cb\'');
        $this->addSql('ALTER TABLE donation_transactions CHANGE type type VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE donation_transactions DROP type');
    }
}
