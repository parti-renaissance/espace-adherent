<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251020083347 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE adherent_message_reach (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  message_id INT UNSIGNED NOT NULL,
                  adherent_id INT UNSIGNED DEFAULT NULL,
                  source VARCHAR(255) NOT NULL,
                  date DATETIME NOT NULL,
                  INDEX IDX_39E44CAD537A1329 (message_id),
                  INDEX IDX_39E44CAD25F06C53 (adherent_id),
                  UNIQUE INDEX UNIQ_39E44CAD25F06C53537A13295F8A7F73 (adherent_id, message_id, source),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_reach
                ADD
                  CONSTRAINT FK_39E44CAD537A1329 FOREIGN KEY (message_id) REFERENCES adherent_messages (id) ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_reach
                ADD
                  CONSTRAINT FK_39E44CAD25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('ALTER TABLE notification ADD uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('UPDATE notification SET uuid = UUID() WHERE uuid IS NULL');
        $this->addSql('ALTER TABLE notification MODIFY uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BF5476CAD17F50A6 ON notification (uuid)');
        $this->addSql('ALTER TABLE push_token DROP FOREIGN KEY FK_51BC138125F06C53');
        $this->addSql('ALTER TABLE push_token DROP FOREIGN KEY FK_51BC138194A4C7D4');
        $this->addSql('DROP INDEX IDX_51BC138125F06C53 ON push_token');
        $this->addSql('DROP INDEX IDX_51BC138194A4C7D4 ON push_token');
        $this->addSql('ALTER TABLE push_token DROP adherent_id, DROP device_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_reach DROP FOREIGN KEY FK_39E44CAD537A1329');
        $this->addSql('ALTER TABLE adherent_message_reach DROP FOREIGN KEY FK_39E44CAD25F06C53');
        $this->addSql('DROP TABLE adherent_message_reach');
        $this->addSql('DROP INDEX UNIQ_BF5476CAD17F50A6 ON notification');
        $this->addSql('ALTER TABLE notification DROP uuid');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  push_token
                ADD
                  adherent_id INT UNSIGNED DEFAULT NULL,
                ADD
                  device_id INT UNSIGNED DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  push_token
                ADD
                  CONSTRAINT FK_51BC138125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  push_token
                ADD
                  CONSTRAINT FK_51BC138194A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql('CREATE INDEX IDX_51BC138125F06C53 ON push_token (adherent_id)');
        $this->addSql('CREATE INDEX IDX_51BC138194A4C7D4 ON push_token (device_id)');
    }
}
