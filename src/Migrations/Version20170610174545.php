<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170610174545 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE projection_referent_managed_users (
                id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
                status SMALLINT NOT NULL,
                type VARCHAR(20) NOT NULL,
                original_id BIGINT UNSIGNED NOT NULL,
                email VARCHAR(255) NOT NULL,
                postal_code VARCHAR(15) NOT NULL,
                city VARCHAR(255) DEFAULT NULL,
                country VARCHAR(2) DEFAULT NULL,
                first_name VARCHAR(50) DEFAULT NULL,
                last_name VARCHAR(50) DEFAULT NULL,
                age SMALLINT DEFAULT NULL,
                phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\',
                committees LONGTEXT DEFAULT NULL,
                is_committee_member TINYINT(1) NOT NULL,
                is_committee_host TINYINT(1) NOT NULL,
                is_mail_subscriber TINYINT(1) NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX projection_referent_managed_users_search (status, postal_code, country),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE projection_referent_managed_users');
    }
}
