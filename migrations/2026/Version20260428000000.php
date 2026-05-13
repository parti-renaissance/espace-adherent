<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260428000000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE adherent_note (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  target_adherent_id INT UNSIGNED NOT NULL,
                  content LONGTEXT NOT NULL,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                  UNIQUE INDEX UNIQ_AD_NOTE_UUID (uuid),
                  INDEX IDX_AD_NOTE_TARGET (target_adherent_id),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE adherent_note_author (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  note_id INT UNSIGNED NOT NULL,
                  author_id INT UNSIGNED DEFAULT NULL,
                  edited_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                  type VARCHAR(10) NOT NULL,
                  content LONGTEXT NOT NULL,
                  INDEX IDX_AD_NOTE_AUTHOR_NOTE (note_id),
                  INDEX IDX_AD_NOTE_AUTHOR_AUTHOR (author_id),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE adherent_note
                ADD CONSTRAINT FK_AD_NOTE_TARGET FOREIGN KEY (target_adherent_id) REFERENCES adherents (id) ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE adherent_note_author
                ADD CONSTRAINT FK_AD_NOTE_AUTHOR_NOTE FOREIGN KEY (note_id) REFERENCES adherent_note (id) ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE adherent_note_author
                ADD CONSTRAINT FK_AD_NOTE_AUTHOR_AUTHOR FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE SET NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_note_author DROP FOREIGN KEY FK_AD_NOTE_AUTHOR_NOTE');
        $this->addSql('ALTER TABLE adherent_note_author DROP FOREIGN KEY FK_AD_NOTE_AUTHOR_AUTHOR');
        $this->addSql('ALTER TABLE adherent_note DROP FOREIGN KEY FK_AD_NOTE_TARGET');
        $this->addSql('DROP TABLE adherent_note_author');
        $this->addSql('DROP TABLE adherent_note');
    }
}
