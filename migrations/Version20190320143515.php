<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190320143515 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_group_category (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL, 
          UNIQUE INDEX event_group_category_name_unique (name), 
          UNIQUE INDEX event_group_category_slug_unique (slug), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');

        $this->addSql('INSERT INTO event_group_category (`name`, `slug`) VALUES (\'événement\', \'evenement\')');
        $this->addSql('ALTER TABLE events_categories ADD event_group_category_id INT UNSIGNED DEFAULT 1');
        $this->addSql('ALTER TABLE  events_categories CHANGE event_group_category_id event_group_category_id INT UNSIGNED NOT NULL');

        $this->addSql('ALTER TABLE 
          events_categories 
        ADD 
          CONSTRAINT FK_EF0AF3E9A267D842 FOREIGN KEY (event_group_category_id) REFERENCES event_group_category (id)');
        $this->addSql('CREATE INDEX IDX_EF0AF3E9A267D842 ON events_categories (event_group_category_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events_categories DROP FOREIGN KEY FK_EF0AF3E9A267D842');
        $this->addSql('DROP TABLE event_group_category');
        $this->addSql('DROP INDEX IDX_EF0AF3E9A267D842 ON events_categories');
        $this->addSql('ALTER TABLE events_categories DROP event_group_category_id');
    }
}
