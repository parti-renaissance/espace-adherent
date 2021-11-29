<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211129160710 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX phoning_campaign_uuid_unique ON phoning_campaign (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX phoning_campaign_uuid_unique ON phoning_campaign');
    }
}
