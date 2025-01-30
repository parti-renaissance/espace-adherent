<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230123015605 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE voting_platform_election SET notifications_sent = notifications_sent | 1');
        $this->addSql('ALTER TABLE designation DROP notifications_sent');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation ADD notifications_sent INT DEFAULT 0 NOT NULL');
    }
}
