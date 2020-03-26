<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200319171717 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elected_representative_zone_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE elected_representative_zone (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_C52FC4A712469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE elected_representative_zone ADD CONSTRAINT FK_C52FC4A712469DE2 FOREIGN KEY (category_id) REFERENCES elected_representative_zone_category (id)');
        $this->addSql('ALTER TABLE elected_representative_mandate ADD zone_id INT DEFAULT NULL, DROP geographical_area');
        $this->addSql('ALTER TABLE elected_representative_mandate ADD CONSTRAINT FK_386091469F2C3FAB FOREIGN KEY (zone_id) REFERENCES elected_representative_zone (id)');
        $this->addSql('CREATE INDEX IDX_386091469F2C3FAB ON elected_representative_mandate (zone_id)');
        $this->addSql('CREATE UNIQUE INDEX elected_representative_zone_name_category_unique ON elected_representative_zone (name, category_id)');
        $this->addSql('CREATE UNIQUE INDEX elected_representative_zone_category_name_unique ON elected_representative_zone_category (name)');
        $this->addSql('CREATE TABLE epci (id INT UNSIGNED AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, surface DOUBLE PRECISION NOT NULL, department_code VARCHAR(10) NOT NULL, department_name VARCHAR(255) NOT NULL, region_code VARCHAR(10) NOT NULL, region_name VARCHAR(255) NOT NULL, city_insee VARCHAR(10) NOT NULL, city_code VARCHAR(10) NOT NULL, city_name VARCHAR(255) NOT NULL, city_full_name VARCHAR(255) NOT NULL, city_dep VARCHAR(255) NOT NULL, city_siren VARCHAR(255) NOT NULL, code_arr VARCHAR(255) NOT NULL, code_cant VARCHAR(255) NOT NULL, population INT UNSIGNED DEFAULT NULL, epci_dep VARCHAR(255) NOT NULL, epci_siren VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, insee VARCHAR(255) NOT NULL, fiscal VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_zone DROP FOREIGN KEY FK_C52FC4A712469DE2');
        $this->addSql('ALTER TABLE elected_representative_mandate DROP FOREIGN KEY FK_386091469F2C3FAB');
        $this->addSql('DROP INDEX elected_representative_zone_name_category_unique ON elected_representative_zone');
        $this->addSql('DROP INDEX elected_representative_zone_category_name_unique ON elected_representative_zone_category');
        $this->addSql('DROP TABLE elected_representative_zone_category');
        $this->addSql('DROP TABLE elected_representative_zone');
        $this->addSql('DROP INDEX IDX_386091469F2C3FAB ON elected_representative_mandate');
        $this->addSql('ALTER TABLE elected_representative_mandate ADD geographical_area VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP zone_id');
        $this->addSql('DROP TABLE epci');
    }
}
