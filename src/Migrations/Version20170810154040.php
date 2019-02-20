<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170810154040 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE citizen_initiative_categories (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX citizen_initiative_category_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE events ADD citizen_initiative_category_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574AE03E2EB9 FOREIGN KEY (citizen_initiative_category_id) REFERENCES citizen_initiative_categories (id)');
        $this->addSql('CREATE INDEX IDX_5387574AE03E2EB9 ON events (citizen_initiative_category_id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AE03E2EB9');
        $this->addSql('DROP TABLE citizen_initiative_categories');
        $this->addSql('DROP INDEX IDX_5387574AE03E2EB9 ON events');
        $this->addSql('ALTER TABLE events DROP citizen_initiative_category_id');
    }
}
