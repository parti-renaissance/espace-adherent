<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240306174317 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event ADD text_ticket_email LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE national_event_inscription ADD ticket_sent_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event DROP text_ticket_email');
        $this->addSql('ALTER TABLE national_event_inscription DROP ticket_sent_at');
    }
}
