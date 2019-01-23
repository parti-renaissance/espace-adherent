<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181217174342 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX theme_slug_unique ON ideas_workshop_theme');
        $this->addSql('ALTER TABLE ideas_workshop_theme DROP canonical_name, DROP slug');
        $this->addSql('CREATE UNIQUE INDEX theme_name_unique ON ideas_workshop_theme (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX theme_name_unique ON ideas_workshop_theme');
        $this->addSql('ALTER TABLE ideas_workshop_theme ADD canonical_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD slug VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX theme_slug_unique ON ideas_workshop_theme (slug)');
    }
}
