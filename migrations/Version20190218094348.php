<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190218094348 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE institutional_event_referent_tag (
          institutional_event_id INT UNSIGNED NOT NULL, 
          referent_tag_id INT UNSIGNED NOT NULL, 
          INDEX IDX_893A83F2D07A00D (institutional_event_id), 
          INDEX IDX_893A83F29C262DB3 (referent_tag_id), 
          PRIMARY KEY(
            institutional_event_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE institutional_events_categories (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL, 
          UNIQUE INDEX institutional_event_category_name_unique (name), 
          UNIQUE INDEX institutional_event_slug_unique (slug), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          institutional_event_referent_tag 
        ADD 
          CONSTRAINT FK_893A83F2D07A00D FOREIGN KEY (institutional_event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          institutional_event_referent_tag 
        ADD 
          CONSTRAINT FK_893A83F29C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE institutional_event_referent_tag');
        $this->addSql('DROP TABLE institutional_events_categories');
    }
}
