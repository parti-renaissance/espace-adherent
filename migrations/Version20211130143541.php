<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211130143541 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          voter_city_id INT UNSIGNED DEFAULT NULL,
        ADD
          voter_status VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F772054F0F FOREIGN KEY (voter_city_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_5A3F26F772054F0F ON pap_campaign_history (voter_city_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_campaign_history DROP FOREIGN KEY FK_5A3F26F772054F0F');
        $this->addSql('DROP INDEX IDX_5A3F26F772054F0F ON pap_campaign_history');
        $this->addSql('ALTER TABLE pap_campaign_history DROP voter_city_id, DROP voter_status');
    }
}
