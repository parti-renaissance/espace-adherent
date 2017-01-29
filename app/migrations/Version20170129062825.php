<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170129062825 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE articles_categories ADD position SMALLINT NOT NULL, ADD slug VARCHAR(100) NOT NULL');
        $this->addSql('UPDATE articles_categories SET position = id, slug = id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DE004A0E989D9B62 ON articles_categories (slug)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP INDEX UNIQ_DE004A0E989D9B62 ON articles_categories');
        $this->addSql('ALTER TABLE articles_categories DROP position, DROP slug');
    }
}
