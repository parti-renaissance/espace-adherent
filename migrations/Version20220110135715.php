<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220110135715 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_campaign_history ADD begin_at DATETIME DEFAULT NULL');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE pap_campaign_history SET begin_at = created_at');
        $this->connection->executeQuery('ALTER TABLE pap_campaign_history MODIFY begin_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_campaign_history DROP begin_at');
    }
}
