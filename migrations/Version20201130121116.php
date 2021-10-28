<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201130121116 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE projection_managed_users_zone (
          managed_user_id BIGINT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_E4D4ADCDC679DD78 (managed_user_id),
          INDEX IDX_E4D4ADCD9F2C3FAB (zone_id),
          PRIMARY KEY(managed_user_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          projection_managed_users_zone
        ADD
          CONSTRAINT FK_E4D4ADCDC679DD78 FOREIGN KEY (managed_user_id) REFERENCES projection_managed_users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          projection_managed_users_zone
        ADD
          CONSTRAINT FK_E4D4ADCD9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE projection_managed_users DROP FOREIGN KEY FK_90A7D6569F2C3FAB');
        $this->addSql('DROP INDEX IDX_90A7D6569F2C3FAB ON projection_managed_users');
        $this->addSql('ALTER TABLE projection_managed_users DROP zone_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE projection_managed_users_zone');
        $this->addSql('ALTER TABLE projection_managed_users ADD zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          projection_managed_users
        ADD
          CONSTRAINT FK_90A7D6569F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_90A7D6569F2C3FAB ON projection_managed_users (zone_id)');
    }
}
