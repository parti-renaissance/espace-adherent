<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190401172021 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSQL(<<<'SQL'
            INSERT INTO event_referent_tag
            (event_id, referent_tag_id)
            (SELECT institutional_event_id, referent_tag_id FROM institutional_event_referent_tag)
SQL
        );
        $this->addSql('DROP TABLE institutional_event_referent_tag');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE institutional_event_referent_tag (
          institutional_event_id INT UNSIGNED NOT NULL, 
          referent_tag_id INT UNSIGNED NOT NULL, 
          INDEX IDX_893A83F2D07A00D (institutional_event_id), 
          INDEX IDX_893A83F29C262DB3 (referent_tag_id), 
          PRIMARY KEY(
            institutional_event_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          institutional_event_referent_tag 
        ADD 
          CONSTRAINT FK_893A83F29C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          institutional_event_referent_tag 
        ADD 
          CONSTRAINT FK_893A83F2D07A00D FOREIGN KEY (institutional_event_id) REFERENCES events (id) ON DELETE CASCADE');
    }
}
