<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220104015613 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_zone_based_role (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          type VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_390E4D3825F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE adherent_zone_based_role_zone (
          adherent_zone_based_role_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_1FB630BEE566D6E (adherent_zone_based_role_id),
          INDEX IDX_1FB630B9F2C3FAB (zone_id),
          PRIMARY KEY(
            adherent_zone_based_role_id, zone_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          adherent_zone_based_role
        ADD
          CONSTRAINT FK_390E4D3825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_zone_based_role_zone
        ADD
          CONSTRAINT FK_1FB630BEE566D6E FOREIGN KEY (adherent_zone_based_role_id) REFERENCES adherent_zone_based_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_zone_based_role_zone
        ADD
          CONSTRAINT FK_1FB630B9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_zone_based_role_zone DROP FOREIGN KEY FK_1FB630BEE566D6E');
        $this->addSql('DROP TABLE adherent_zone_based_role');
        $this->addSql('DROP TABLE adherent_zone_based_role_zone');
    }
}
