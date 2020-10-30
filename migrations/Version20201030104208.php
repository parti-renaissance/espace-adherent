<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201030104208 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE candidate_managed_area (id INT UNSIGNED AUTO_INCREMENT NOT NULL, zone_id INT UNSIGNED NOT NULL, INDEX IDX_C604D2EA9F2C3FAB (zone_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE candidate_managed_area ADD CONSTRAINT FK_C604D2EA9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE adherents ADD candidate_managed_area_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE adherents ADD CONSTRAINT FK_562C7DA37657F304 FOREIGN KEY (candidate_managed_area_id) REFERENCES candidate_managed_area (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA37657F304 ON adherents (candidate_managed_area_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA37657F304');
        $this->addSql('DROP TABLE candidate_managed_area');
        $this->addSql('DROP INDEX UNIQ_562C7DA37657F304 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP candidate_managed_area_id');
    }
}
