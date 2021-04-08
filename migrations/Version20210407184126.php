<?php

namespace Migrations;

use App\Entity\Event\BaseEvent;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210407184126 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events ADD mode VARCHAR(255) DEFAULT NULL');
        $this->addSql(sprintf("UPDATE events SET mode = '%s'", BaseEvent::MODE_MEETING));
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP mode');
    }
}
