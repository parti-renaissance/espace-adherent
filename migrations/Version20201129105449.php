<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201129105449 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
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
        $this->addSql('ALTER TABLE oauth_access_tokens DROP FOREIGN KEY FK_CA42527C94A4C7D4');
        $this->addSql('DROP INDEX IDX_CA42527C94A4C7D4 ON oauth_access_tokens');
        $this->addSql('ALTER TABLE oauth_access_tokens DROP device_id');
        $this->addSql('ALTER TABLE oauth_auth_codes DROP FOREIGN KEY FK_BB493F8394A4C7D4');
        $this->addSql('DROP INDEX IDX_BB493F8394A4C7D4 ON oauth_auth_codes');
        $this->addSql('ALTER TABLE oauth_auth_codes DROP device_id');
    }
}
