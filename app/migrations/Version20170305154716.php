<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170305154716 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('DELETE FROM mailjet_emails');
        $this->addSql('DROP INDEX mailjet_email_uuid ON mailjet_emails');
        $this->addSql('DROP INDEX mailjet_email_message_batch_uuid ON mailjet_emails');
        $this->addSql('ALTER TABLE mailjet_emails ADD recipients LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', ADD updated_at DATETIME NOT NULL, DROP message_batch_uuid, DROP recipient, DROP template, DROP request_payload_checksum, DROP response_payload_checksum, DROP delivered, CHANGE message_class message_class VARCHAR(50) DEFAULT NULL, CHANGE subject sender VARCHAR(100) NOT NULL, CHANGE sent_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mailjet_emails ADD message_batch_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', ADD recipient VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD template VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, ADD request_payload_checksum VARCHAR(40) NOT NULL COLLATE utf8_unicode_ci, ADD response_payload_checksum VARCHAR(40) DEFAULT NULL COLLATE utf8_unicode_ci, ADD delivered TINYINT(1) NOT NULL, ADD sent_at DATETIME NOT NULL, DROP recipients, DROP created_at, DROP updated_at, CHANGE message_class message_class VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE sender subject VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX mailjet_email_uuid ON mailjet_emails (uuid)');
        $this->addSql('CREATE INDEX mailjet_email_message_batch_uuid ON mailjet_emails (message_batch_uuid)');
    }
}
