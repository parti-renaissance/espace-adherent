<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250828213838 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE timeline_item_private_message (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          is_active TINYINT(1) DEFAULT 1 NOT NULL,
          is_notification_active TINYINT(1) DEFAULT 1 NOT NULL,
          notification_sent_at DATETIME DEFAULT NULL,
          title VARCHAR(255) NOT NULL,
          description LONGTEXT NOT NULL,
          notification_title VARCHAR(255) DEFAULT NULL,
          notification_description LONGTEXT DEFAULT NULL,
          cta_label VARCHAR(255) DEFAULT NULL,
          cta_url VARCHAR(255) DEFAULT NULL,
          source VARCHAR(255) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_CD291A19D17F50A6 (uuid),
          INDEX IDX_CD291A199DF5350C (created_by_administrator_id),
          INDEX IDX_CD291A19CF1918FF (updated_by_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE timeline_item_private_message_adherent (
          timeline_item_private_message_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          INDEX IDX_A4581DEC87293FB5 (
            timeline_item_private_message_id
          ),
          INDEX IDX_A4581DEC25F06C53 (adherent_id),
          PRIMARY KEY(
            timeline_item_private_message_id,
            adherent_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          timeline_item_private_message
        ADD
          CONSTRAINT FK_CD291A199DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          timeline_item_private_message
        ADD
          CONSTRAINT FK_CD291A19CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          timeline_item_private_message_adherent
        ADD
          CONSTRAINT FK_A4581DEC87293FB5 FOREIGN KEY (
            timeline_item_private_message_id
          ) REFERENCES timeline_item_private_message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          timeline_item_private_message_adherent
        ADD
          CONSTRAINT FK_A4581DEC25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE timeline_item_private_message DROP FOREIGN KEY FK_CD291A199DF5350C');
        $this->addSql('ALTER TABLE timeline_item_private_message DROP FOREIGN KEY FK_CD291A19CF1918FF');
        $this->addSql('ALTER TABLE timeline_item_private_message_adherent DROP FOREIGN KEY FK_A4581DEC87293FB5');
        $this->addSql('ALTER TABLE timeline_item_private_message_adherent DROP FOREIGN KEY FK_A4581DEC25F06C53');
        $this->addSql('DROP TABLE timeline_item_private_message');
        $this->addSql('DROP TABLE timeline_item_private_message_adherent');
    }
}
