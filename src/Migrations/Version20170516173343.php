<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170516173343 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pages ADD twitter_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE proposals ADD twitter_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE articles ADD twitter_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE clarifications ADD twitter_description VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE articles DROP twitter_description');
        $this->addSql('ALTER TABLE clarifications DROP twitter_description');
        $this->addSql('ALTER TABLE pages DROP twitter_description');
        $this->addSql('ALTER TABLE proposals DROP twitter_description');
    }
}
