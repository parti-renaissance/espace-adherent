<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201130131955 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE devices (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          device_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          last_logged_at DATETIME DEFAULT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          UNIQUE INDEX devices_uuid_unique (uuid), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE oauth_auth_codes ADD device_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          oauth_auth_codes 
        ADD 
          CONSTRAINT FK_BB493F8394A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_BB493F8394A4C7D4 ON oauth_auth_codes (device_id)');
        $this->addSql('ALTER TABLE oauth_access_tokens ADD device_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          oauth_access_tokens 
        ADD 
          CONSTRAINT FK_CA42527C94A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_CA42527C94A4C7D4 ON oauth_access_tokens (device_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oauth_auth_codes DROP FOREIGN KEY FK_BB493F8394A4C7D4');
        $this->addSql('ALTER TABLE oauth_access_tokens DROP FOREIGN KEY FK_CA42527C94A4C7D4');
        $this->addSql('DROP TABLE devices');
        $this->addSql('DROP INDEX IDX_CA42527C94A4C7D4 ON oauth_access_tokens');
        $this->addSql('ALTER TABLE oauth_access_tokens DROP device_id');
        $this->addSql('DROP INDEX IDX_BB493F8394A4C7D4 ON oauth_auth_codes');
        $this->addSql('ALTER TABLE oauth_auth_codes DROP device_id');
    }
}
