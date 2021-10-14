<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211014173306 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mailchimp_campaign DROP FOREIGN KEY FK_CFABD309537A1329');
        $this->addSql('ALTER TABLE
          mailchimp_campaign
        ADD
          CONSTRAINT FK_CFABD309537A1329 FOREIGN KEY (message_id) REFERENCES adherent_messages (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mailchimp_campaign DROP FOREIGN KEY FK_CFABD309537A1329');
        $this->addSql('ALTER TABLE
          mailchimp_campaign
        ADD
          CONSTRAINT FK_CFABD309537A1329 FOREIGN KEY (message_id) REFERENCES adherent_messages (id)');
    }
}
