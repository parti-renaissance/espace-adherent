<?php

namespace Migrations;

use App\Coordinator\CoordinatorAreaSectors;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180911093716 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD coordinator_citizen_project_area_id INT DEFAULT NULL, ADD coordinator_committee_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adherents ADD CONSTRAINT FK_562C7DA37034326B FOREIGN KEY (coordinator_citizen_project_area_id) REFERENCES coordinator_managed_areas (id)');
        $this->addSql('ALTER TABLE adherents ADD CONSTRAINT FK_562C7DA31A912B27 FOREIGN KEY (coordinator_committee_area_id) REFERENCES coordinator_managed_areas (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA37034326B ON adherents (coordinator_citizen_project_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA31A912B27 ON adherents (coordinator_committee_area_id)');

        $this->addSql(
            'UPDATE adherents AS a
            INNER JOIN coordinator_managed_areas AS c ON c.adherent_id = a.id AND c.sector = :sector
            SET a.coordinator_citizen_project_area_id = c.id',
            ['sector' => CoordinatorAreaSectors::CITIZEN_PROJECT_SECTOR]
        );

        $this->addSql(
            'UPDATE adherents AS a
            INNER JOIN coordinator_managed_areas AS c ON c.adherent_id = a.id AND c.sector = :sector
            SET a.coordinator_committee_area_id = c.id',
            ['sector' => CoordinatorAreaSectors::COMMITTEE_SECTOR]
        );

        $this->addSql('ALTER TABLE coordinator_managed_areas DROP FOREIGN KEY FK_C20973D25F06C53');
        $this->addSql('DROP INDEX IDX_C20973D25F06C53 ON coordinator_managed_areas');
        $this->addSql('ALTER TABLE coordinator_managed_areas DROP adherent_id, CHANGE sector sector VARCHAR(255) NOT NULL, CHANGE codes codes LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA37034326B');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA31A912B27');
        $this->addSql('DROP INDEX UNIQ_562C7DA37034326B ON adherents');
        $this->addSql('DROP INDEX UNIQ_562C7DA31A912B27 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP coordinator_citizen_project_area_id, DROP coordinator_committee_area_id');

        $this->addSql('ALTER TABLE coordinator_managed_areas ADD adherent_id INT UNSIGNED DEFAULT NULL, CHANGE sector sector VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE codes codes LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE coordinator_managed_areas ADD CONSTRAINT FK_C20973D25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C20973D25F06C53 ON coordinator_managed_areas (adherent_id)');
    }
}
