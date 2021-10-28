<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200918150346 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE geo_zone (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          type VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          code VARCHAR(255) NOT NULL,
          UNIQUE INDEX geo_zone_code_type_unique (code, type),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geo_zone_parent (
          child_id INT UNSIGNED NOT NULL,
          parent_id INT UNSIGNED NOT NULL,
          INDEX IDX_8E49B9DDD62C21B (child_id),
          INDEX IDX_8E49B9D727ACA70 (parent_id),
          PRIMARY KEY(child_id, parent_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geo_foreign_district (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          number SMALLINT NOT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_973BE1F177153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geo_city_district (
          city_id INT UNSIGNED NOT NULL,
          district_id INT UNSIGNED NOT NULL,
          INDEX IDX_5C4191F8BAC62AF (city_id),
          INDEX IDX_5C4191FB08FA272 (district_id),
          PRIMARY KEY(city_id, district_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geo_consular_district (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          foreign_district_id INT UNSIGNED DEFAULT NULL,
          cities LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\',
          number SMALLINT NOT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_BBFC552F77153098 (code),
          INDEX IDX_BBFC552F72D24D35 (foreign_district_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          geo_zone_parent
        ADD
          CONSTRAINT FK_8E49B9DDD62C21B FOREIGN KEY (child_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          geo_zone_parent
        ADD
          CONSTRAINT FK_8E49B9D727ACA70 FOREIGN KEY (parent_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          geo_city_district
        ADD
          CONSTRAINT FK_5C4191F8BAC62AF FOREIGN KEY (city_id) REFERENCES geo_city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          geo_city_district
        ADD
          CONSTRAINT FK_5C4191FB08FA272 FOREIGN KEY (district_id) REFERENCES geo_district (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          geo_consular_district
        ADD
          CONSTRAINT FK_BBFC552F72D24D35 FOREIGN KEY (foreign_district_id) REFERENCES geo_foreign_district (id)');
        $this->addSql('ALTER TABLE geo_district ADD number SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE geo_city DROP FOREIGN KEY FK_297C2D34B08FA272');
        $this->addSql('DROP INDEX IDX_297C2D34B08FA272 ON geo_city');
        $this->addSql('ALTER TABLE geo_city DROP district_id');
        $this->addSql('ALTER TABLE geo_country ADD foreign_district_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_country
        ADD
          CONSTRAINT FK_E465446472D24D35 FOREIGN KEY (foreign_district_id) REFERENCES geo_foreign_district (id)');
        $this->addSql('CREATE INDEX IDX_E465446472D24D35 ON geo_country (foreign_district_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE geo_zone_parent DROP FOREIGN KEY FK_8E49B9DDD62C21B');
        $this->addSql('ALTER TABLE geo_zone_parent DROP FOREIGN KEY FK_8E49B9D727ACA70');
        $this->addSql('ALTER TABLE geo_country DROP FOREIGN KEY FK_E465446472D24D35');
        $this->addSql('ALTER TABLE geo_consular_district DROP FOREIGN KEY FK_BBFC552F72D24D35');
        $this->addSql('DROP TABLE geo_zone');
        $this->addSql('DROP TABLE geo_zone_parent');
        $this->addSql('DROP TABLE geo_foreign_district');
        $this->addSql('DROP TABLE geo_city_district');
        $this->addSql('DROP TABLE geo_consular_district');
        $this->addSql('ALTER TABLE geo_city ADD district_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_city
        ADD
          CONSTRAINT FK_297C2D34B08FA272 FOREIGN KEY (district_id) REFERENCES geo_district (id)');
        $this->addSql('CREATE INDEX IDX_297C2D34B08FA272 ON geo_city (district_id)');
        $this->addSql('DROP INDEX IDX_E465446472D24D35 ON geo_country');
        $this->addSql('ALTER TABLE geo_country DROP foreign_district_id');
        $this->addSql('ALTER TABLE geo_district DROP number');
    }
}
