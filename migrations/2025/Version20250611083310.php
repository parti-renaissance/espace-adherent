<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250611083310 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_news_user_documents DROP FOREIGN KEY FK_1231D19DD18EE7B3');

        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_news
                DROP
                  pinned,
                DROP
                  enriched,
                CHANGE
                  id id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                CHANGE
                  notification notification TINYINT(1) DEFAULT 1 NOT NULL,
                CHANGE
                  text content LONGTEXT NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_news_user_documents
                CHANGE
                  jecoute_news_id jecoute_news_id INT UNSIGNED NOT NULL
            SQL);

        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_news_user_documents
                ADD
                  CONSTRAINT FK_1231D19DD18EE7B3 FOREIGN KEY (jecoute_news_id) REFERENCES jecoute_news (id) ON DELETE CASCADE
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  jecoute_news
                ADD
                  pinned TINYINT(1) DEFAULT 0 NOT NULL,
                ADD
                  enriched TINYINT(1) DEFAULT 0 NOT NULL,
                CHANGE
                  id id INT AUTO_INCREMENT NOT NULL,
                CHANGE
                  notification notification TINYINT(1) DEFAULT 0 NOT NULL,
                CHANGE
                  content text LONGTEXT NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE jecoute_news_user_documents CHANGE jecoute_news_id jecoute_news_id INT NOT NULL
            SQL);
    }
}
