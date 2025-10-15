<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251015130941 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                ADD
                  team_owner_id INT UNSIGNED DEFAULT NULL,
                ADD
                  sender_role VARCHAR(255) DEFAULT NULL,
                ADD
                  sender_instance VARCHAR(255) DEFAULT NULL,
                ADD
                  sender_zone VARCHAR(255) DEFAULT NULL,
                ADD
                  sender_theme JSON DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                ADD
                  CONSTRAINT FK_D187C183C67EBD87 FOREIGN KEY (team_owner_id) REFERENCES adherents (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('CREATE INDEX IDX_D187C183C67EBD87 ON adherent_messages (team_owner_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_messages DROP FOREIGN KEY FK_D187C183C67EBD87');
        $this->addSql('DROP INDEX IDX_D187C183C67EBD87 ON adherent_messages');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                DROP
                  team_owner_id,
                DROP
                  sender_role,
                DROP
                  sender_instance,
                DROP
                  sender_zone,
                DROP
                  sender_theme
            SQL);
    }
}
