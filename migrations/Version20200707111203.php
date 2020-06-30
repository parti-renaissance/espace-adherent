<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200707111203 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE geo_region (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          country_id INT UNSIGNED NOT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_A4B3C80877153098 (code),
          INDEX IDX_A4B3C808F92F3E70 (country_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geo_canton (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          department_id INT UNSIGNED NOT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_F04FC05F77153098 (code),
          INDEX IDX_F04FC05FAE80F5DF (department_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geo_department (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          region_id INT UNSIGNED NOT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_B460660477153098 (code),
          INDEX IDX_B460660498260155 (region_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geo_district (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          department_id INT UNSIGNED NOT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_DF78232677153098 (code),
          INDEX IDX_DF782326AE80F5DF (department_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geo_city (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          department_id INT UNSIGNED DEFAULT NULL,
          canton_id INT UNSIGNED DEFAULT NULL,
          city_community_id INT UNSIGNED DEFAULT NULL,
          district_id INT UNSIGNED DEFAULT NULL,
          postal_code LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
          population INT DEFAULT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_297C2D3477153098 (code),
          INDEX IDX_297C2D34AE80F5DF (department_id),
          INDEX IDX_297C2D348D070D0B (canton_id),
          INDEX IDX_297C2D346D3B1930 (city_community_id),
          INDEX IDX_297C2D34B08FA272 (district_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geo_country (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_E465446477153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geo_city_community (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          department_id INT UNSIGNED NOT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_E5805E0877153098 (code),
          INDEX IDX_E5805E08AE80F5DF (department_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          geo_region
        ADD
          CONSTRAINT FK_A4B3C808F92F3E70 FOREIGN KEY (country_id) REFERENCES geo_country (id)');
        $this->addSql('ALTER TABLE
          geo_canton
        ADD
          CONSTRAINT FK_F04FC05FAE80F5DF FOREIGN KEY (department_id) REFERENCES geo_department (id)');
        $this->addSql('ALTER TABLE
          geo_department
        ADD
          CONSTRAINT FK_B460660498260155 FOREIGN KEY (region_id) REFERENCES geo_region (id)');
        $this->addSql('ALTER TABLE
          geo_district
        ADD
          CONSTRAINT FK_DF782326AE80F5DF FOREIGN KEY (department_id) REFERENCES geo_department (id)');
        $this->addSql('ALTER TABLE
          geo_city
        ADD
          CONSTRAINT FK_297C2D34AE80F5DF FOREIGN KEY (department_id) REFERENCES geo_department (id)');
        $this->addSql('ALTER TABLE
          geo_city
        ADD
          CONSTRAINT FK_297C2D348D070D0B FOREIGN KEY (canton_id) REFERENCES geo_canton (id)');
        $this->addSql('ALTER TABLE
          geo_city
        ADD
          CONSTRAINT FK_297C2D346D3B1930 FOREIGN KEY (city_community_id) REFERENCES geo_city_community (id)');
        $this->addSql('ALTER TABLE
          geo_city
        ADD
          CONSTRAINT FK_297C2D34B08FA272 FOREIGN KEY (district_id) REFERENCES geo_district (id)');
        $this->addSql('ALTER TABLE
          geo_city_community
        ADD
          CONSTRAINT FK_E5805E08AE80F5DF FOREIGN KEY (department_id) REFERENCES geo_department (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE geo_department DROP FOREIGN KEY FK_B460660498260155');
        $this->addSql('ALTER TABLE geo_city DROP FOREIGN KEY FK_297C2D348D070D0B');
        $this->addSql('ALTER TABLE geo_canton DROP FOREIGN KEY FK_F04FC05FAE80F5DF');
        $this->addSql('ALTER TABLE geo_district DROP FOREIGN KEY FK_DF782326AE80F5DF');
        $this->addSql('ALTER TABLE geo_city DROP FOREIGN KEY FK_297C2D34AE80F5DF');
        $this->addSql('ALTER TABLE geo_city_community DROP FOREIGN KEY FK_E5805E08AE80F5DF');
        $this->addSql('ALTER TABLE geo_city DROP FOREIGN KEY FK_297C2D34B08FA272');
        $this->addSql('ALTER TABLE geo_region DROP FOREIGN KEY FK_A4B3C808F92F3E70');
        $this->addSql('ALTER TABLE geo_city DROP FOREIGN KEY FK_297C2D346D3B1930');
        $this->addSql('DROP TABLE geo_region');
        $this->addSql('DROP TABLE geo_canton');
        $this->addSql('DROP TABLE geo_department');
        $this->addSql('DROP TABLE geo_district');
        $this->addSql('DROP TABLE geo_city');
        $this->addSql('DROP TABLE geo_country');
        $this->addSql('DROP TABLE geo_city_community');
    }
}
