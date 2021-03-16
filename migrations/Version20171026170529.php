<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171026170529 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mooc_event_categories (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX mooc_event_category_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->insert('mooc_event_categories', ['name' => 'Séance MOOC']);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE mooc_event_categories');
    }
}
