<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211118172844 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_campaign_history DROP FOREIGN KEY FK_5A3F26F74D2A7E12');
        $this->addSql('ALTER TABLE pap_campaign_history DROP FOREIGN KEY FK_5A3F26F7F639F774');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F74D2A7E12 FOREIGN KEY (building_id) REFERENCES pap_building (id)');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F7F639F774 FOREIGN KEY (campaign_id) REFERENCES pap_campaign (id)');
        $this->addSql('ALTER TABLE phoning_campaign ADD participants_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE phoning_campaign_history DROP FOREIGN KEY FK_EC191198F639F774');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC191198F639F774 FOREIGN KEY (campaign_id) REFERENCES phoning_campaign (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_campaign_history DROP FOREIGN KEY FK_5A3F26F7F639F774');
        $this->addSql('ALTER TABLE pap_campaign_history DROP FOREIGN KEY FK_5A3F26F74D2A7E12');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F7F639F774 FOREIGN KEY (campaign_id) REFERENCES pap_campaign (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F74D2A7E12 FOREIGN KEY (building_id) REFERENCES pap_building (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phoning_campaign DROP participants_count');
        $this->addSql('ALTER TABLE phoning_campaign_history DROP FOREIGN KEY FK_EC191198F639F774');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC191198F639F774 FOREIGN KEY (campaign_id) REFERENCES phoning_campaign (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
