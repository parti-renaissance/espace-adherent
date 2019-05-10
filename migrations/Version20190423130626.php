<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190423130626 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees DROP google_plus_page_url');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees ADD google_plus_page_url VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
