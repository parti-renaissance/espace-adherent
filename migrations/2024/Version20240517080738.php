<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240517080738 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          national_event
        ADD
          subject_ticket_email VARCHAR(255) DEFAULT NULL,
        ADD
          image_ticket_email LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE national_event_inscription ADD ticket_qrcode_file VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event DROP subject_ticket_email, DROP image_ticket_email');
        $this->addSql('ALTER TABLE national_event_inscription DROP ticket_qrcode_file');
    }
}
