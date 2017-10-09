<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171205111014 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('DROP TABLE adherent_reset_password_tokens');
        $this->addSql('ALTER TABLE events CHANGE address_country address_country VARCHAR(2) DEFAULT NULL, CHANGE expert_found expert_found TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE adherents ADD adherent TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE gender gender VARCHAR(6) DEFAULT NULL, CHANGE position position VARCHAR(20) DEFAULT NULL, CHANGE address_country address_country VARCHAR(2) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('CREATE TABLE adherent_reset_password_tokens (id INT UNSIGNED AUTO_INCREMENT NOT NULL, adherent_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', value VARCHAR(40) NOT NULL COLLATE utf8_unicode_ci, created_at DATETIME NOT NULL, expired_at DATETIME NOT NULL, used_at DATETIME DEFAULT NULL, uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX adherent_reset_password_token_unique (value), UNIQUE INDEX adherent_reset_password_token_account_unique (value, adherent_uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adherents DROP adherent, CHANGE gender gender VARCHAR(6) NOT NULL COLLATE utf8_unicode_ci, CHANGE position position VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci, CHANGE address_country address_country VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE events CHANGE address_country address_country VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci, CHANGE expert_found expert_found TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
