<?php

namespace Migrations;

use App\Scope\ScopeVisibilityEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220110122036 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          zone_id INT UNSIGNED DEFAULT NULL,
        ADD
          visibility VARCHAR(30) DEFAULT NULL');
        $this->addSql('UPDATE pap_campaign SET visibility = :visibility', ['visibility' => ScopeVisibilityEnum::NATIONAL]);
        $this->addSql('ALTER TABLE pap_campaign CHANGE visibility visibility VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          CONSTRAINT FK_EF50C8E89F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_EF50C8E89F2C3FAB ON pap_campaign (zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_campaign DROP FOREIGN KEY FK_EF50C8E89F2C3FAB');
        $this->addSql('DROP INDEX IDX_EF50C8E89F2C3FAB ON pap_campaign');
        $this->addSql('ALTER TABLE pap_campaign DROP zone_id, DROP visibility');
    }
}
