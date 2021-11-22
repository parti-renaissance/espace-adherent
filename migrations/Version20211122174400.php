<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211122174400 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX jecoute_news_uuid_unique ON jecoute_news (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX jecoute_news_uuid_unique ON jecoute_news');
    }
}
