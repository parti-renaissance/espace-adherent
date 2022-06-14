<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220614100027 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE republican_silence_zone (
          republican_silence_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_9197540D12359909 (republican_silence_id),
          INDEX IDX_9197540D9F2C3FAB (zone_id),
          PRIMARY KEY(republican_silence_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          republican_silence_zone
        ADD
          CONSTRAINT FK_9197540D12359909 FOREIGN KEY (republican_silence_id) REFERENCES republican_silence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          republican_silence_zone
        ADD
          CONSTRAINT FK_9197540D9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE republican_silence_zone');
    }
}
