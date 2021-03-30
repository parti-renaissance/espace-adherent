<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181203114057 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX category_slug_unique ON ideas_workshop_category');
        $this->addSql('ALTER TABLE ideas_workshop_category DROP canonical_name, DROP slug');
        $this->addSql('CREATE UNIQUE INDEX category_name_unique ON ideas_workshop_category (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX category_name_unique ON ideas_workshop_category');
        $this->addSql('ALTER TABLE ideas_workshop_category ADD canonical_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD slug VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX category_slug_unique ON ideas_workshop_category (slug)');
    }
}
