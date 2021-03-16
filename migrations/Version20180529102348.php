<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180529102348 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donation_transactions ADD type VARCHAR(255) NOT NULL DEFAULT \'cb\'');
        $this->addSql('ALTER TABLE donation_transactions CHANGE type type VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donation_transactions DROP type');
    }
}
