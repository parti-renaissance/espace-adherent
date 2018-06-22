<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180622092742 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE emails ADD app VARCHAR(32) NOT NULL, ADD last_failed_message LONGTEXT DEFAULT NULL, ADD last_failed_date DATETIME DEFAULT NULL');

        $this->addSql('CREATE INDEX emails_message_class_idx ON emails (message_class)');
        $this->addSql('CREATE INDEX emails_sender_idx ON emails (sender)');

        $this->addSql("UPDATE emails SET app='EM' WHERE message_class!='Message'");
        $this->addSql("UPDATE emails SET app='Unknown' WHERE message_class='Message'");
        $this->addSql('CREATE INDEX emails_app_idx ON emails (app)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX emails_app_idx ON emails');
        $this->addSql('DROP INDEX emails_message_class_idx ON emails');
        $this->addSql('DROP INDEX emails_sender_idx ON emails');
        $this->addSql('ALTER TABLE emails DROP app, DROP last_failed_message, DROP last_failed_date');
    }
}
