<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190204103132 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE `ideas_workshop_question` SET position = position + 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE `ideas_workshop_question` SET position = position - 1');
    }
}
