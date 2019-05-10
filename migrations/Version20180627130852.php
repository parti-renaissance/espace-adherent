<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180627130852 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE adherent_change_email_token (
                id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                adherent_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
                value VARCHAR(40) NOT NULL, 
                created_at DATETIME NOT NULL, 
                expired_at DATETIME NOT NULL, 
                used_at DATETIME DEFAULT NULL,
                email VARCHAR(255) NOT NULL, 
                uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
                INDEX IDX_6F8B4B5AE7927C7477241BAC253ECC4 (email, used_at, expired_at), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE adherent_change_email_token');
    }
}
