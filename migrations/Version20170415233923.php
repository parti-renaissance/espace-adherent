<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170415233923 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE facebook_videos (id INT AUTO_INCREMENT NOT NULL, facebook_url VARCHAR(255) NOT NULL, twitter_url VARCHAR(255) DEFAULT NULL, description VARCHAR(255) NOT NULL, author VARCHAR(100) NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, published TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE facebook_videos');
    }
}
