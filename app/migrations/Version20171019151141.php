<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171019151141 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('DROP TABLE adherent_reset_password_tokens');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('CREATE TABLE adherent_reset_password_tokens (id INT UNSIGNED AUTO_INCREMENT NOT NULL, adherent_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', value VARCHAR(40) NOT NULL COLLATE utf8_unicode_ci, created_at DATETIME NOT NULL, expired_at DATETIME NOT NULL, used_at DATETIME DEFAULT NULL, uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX adherent_reset_password_token_unique (value), UNIQUE INDEX adherent_reset_password_token_account_unique (value, adherent_uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }
}
