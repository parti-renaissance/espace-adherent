<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180110151141 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE oauth_clients (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, redirect_uris JSON NOT NULL, secret VARCHAR(255) NOT NULL, allowed_grant_types LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', supported_scopes LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', ask_user_for_authorization TINYINT(1) DEFAULT \'1\' NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX oauth_clients_uuid_unique (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_access_tokens (id INT UNSIGNED AUTO_INCREMENT NOT NULL, client_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED DEFAULT NULL, identifier VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL, revoked_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, scopes JSON NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_CA42527C19EB6921 (client_id), INDEX IDX_CA42527CA76ED395 (user_id), UNIQUE INDEX oauth_access_tokens_uuid_unique (uuid), UNIQUE INDEX oauth_access_tokens_identifier_unique (identifier), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_auth_codes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, client_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED DEFAULT NULL, identifier VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL, revoked_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, scopes JSON NOT NULL, redirect_uri LONGTEXT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_BB493F8319EB6921 (client_id), INDEX IDX_BB493F83A76ED395 (user_id), UNIQUE INDEX oauth_auth_codes_uuid_unique (uuid), UNIQUE INDEX oauth_auth_codes_identifier_unique (identifier), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_authorizations (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, client_id INT UNSIGNED DEFAULT NULL, scopes JSON NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_40448230A76ED395 (user_id), INDEX IDX_4044823019EB6921 (client_id), UNIQUE INDEX user_authorizations_unique (user_id, client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_refresh_tokens (id INT UNSIGNED AUTO_INCREMENT NOT NULL, access_token_id INT UNSIGNED DEFAULT NULL, identifier VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL, revoked_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_5AB6872CCB2688 (access_token_id), UNIQUE INDEX oauth_refresh_tokens_uuid_unique (uuid), UNIQUE INDEX oauth_refresh_tokens_identifier_unique (identifier), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE oauth_access_tokens ADD CONSTRAINT FK_CA42527C19EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)');
        $this->addSql('ALTER TABLE oauth_access_tokens ADD CONSTRAINT FK_CA42527CA76ED395 FOREIGN KEY (user_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_auth_codes ADD CONSTRAINT FK_BB493F8319EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)');
        $this->addSql('ALTER TABLE oauth_auth_codes ADD CONSTRAINT FK_BB493F83A76ED395 FOREIGN KEY (user_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_authorizations ADD CONSTRAINT FK_40448230A76ED395 FOREIGN KEY (user_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_authorizations ADD CONSTRAINT FK_4044823019EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE oauth_refresh_tokens ADD CONSTRAINT FK_5AB6872CCB2688 FOREIGN KEY (access_token_id) REFERENCES oauth_access_tokens (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oauth_access_tokens DROP FOREIGN KEY FK_CA42527C19EB6921');
        $this->addSql('ALTER TABLE oauth_auth_codes DROP FOREIGN KEY FK_BB493F8319EB6921');
        $this->addSql('ALTER TABLE user_authorizations DROP FOREIGN KEY FK_4044823019EB6921');
        $this->addSql('ALTER TABLE oauth_refresh_tokens DROP FOREIGN KEY FK_5AB6872CCB2688');
        $this->addSql('DROP TABLE oauth_clients');
        $this->addSql('DROP TABLE oauth_access_tokens');
        $this->addSql('DROP TABLE oauth_auth_codes');
        $this->addSql('DROP TABLE user_authorizations');
        $this->addSql('DROP TABLE oauth_refresh_tokens');
    }
}
