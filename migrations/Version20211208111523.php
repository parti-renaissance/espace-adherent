<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211208111523 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX pap_campaign_history_uuid_unique ON pap_campaign_history (uuid)');
        $this->addSql('ALTER TABLE pap_address ADD dpt_code VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX pap_campaign_history_uuid_unique ON pap_campaign_history');
        $this->addSql('ALTER TABLE pap_address DROP dpt_code');
    }
}
