<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181203115226 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX need_slug_unique ON ideas_workshop_need');
        $this->addSql('ALTER TABLE ideas_workshop_need DROP canonical_name, DROP slug');
        $this->addSql('CREATE UNIQUE INDEX need_name_unique ON ideas_workshop_need (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX need_name_unique ON ideas_workshop_need');
        $this->addSql('ALTER TABLE ideas_workshop_need ADD canonical_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD slug VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX need_slug_unique ON ideas_workshop_need (slug)');
    }
}
