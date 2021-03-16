<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201127155445 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_survey ADD zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_survey ADD CONSTRAINT FK_EC4948E59F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_EC4948E59F2C3FAB ON jecoute_survey (zone_id)');
        $this->addSql('ALTER TABLE jecoute_managed_areas ADD zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_managed_areas ADD CONSTRAINT FK_DF8531749F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_DF8531749F2C3FAB ON jecoute_managed_areas (zone_id)');
        $this->addSql('ALTER TABLE jecoute_survey ADD blocked_changes TINYINT(1) DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_survey DROP FOREIGN KEY FK_EC4948E59F2C3FAB');
        $this->addSql('DROP INDEX IDX_EC4948E59F2C3FAB ON jecoute_survey');
        $this->addSql('ALTER TABLE jecoute_survey DROP zone_id');
        $this->addSql('ALTER TABLE jecoute_managed_areas DROP FOREIGN KEY FK_DF8531749F2C3FAB');
        $this->addSql('DROP INDEX IDX_DF8531749F2C3FAB ON jecoute_managed_areas');
        $this->addSql('ALTER TABLE jecoute_managed_areas DROP zone_id');
        $this->addSql('ALTER TABLE jecoute_survey DROP blocked_changes');
    }
}
