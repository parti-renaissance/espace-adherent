<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190104103759 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_consultation CHANGE enabled enabled TINYINT(1) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX consultation_enabled_unique ON ideas_workshop_consultation (enabled)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX consultation_enabled_unique ON ideas_workshop_consultation');
        $this->addSql('ALTER TABLE ideas_workshop_consultation CHANGE enabled enabled TINYINT(1) NOT NULL');
    }
}
