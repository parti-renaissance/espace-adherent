<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210226133019 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          jecoute_news 
        ADD 
          author_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          notification TINYINT(1) DEFAULT \'0\' NOT NULL, 
        ADD 
          published TINYINT(1) DEFAULT \'1\' NOT NULL, 
          CHANGE topic topic VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          jecoute_news 
        ADD 
          CONSTRAINT FK_3436209F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_3436209F675F31B ON jecoute_news (author_id)');
        $this->addSql('UPDATE jecoute_news SET notification = 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_news DROP FOREIGN KEY FK_3436209F675F31B');
        $this->addSql('DROP INDEX IDX_3436209F675F31B ON jecoute_news');
        $this->addSql('ALTER TABLE 
          jecoute_news 
        DROP 
          author_id, 
        DROP 
          notification, 
        DROP 
          published, 
          CHANGE topic topic VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
