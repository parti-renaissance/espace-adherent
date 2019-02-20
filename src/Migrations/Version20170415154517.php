<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170415154517 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE facebook_profiles (id INT UNSIGNED AUTO_INCREMENT NOT NULL, facebook_id VARCHAR(30) NOT NULL, name VARCHAR(255) NOT NULL, email_address VARCHAR(255) NOT NULL, gender VARCHAR(30) NOT NULL, age_range LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', created_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX facebook_profile_uuid (uuid), UNIQUE INDEX facebook_profile_facebook_id (facebook_id), UNIQUE INDEX facebook_profile_email_address (email_address), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE facebook_profiles');
    }
}
