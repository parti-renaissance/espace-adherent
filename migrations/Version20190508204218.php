<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190508204218 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          procuration_managed_area_id INT DEFAULT NULL, 
        ADD 
          assessor_managed_area_id INT DEFAULT NULL');

        $this->addSql(
            'UPDATE adherents AS a
            INNER JOIN assessor_managed_areas AS m ON m.adherent_id = a.id
            SET a.assessor_managed_area_id = m.id'
        );

        $this->addSql(
            'UPDATE adherents AS a
            INNER JOIN procuration_managed_areas AS m ON m.adherent_id = a.id
            SET a.procuration_managed_area_id = m.id'
        );

        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA339054338 FOREIGN KEY (procuration_managed_area_id) REFERENCES procuration_managed_areas (id)');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3E1B55931 FOREIGN KEY (assessor_managed_area_id) REFERENCES assessor_managed_areas (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA339054338 ON adherents (procuration_managed_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3E1B55931 ON adherents (assessor_managed_area_id)');
        $this->addSql('ALTER TABLE assessor_managed_areas DROP FOREIGN KEY FK_9D55225025F06C53');
        $this->addSql('DROP INDEX UNIQ_9D55225025F06C53 ON assessor_managed_areas');
        $this->addSql('ALTER TABLE assessor_managed_areas DROP adherent_id');
        $this->addSql('ALTER TABLE procuration_managed_areas DROP FOREIGN KEY FK_117496A025F06C53');
        $this->addSql('DROP INDEX UNIQ_117496A025F06C53 ON procuration_managed_areas');
        $this->addSql('ALTER TABLE procuration_managed_areas DROP adherent_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA339054338');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3E1B55931');
        $this->addSql('DROP INDEX UNIQ_562C7DA339054338 ON adherents');
        $this->addSql('DROP INDEX UNIQ_562C7DA3E1B55931 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP procuration_managed_area_id, DROP assessor_managed_area_id');
        $this->addSql('ALTER TABLE assessor_managed_areas ADD adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          assessor_managed_areas 
        ADD 
          CONSTRAINT FK_9D55225025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9D55225025F06C53 ON assessor_managed_areas (adherent_id)');
        $this->addSql('ALTER TABLE procuration_managed_areas ADD adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          procuration_managed_areas 
        ADD 
          CONSTRAINT FK_117496A025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_117496A025F06C53 ON procuration_managed_areas (adherent_id)');
    }
}
