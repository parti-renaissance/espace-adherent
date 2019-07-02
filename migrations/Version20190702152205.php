<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190702152205 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE chez_vous_departments (id INT UNSIGNED AUTO_INCREMENT NOT NULL, region_id INT UNSIGNED NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(10) NOT NULL, UNIQUE INDEX UNIQ_29E7DD5777153098 (code), INDEX IDX_29E7DD5798260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chez_vous_markers (id INT UNSIGNED AUTO_INCREMENT NOT NULL, city_id INT UNSIGNED NOT NULL, type VARCHAR(255) NOT NULL, latitude FLOAT (10,6) NOT NULL COMMENT \'(DC2Type:geo_point)\', longitude FLOAT (10,6) NOT NULL COMMENT \'(DC2Type:geo_point)\', INDEX IDX_452F890F8BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chez_vous_regions (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(10) NOT NULL, UNIQUE INDEX UNIQ_A6C12FCC77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chez_vous_cities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, department_id INT UNSIGNED NOT NULL, name VARCHAR(100) NOT NULL, postal_codes JSON NOT NULL COMMENT \'(DC2Type:json_array)\', insee_code VARCHAR(10) NOT NULL, latitude FLOAT (10,6) NOT NULL COMMENT \'(DC2Type:geo_point)\', longitude FLOAT (10,6) NOT NULL COMMENT \'(DC2Type:geo_point)\', slug VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_A42D9BED15A3C1BC (insee_code), UNIQUE INDEX UNIQ_A42D9BED989D9B62 (slug), INDEX IDX_A42D9BEDAE80F5DF (department_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chez_vous_measures (id INT UNSIGNED AUTO_INCREMENT NOT NULL, city_id INT UNSIGNED NOT NULL, type VARCHAR(255) NOT NULL, payload JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\', INDEX IDX_E6E8973E8BAC62AF (city_id), UNIQUE INDEX chez_vous_measures_city_type_unique (city_id, type), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chez_vous_departments ADD CONSTRAINT FK_29E7DD5798260155 FOREIGN KEY (region_id) REFERENCES chez_vous_regions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chez_vous_markers ADD CONSTRAINT FK_452F890F8BAC62AF FOREIGN KEY (city_id) REFERENCES chez_vous_cities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chez_vous_cities ADD CONSTRAINT FK_A42D9BEDAE80F5DF FOREIGN KEY (department_id) REFERENCES chez_vous_departments (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chez_vous_measures ADD CONSTRAINT FK_E6E8973E8BAC62AF FOREIGN KEY (city_id) REFERENCES chez_vous_cities (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chez_vous_cities DROP FOREIGN KEY FK_A42D9BEDAE80F5DF');
        $this->addSql('ALTER TABLE chez_vous_departments DROP FOREIGN KEY FK_29E7DD5798260155');
        $this->addSql('ALTER TABLE chez_vous_markers DROP FOREIGN KEY FK_452F890F8BAC62AF');
        $this->addSql('ALTER TABLE chez_vous_measures DROP FOREIGN KEY FK_E6E8973E8BAC62AF');
        $this->addSql('DROP TABLE chez_vous_departments');
        $this->addSql('DROP TABLE chez_vous_markers');
        $this->addSql('DROP TABLE chez_vous_regions');
        $this->addSql('DROP TABLE chez_vous_cities');
        $this->addSql('DROP TABLE chez_vous_measures');
    }
}
