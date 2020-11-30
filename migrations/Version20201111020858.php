<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201111020858 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          oauth_refresh_tokens CHANGE expires_at expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE 
          oauth_auth_codes CHANGE expires_at expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE 
          oauth_access_tokens CHANGE expires_at expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oauth_access_tokens CHANGE expires_at expires_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE oauth_auth_codes CHANGE expires_at expires_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE oauth_refresh_tokens CHANGE expires_at expires_at DATETIME NOT NULL');
    }
}
