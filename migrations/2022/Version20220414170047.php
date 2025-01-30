<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220414170047 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6FB4E7B4D2A7E12F639F774 ON pap_building_statistics (building_id, campaign_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_B6FB4E7B4D2A7E12F639F774 ON pap_building_statistics');
    }
}
