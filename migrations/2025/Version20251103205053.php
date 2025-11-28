<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251103205053 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_messages ADD instance_key VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD instance_key VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_news ADD instance_key VARCHAR(255) DEFAULT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  user_documents
                ADD
                  author_id INT UNSIGNED DEFAULT NULL,
                ADD
                  author_scope VARCHAR(255) DEFAULT NULL,
                ADD
                  author_role VARCHAR(255) DEFAULT NULL,
                ADD
                  author_instance VARCHAR(255) DEFAULT NULL,
                ADD
                  author_zone VARCHAR(255) DEFAULT NULL,
                ADD
                  author_theme JSON DEFAULT NULL,
                ADD
                  instance_key VARCHAR(255) DEFAULT NULL,
                ADD
                  updated_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql('UPDATE user_documents SET updated_at = created_at WHERE updated_at IS NULL');
        $this->addSql('ALTER TABLE user_documents MODIFY updated_at DATETIME NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  user_documents
                ADD
                  CONSTRAINT FK_A250FF6CF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('CREATE INDEX IDX_A250FF6CF675F31B ON user_documents (author_id)');
        $this->addSql('ALTER TABLE vox_action ADD instance_key VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_messages DROP instance_key');
        $this->addSql('ALTER TABLE `events` DROP instance_key');
        $this->addSql('ALTER TABLE jecoute_news DROP instance_key');
        $this->addSql('ALTER TABLE user_documents DROP FOREIGN KEY FK_A250FF6CF675F31B');
        $this->addSql('DROP INDEX IDX_A250FF6CF675F31B ON user_documents');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  user_documents
                DROP
                  author_id,
                DROP
                  author_scope,
                DROP
                  author_role,
                DROP
                  author_instance,
                DROP
                  author_zone,
                DROP
                  author_theme,
                DROP
                  instance_key,
                DROP
                  updated_at
            SQL);
        $this->addSql('ALTER TABLE vox_action DROP instance_key');
    }
}
