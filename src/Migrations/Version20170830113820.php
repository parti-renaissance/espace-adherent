<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170830113820 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_feed_item DROP FOREIGN KEY FK_4F1CDC8071F7E88B');
        $this->addSql('ALTER TABLE committee_feed_item ADD CONSTRAINT FK_4F1CDC8071F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_feed_item DROP FOREIGN KEY FK_4F1CDC8071F7E88B');
        $this->addSql('ALTER TABLE committee_feed_item ADD CONSTRAINT FK_4F1CDC8071F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE SET NULL');
    }
}
