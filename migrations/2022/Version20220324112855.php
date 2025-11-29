<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220324112855 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE jecoute_news_user_documents (
          jecoute_news_id INT NOT NULL,
          user_document_id INT UNSIGNED NOT NULL,
          INDEX IDX_1231D19DD18EE7B3 (jecoute_news_id),
          INDEX IDX_1231D19D6A24B1A2 (user_document_id),
          PRIMARY KEY(
            jecoute_news_id, user_document_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          jecoute_news_user_documents
        ADD
          CONSTRAINT FK_1231D19DD18EE7B3 FOREIGN KEY (jecoute_news_id) REFERENCES jecoute_news (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          jecoute_news_user_documents
        ADD
          CONSTRAINT FK_1231D19D6A24B1A2 FOREIGN KEY (user_document_id) REFERENCES user_documents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE jecoute_news_user_documents');
    }
}
