<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250912144715 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE administrator_zone (
          administrator_id INT NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_2961ACEA4B09E92C (administrator_id),
          INDEX IDX_2961ACEA9F2C3FAB (zone_id),
          PRIMARY KEY(administrator_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          administrator_zone
        ADD
          CONSTRAINT FK_2961ACEA4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          administrator_zone
        ADD
          CONSTRAINT FK_2961ACEA9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE administrator_zone DROP FOREIGN KEY FK_2961ACEA4B09E92C');
        $this->addSql('ALTER TABLE administrator_zone DROP FOREIGN KEY FK_2961ACEA9F2C3FAB');
        $this->addSql('DROP TABLE administrator_zone');
    }
}
