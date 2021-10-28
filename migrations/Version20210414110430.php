<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210414110430 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE email_templates (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          label VARCHAR(255) NOT NULL,
          content LONGTEXT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_6023E2A5F675F31B (author_id),
          UNIQUE INDEX email_template_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          email_templates
        ADD
          CONSTRAINT FK_6023E2A5F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE email_templates');
    }
}
