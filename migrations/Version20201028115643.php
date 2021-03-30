<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201028115643 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_zone (adherent_id INT UNSIGNED NOT NULL, zone_id INT UNSIGNED NOT NULL, INDEX IDX_1C14D08525F06C53 (adherent_id), INDEX IDX_1C14D0859F2C3FAB (zone_id), PRIMARY KEY(adherent_id, zone_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adherent_zone ADD CONSTRAINT FK_1C14D08525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherent_zone ADD CONSTRAINT FK_1C14D0859F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE adherent_zone');
    }
}
