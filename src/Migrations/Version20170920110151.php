<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170920110151 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events_invitations DROP FOREIGN KEY FK_B94D5AAD71F7E88B');
        $this->addSql('ALTER TABLE events_invitations ADD CONSTRAINT FK_B94D5AAD71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events_invitations DROP FOREIGN KEY FK_B94D5AAD71F7E88B');
        $this->addSql('ALTER TABLE events_invitations ADD CONSTRAINT FK_B94D5AAD71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
    }
}
