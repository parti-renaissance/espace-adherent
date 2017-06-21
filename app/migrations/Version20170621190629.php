<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170621190629 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('DROP TABLE mailjet_logs');
    }

    public function down(Schema $schema)
    {
        $this->addSql('CREATE TABLE mailjet_logs (id INT UNSIGNED AUTO_INCREMENT NOT NULL, message_class VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, sender VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, recipients LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\', request_payload LONGTEXT NOT NULL COLLATE utf8_unicode_ci, response_payload LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }
}
