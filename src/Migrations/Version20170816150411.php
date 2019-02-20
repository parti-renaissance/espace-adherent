<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170816150411 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_feed_item CHANGE item_type item_type VARCHAR(18) NOT NULL;');
        $this->addSql('ALTER TABLE mailjet_emails CHANGE message_class message_class VARCHAR(55) DEFAULT NULL;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_feed_item CHANGE item_type item_type VARCHAR(15) NOT NULL;');
        $this->addSql('ALTER TABLE mailjet_emails CHANGE message_class message_class VARCHAR(50) DEFAULT NULL;');
    }
}
