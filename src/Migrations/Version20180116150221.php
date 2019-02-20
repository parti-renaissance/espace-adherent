<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180116150221 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE failed_login_attempt (id INT UNSIGNED AUTO_INCREMENT NOT NULL, signature VARCHAR(255) NOT NULL, at DATETIME NOT NULL, extra JSON NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE failed_login_attempt');
    }
}
