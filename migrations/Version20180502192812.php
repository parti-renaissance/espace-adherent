<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180502192812 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE user_documents CHANGE mime_type mime_type VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE user_documents CHANGE mime_type mime_type VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci');
    }
}
