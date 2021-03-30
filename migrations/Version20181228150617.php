<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181228150617 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX idea_uuid_unique ON ideas_workshop_idea (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idea_uuid_unique ON ideas_workshop_idea');
    }
}
