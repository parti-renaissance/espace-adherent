<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180921151801 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE biography_executive_office_member (
                id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                job VARCHAR(255) NOT NULL, 
                executive_officer TINYINT(1) DEFAULT \'0\' NOT NULL, 
                first_name VARCHAR(50) NOT NULL, 
                last_name VARCHAR(50) NOT NULL, 
                slug VARCHAR(255) NOT NULL, 
                published TINYINT(1) DEFAULT \'0\' NOT NULL, 
                description VARCHAR(255) DEFAULT NULL, 
                content VARCHAR(800) DEFAULT NULL, 
                facebook_profile VARCHAR(255) DEFAULT NULL, 
                twitter_profile VARCHAR(255) DEFAULT NULL, 
                instagram_profile VARCHAR(255) DEFAULT NULL, 
                linked_in_profile VARCHAR(255) DEFAULT NULL, 
                image_name VARCHAR(255) DEFAULT NULL, 
                uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
                created_at DATETIME NOT NULL, 
                updated_at DATETIME NOT NULL, 
                UNIQUE INDEX executive_office_member_uuid_unique (uuid), 
                UNIQUE INDEX executive_office_member_slug_unique (slug), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE biography_executive_office_member');
    }
}
