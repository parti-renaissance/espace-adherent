<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190117140514 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD enabled TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_thread ADD enabled TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP enabled');
        $this->addSql('ALTER TABLE ideas_workshop_thread DROP enabled');
    }
}
