<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250701152300 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE adherent_messages DROP FOREIGN KEY FK_D187C183F675F31B
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                ADD
                  sender_id INT UNSIGNED DEFAULT NULL,
                ADD
                  sender_email VARCHAR(255) DEFAULT NULL,
                ADD
                  sender_name VARCHAR(255) DEFAULT NULL,
                ADD
                  author_scope VARCHAR(255) DEFAULT NULL,
                ADD
                  author_role VARCHAR(255) DEFAULT NULL,
                ADD
                  author_instance VARCHAR(255) DEFAULT NULL,
                ADD
                  author_zone VARCHAR(255) DEFAULT NULL,
                CHANGE
                  author_id author_id INT UNSIGNED DEFAULT NULL,
                CHANGE
                  source source VARCHAR(255) DEFAULT 'cadre' NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                ADD
                  CONSTRAINT FK_D187C183F624B39D FOREIGN KEY (sender_id) REFERENCES adherents (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                ADD
                  CONSTRAINT FK_D187C183F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_D187C183F624B39D ON adherent_messages (sender_id)
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE adherent_messages DROP FOREIGN KEY FK_D187C183F624B39D
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE adherent_messages DROP FOREIGN KEY FK_D187C183F675F31B
            SQL);
        $this->addSql(<<<'SQL'
                DROP INDEX IDX_D187C183F624B39D ON adherent_messages
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                DROP
                  sender_id,
                DROP
                  sender_email,
                DROP
                  sender_name,
                DROP
                  author_scope,
                DROP
                  author_role,
                DROP
                  author_instance,
                DROP
                  author_zone,
                CHANGE
                  author_id author_id INT UNSIGNED NOT NULL,
                CHANGE
                  source source VARCHAR(255) DEFAULT 'platform' NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                ADD
                  CONSTRAINT FK_D187C183F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
    }
}
