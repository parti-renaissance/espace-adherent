<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190305155124 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE events_groups_categories (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL, 
          UNIQUE INDEX event_group_category_name_unique (name), 
          UNIQUE INDEX event_group_category_slug_unique (slug), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE events_categories ADD event_group_category_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          events_categories 
        ADD 
          CONSTRAINT FK_EF0AF3E9A267D842 FOREIGN KEY (event_group_category_id) REFERENCES events_groups_categories (id)');
        $this->addSql('CREATE INDEX IDX_EF0AF3E9A267D842 ON events_categories (event_group_category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE events_categories DROP FOREIGN KEY FK_EF0AF3E9A267D842');
        $this->addSql('DROP TABLE events_groups_categories');
        $this->addSql('DROP INDEX IDX_EF0AF3E9A267D842 ON events_categories');
        $this->addSql('ALTER TABLE events_categories DROP event_group_category_id');
    }
}
