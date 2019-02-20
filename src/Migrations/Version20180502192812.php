<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180502192812 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_documents CHANGE mime_type mime_type VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_documents CHANGE mime_type mime_type VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci');
    }
}
