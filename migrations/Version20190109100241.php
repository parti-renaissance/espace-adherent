<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190109100241 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events ADD time_zone VARCHAR(50) DEFAULT \'Europe/Paris\' NOT NULL');
        $this->addSql('ALTER TABLE events CHANGE time_zone time_zone VARCHAR(50) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP time_zone');
    }
}
