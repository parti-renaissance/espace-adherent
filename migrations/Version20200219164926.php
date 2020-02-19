<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200219164926 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vote_place ADD enabled TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vote_place DROP enabled');
    }
}
