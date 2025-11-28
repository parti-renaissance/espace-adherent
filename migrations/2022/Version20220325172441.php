<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220325172441 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          created_by_adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          CONSTRAINT FK_EF50C8E885C9D733 FOREIGN KEY (created_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          CONSTRAINT FK_EF50C8E8DF6CFDC9 FOREIGN KEY (updated_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_EF50C8E885C9D733 ON pap_campaign (created_by_adherent_id)');
        $this->addSql('CREATE INDEX IDX_EF50C8E8DF6CFDC9 ON pap_campaign (updated_by_adherent_id)');
        $this->addSql('CREATE TABLE pap_campaign_zone (
          campaign_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_E3C93B78F639F774 (campaign_id),
          INDEX IDX_E3C93B789F2C3FAB (zone_id),
          PRIMARY KEY(campaign_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          pap_campaign_zone
        ADD
          CONSTRAINT FK_A10CFBE5F639F774 FOREIGN KEY (campaign_id) REFERENCES pap_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_campaign_zone
        ADD
          CONSTRAINT FK_A10CFBE59F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pap_campaign DROP FOREIGN KEY FK_EF50C8E89F2C3FAB');
        $this->addSql('DROP INDEX IDX_EF50C8E89F2C3FAB ON pap_campaign');
        $this->addSql('ALTER TABLE pap_campaign DROP zone_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_campaign DROP FOREIGN KEY FK_EF50C8E885C9D733');
        $this->addSql('ALTER TABLE pap_campaign DROP FOREIGN KEY FK_EF50C8E8DF6CFDC9');
        $this->addSql('DROP INDEX IDX_EF50C8E885C9D733 ON pap_campaign');
        $this->addSql('DROP INDEX IDX_EF50C8E8DF6CFDC9 ON pap_campaign');
        $this->addSql('ALTER TABLE pap_campaign DROP created_by_adherent_id, DROP updated_by_adherent_id');
        $this->addSql('DROP TABLE pap_campaign_zone');
        $this->addSql('ALTER TABLE pap_campaign ADD zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          CONSTRAINT FK_EF50C8E89F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_EF50C8E89F2C3FAB ON pap_campaign (zone_id)');
    }
}
