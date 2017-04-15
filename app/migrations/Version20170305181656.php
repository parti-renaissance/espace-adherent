<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170305181656 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE mailjet_logs (id INT UNSIGNED AUTO_INCREMENT NOT NULL, message_class VARCHAR(50) DEFAULT NULL, sender VARCHAR(100) NOT NULL, recipients LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', request_payload LONGTEXT NOT NULL, response_payload LONGTEXT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE mailjet_emails');
    }

    public function down(Schema $schema)
    {
        $this->addSql('CREATE TABLE mailjet_emails (id INT UNSIGNED AUTO_INCREMENT NOT NULL, message_batch_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', subject VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, recipient VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, template VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, message_class VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, request_payload LONGTEXT NOT NULL COLLATE utf8_unicode_ci, request_payload_checksum VARCHAR(40) NOT NULL COLLATE utf8_unicode_ci, response_payload LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, response_payload_checksum VARCHAR(40) DEFAULT NULL COLLATE utf8_unicode_ci, delivered TINYINT(1) NOT NULL, sent_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX mailjet_email_uuid (uuid), INDEX mailjet_email_message_batch_uuid (message_batch_uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE mailjet_logs');
    }
}
