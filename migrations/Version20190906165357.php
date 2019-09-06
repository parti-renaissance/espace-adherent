<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190906165357 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('TRUNCATE chez_vous_measures');
        $this->addSql('CREATE TABLE chez_vous_measure_types (id INT UNSIGNED AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, updated_at DATETIME NOT NULL, source_link VARCHAR(255) DEFAULT NULL, source_label VARCHAR(255) DEFAULT NULL, oldolf_link VARCHAR(255) DEFAULT NULL, eligibility_link VARCHAR(255) DEFAULT NULL, citizen_projects_link VARCHAR(255) DEFAULT NULL, ideas_workshop_link VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_B80D46F577153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP INDEX chez_vous_measures_city_type_unique ON chez_vous_measures');
        $this->addSql('ALTER TABLE chez_vous_measures ADD type_id INT UNSIGNED NOT NULL, DROP type');
        $this->addSql('ALTER TABLE chez_vous_measures ADD CONSTRAINT FK_E6E8973EC54C8C93 FOREIGN KEY (type_id) REFERENCES chez_vous_measure_types (id)');
        $this->addSql('CREATE INDEX IDX_E6E8973EC54C8C93 ON chez_vous_measures (type_id)');
        $this->addSql('CREATE UNIQUE INDEX chez_vous_measures_city_type_unique ON chez_vous_measures (city_id, type_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('TRUNCATE chez_vous_measures');
        $this->addSql('ALTER TABLE chez_vous_measures DROP FOREIGN KEY FK_E6E8973EC54C8C93');
        $this->addSql('DROP TABLE chez_vous_measure_types');
        $this->addSql('DROP INDEX IDX_E6E8973EC54C8C93 ON chez_vous_measures');
        $this->addSql('DROP INDEX chez_vous_measures_city_type_unique ON chez_vous_measures');
        $this->addSql('ALTER TABLE chez_vous_measures ADD type VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP type_id');
        $this->addSql('CREATE UNIQUE INDEX chez_vous_measures_city_type_unique ON chez_vous_measures (city_id, type)');
    }
}
