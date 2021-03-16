<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170526163645 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE administrators ADD roles LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('UPDATE administrators SET roles = role');
        $this->addSql('ALTER TABLE administrators DROP role');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE administrators ADD role VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE administrators DROP roles');
    }
}
