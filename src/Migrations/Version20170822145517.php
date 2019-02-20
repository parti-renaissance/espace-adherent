<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170822145517 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events ADD was_published TINYINT(1) DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP was_published');
    }
}
