<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180302170858 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AED1A100B');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574AED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AED1A100B');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574AED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
    }
}
