<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190701001806 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE oldolf_departments (id INT UNSIGNED AUTO_INCREMENT NOT NULL, region_id INT UNSIGNED NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(10) NOT NULL, UNIQUE INDEX UNIQ_506C0C9677153098 (code), INDEX IDX_506C0C9698260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oldolf_markers (id INT UNSIGNED AUTO_INCREMENT NOT NULL, city_id INT UNSIGNED NOT NULL, type VARCHAR(255) NOT NULL, latitude FLOAT (10,6) NOT NULL COMMENT \'(DC2Type:geo_point)\', longitude FLOAT (10,6) NOT NULL COMMENT \'(DC2Type:geo_point)\', INDEX IDX_C382E9C08BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oldolf_regions (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(10) NOT NULL, UNIQUE INDEX UNIQ_206C4F0377153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oldolf_cities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, department_id INT UNSIGNED NOT NULL, name VARCHAR(100) NOT NULL, postal_codes JSON NOT NULL COMMENT \'(DC2Type:json_array)\', insee_code VARCHAR(10) NOT NULL, latitude FLOAT (10,6) NOT NULL COMMENT \'(DC2Type:geo_point)\', longitude FLOAT (10,6) NOT NULL COMMENT \'(DC2Type:geo_point)\', slug VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_DA9F803D15A3C1BC (insee_code), UNIQUE INDEX UNIQ_DA9F803D989D9B62 (slug), INDEX IDX_DA9F803DAE80F5DF (department_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oldolf_measures (id INT UNSIGNED AUTO_INCREMENT NOT NULL, city_id INT UNSIGNED NOT NULL, type VARCHAR(255) NOT NULL, payload JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\', INDEX IDX_EDB5E57F8BAC62AF (city_id), UNIQUE INDEX oldolf_measures_city_type_unique (city_id, type), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE oldolf_departments ADD CONSTRAINT FK_506C0C9698260155 FOREIGN KEY (region_id) REFERENCES oldolf_regions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oldolf_markers ADD CONSTRAINT FK_C382E9C08BAC62AF FOREIGN KEY (city_id) REFERENCES oldolf_cities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oldolf_cities ADD CONSTRAINT FK_DA9F803DAE80F5DF FOREIGN KEY (department_id) REFERENCES oldolf_departments (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oldolf_measures ADD CONSTRAINT FK_EDB5E57F8BAC62AF FOREIGN KEY (city_id) REFERENCES oldolf_cities (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oldolf_cities DROP FOREIGN KEY FK_DA9F803DAE80F5DF');
        $this->addSql('ALTER TABLE oldolf_departments DROP FOREIGN KEY FK_506C0C9698260155');
        $this->addSql('ALTER TABLE oldolf_markers DROP FOREIGN KEY FK_C382E9C08BAC62AF');
        $this->addSql('ALTER TABLE oldolf_measures DROP FOREIGN KEY FK_EDB5E57F8BAC62AF');
        $this->addSql('DROP TABLE oldolf_departments');
        $this->addSql('DROP TABLE oldolf_markers');
        $this->addSql('DROP TABLE oldolf_regions');
        $this->addSql('DROP TABLE oldolf_cities');
        $this->addSql('DROP TABLE oldolf_measures');
    }
}
