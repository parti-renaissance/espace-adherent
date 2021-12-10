<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211208111523 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX pap_campaign_history_uuid_unique ON pap_campaign_history (uuid)');
        $this->addSql('ALTER TABLE pap_address ADD zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_address
        ADD
          CONSTRAINT FK_47071E119F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_47071E119F2C3FAB ON pap_address (zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX pap_campaign_history_uuid_unique ON pap_campaign_history');
        $this->addSql('ALTER TABLE pap_address DROP FOREIGN KEY FK_47071E119F2C3FAB');
        $this->addSql('DROP INDEX IDX_47071E119F2C3FAB ON pap_address');
        $this->addSql('ALTER TABLE
          pap_address
        DROP
          zone_id');
    }
}
