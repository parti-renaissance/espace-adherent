<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20210219175042 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          jecoute_news 
        ADD 
          author_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          notification TINYINT(1) DEFAULT \'0\' NOT NULL');
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
        $this->addSql('ALTER TABLE jecoute_news DROP author_id, DROP notification');
    }
}
