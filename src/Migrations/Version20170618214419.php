<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170618214419 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE clarifications ADD amp_content LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE proposals ADD amp_content LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE articles ADD amp_content LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE pages ADD amp_content LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE articles DROP amp_content');
        $this->addSql('ALTER TABLE clarifications DROP amp_content');
        $this->addSql('ALTER TABLE pages DROP amp_content');
        $this->addSql('ALTER TABLE proposals DROP amp_content');
    }
}
