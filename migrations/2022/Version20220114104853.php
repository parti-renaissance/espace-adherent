<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220114104853 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pap_address_zone (
          address_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_AAFFE1C5F5B7AF75 (address_id),
          INDEX IDX_AAFFE1C59F2C3FAB (zone_id),
          PRIMARY KEY(address_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          pap_address_zone
        ADD
          CONSTRAINT FK_AAFFE1C5F5B7AF75 FOREIGN KEY (address_id) REFERENCES pap_address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_address_zone
        ADD
          CONSTRAINT FK_AAFFE1C59F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pap_address DROP FOREIGN KEY FK_47071E119F2C3FAB');
        $this->addSql('DROP INDEX IDX_47071E119F2C3FAB ON pap_address');
        $this->addSql('ALTER TABLE pap_address DROP zone_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE pap_address_zone');
        $this->addSql('ALTER TABLE pap_address ADD zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_address
        ADD
          CONSTRAINT FK_47071E119F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_47071E119F2C3FAB ON pap_address (zone_id)');
    }
}
