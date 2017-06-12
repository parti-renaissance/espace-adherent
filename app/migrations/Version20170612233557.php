<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170612233557 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE mailjet_emails (id INT UNSIGNED AUTO_INCREMENT NOT NULL, message_class VARCHAR(50) DEFAULT NULL, sender VARCHAR(100) NOT NULL, recipients LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', request_payload LONGTEXT NOT NULL, response_payload LONGTEXT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE legislative_candidates CHANGE status status VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE mailjet_emails');
        $this->addSql('ALTER TABLE legislative_candidates CHANGE status status VARCHAR(20) DEFAULT \'none\' NOT NULL COLLATE utf8_unicode_ci');
    }
}
