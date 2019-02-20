<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170919103349 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees ADD phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees DROP phone');
    }
}
