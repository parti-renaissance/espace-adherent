<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240829173637 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chez_vous_cities DROP FOREIGN KEY FK_A42D9BEDAE80F5DF');
        $this->addSql('ALTER TABLE chez_vous_departments DROP FOREIGN KEY FK_29E7DD5798260155');
        $this->addSql('ALTER TABLE chez_vous_markers DROP FOREIGN KEY FK_452F890F8BAC62AF');
        $this->addSql('ALTER TABLE chez_vous_measures DROP FOREIGN KEY FK_E6E8973E8BAC62AF');
        $this->addSql('ALTER TABLE chez_vous_measures DROP FOREIGN KEY FK_E6E8973EC54C8C93');
        $this->addSql('ALTER TABLE timeline_manifesto_translations DROP FOREIGN KEY FK_F7BD6C172C2AC5D3');
        $this->addSql('ALTER TABLE timeline_manifestos DROP FOREIGN KEY FK_C6ED4403EA9FDD75');
        $this->addSql('ALTER TABLE timeline_measure_translations DROP FOREIGN KEY FK_5C9EB6072C2AC5D3');
        $this->addSql('ALTER TABLE timeline_measures DROP FOREIGN KEY FK_BA475ED737E924');
        $this->addSql('ALTER TABLE timeline_measures_profiles DROP FOREIGN KEY FK_B83D81AE5DA37D00');
        $this->addSql('ALTER TABLE timeline_measures_profiles DROP FOREIGN KEY FK_B83D81AECCFA12B8');
        $this->addSql('ALTER TABLE timeline_profile_translations DROP FOREIGN KEY FK_41B3A6DA2C2AC5D3');
        $this->addSql('ALTER TABLE timeline_theme_translations DROP FOREIGN KEY FK_F81F72932C2AC5D3');
        $this->addSql('ALTER TABLE timeline_themes DROP FOREIGN KEY FK_8ADDB8F6EA9FDD75');
        $this->addSql('ALTER TABLE timeline_themes_measures DROP FOREIGN KEY FK_EB8A7B0C59027487');
        $this->addSql('ALTER TABLE timeline_themes_measures DROP FOREIGN KEY FK_EB8A7B0C5DA37D00');
        $this->addSql('DROP TABLE chez_vous_cities');
        $this->addSql('DROP TABLE chez_vous_departments');
        $this->addSql('DROP TABLE chez_vous_markers');
        $this->addSql('DROP TABLE chez_vous_measure_types');
        $this->addSql('DROP TABLE chez_vous_measures');
        $this->addSql('DROP TABLE chez_vous_regions');
        $this->addSql('DROP TABLE timeline_manifesto_translations');
        $this->addSql('DROP TABLE timeline_manifestos');
        $this->addSql('DROP TABLE timeline_measure_translations');
        $this->addSql('DROP TABLE timeline_measures');
        $this->addSql('DROP TABLE timeline_measures_profiles');
        $this->addSql('DROP TABLE timeline_profile_translations');
        $this->addSql('DROP TABLE timeline_profiles');
        $this->addSql('DROP TABLE timeline_theme_translations');
        $this->addSql('DROP TABLE timeline_themes');
        $this->addSql('DROP TABLE timeline_themes_measures');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE chez_vous_cities (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          department_id INT UNSIGNED NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_codes JSON NOT NULL,
          insee_code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          latitude FLOAT (10, 6) NOT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) NOT NULL COMMENT \'(DC2Type:geo_point)\',
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_A42D9BEDAE80F5DF (department_id),
          UNIQUE INDEX UNIQ_A42D9BED15A3C1BC (insee_code),
          UNIQUE INDEX UNIQ_A42D9BED989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE chez_vous_departments (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          region_id INT UNSIGNED NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          label VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_29E7DD5798260155 (region_id),
          UNIQUE INDEX UNIQ_29E7DD5777153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE chez_vous_markers (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          city_id INT UNSIGNED NOT NULL,
          TYPE VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          latitude FLOAT (10, 6) NOT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) NOT NULL COMMENT \'(DC2Type:geo_point)\',
          INDEX IDX_452F890F8BAC62AF (city_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE chez_vous_measure_types (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          updated_at DATETIME NOT NULL,
          source_link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          source_label VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          oldolf_link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          eligibility_link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_B80D46F577153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE chez_vous_measures (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          city_id INT UNSIGNED NOT NULL,
          type_id INT UNSIGNED NOT NULL,
          payload JSON DEFAULT NULL,
          UNIQUE INDEX chez_vous_measures_city_type_unique (city_id, type_id),
          INDEX IDX_E6E8973E8BAC62AF (city_id),
          INDEX IDX_E6E8973EC54C8C93 (type_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE chez_vous_regions (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_A6C12FCC77153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE timeline_manifesto_translations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          translatable_id BIGINT DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          locale VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_F7BD6C172C2AC5D3 (translatable_id),
          UNIQUE INDEX timeline_manifesto_translations_unique_translation (translatable_id, locale),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE timeline_manifestos (
          id BIGINT AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          display_media TINYINT(1) NOT NULL,
          INDEX IDX_C6ED4403EA9FDD75 (media_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE timeline_measure_translations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          translatable_id BIGINT DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          locale VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_5C9EB6072C2AC5D3 (translatable_id),
          UNIQUE INDEX timeline_measure_translations_unique_translation (translatable_id, locale),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE timeline_measures (
          id BIGINT AUTO_INCREMENT NOT NULL,
          manifesto_id BIGINT NOT NULL,
          link VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          STATUS VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          updated_at DATETIME NOT NULL,
          major TINYINT(1) DEFAULT 0 NOT NULL,
          INDEX IDX_BA475ED737E924 (manifesto_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE timeline_measures_profiles (
          measure_id BIGINT NOT NULL,
          profile_id BIGINT NOT NULL,
          INDEX IDX_B83D81AE5DA37D00 (measure_id),
          INDEX IDX_B83D81AECCFA12B8 (profile_id),
          PRIMARY KEY(measure_id, profile_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE timeline_profile_translations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          translatable_id BIGINT DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          locale VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_41B3A6DA2C2AC5D3 (translatable_id),
          UNIQUE INDEX timeline_profile_translations_unique_translation (translatable_id, locale),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE timeline_profiles (
          id BIGINT AUTO_INCREMENT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE timeline_theme_translations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          translatable_id BIGINT DEFAULT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          locale VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_F81F72932C2AC5D3 (translatable_id),
          UNIQUE INDEX timeline_theme_translations_unique_translation (translatable_id, locale),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE timeline_themes (
          id BIGINT AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          featured TINYINT(1) DEFAULT 0 NOT NULL,
          display_media TINYINT(1) NOT NULL,
          INDEX IDX_8ADDB8F6EA9FDD75 (media_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE timeline_themes_measures (
          theme_id BIGINT NOT NULL,
          measure_id BIGINT NOT NULL,
          INDEX IDX_EB8A7B0C59027487 (theme_id),
          INDEX IDX_EB8A7B0C5DA37D00 (measure_id),
          PRIMARY KEY(measure_id, theme_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          chez_vous_cities
        ADD
          CONSTRAINT FK_A42D9BEDAE80F5DF FOREIGN KEY (department_id) REFERENCES chez_vous_departments (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          chez_vous_departments
        ADD
          CONSTRAINT FK_29E7DD5798260155 FOREIGN KEY (region_id) REFERENCES chez_vous_regions (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          chez_vous_markers
        ADD
          CONSTRAINT FK_452F890F8BAC62AF FOREIGN KEY (city_id) REFERENCES chez_vous_cities (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          chez_vous_measures
        ADD
          CONSTRAINT FK_E6E8973E8BAC62AF FOREIGN KEY (city_id) REFERENCES chez_vous_cities (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          chez_vous_measures
        ADD
          CONSTRAINT FK_E6E8973EC54C8C93 FOREIGN KEY (type_id) REFERENCES chez_vous_measure_types (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          timeline_manifesto_translations
        ADD
          CONSTRAINT FK_F7BD6C172C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_manifestos (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          timeline_manifestos
        ADD
          CONSTRAINT FK_C6ED4403EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          timeline_measure_translations
        ADD
          CONSTRAINT FK_5C9EB6072C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_measures (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          timeline_measures
        ADD
          CONSTRAINT FK_BA475ED737E924 FOREIGN KEY (manifesto_id) REFERENCES timeline_manifestos (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          timeline_measures_profiles
        ADD
          CONSTRAINT FK_B83D81AE5DA37D00 FOREIGN KEY (measure_id) REFERENCES timeline_measures (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          timeline_measures_profiles
        ADD
          CONSTRAINT FK_B83D81AECCFA12B8 FOREIGN KEY (profile_id) REFERENCES timeline_profiles (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          timeline_profile_translations
        ADD
          CONSTRAINT FK_41B3A6DA2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_profiles (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          timeline_theme_translations
        ADD
          CONSTRAINT FK_F81F72932C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_themes (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          timeline_themes
        ADD
          CONSTRAINT FK_8ADDB8F6EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          timeline_themes_measures
        ADD
          CONSTRAINT FK_EB8A7B0C59027487 FOREIGN KEY (theme_id) REFERENCES timeline_themes (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          timeline_themes_measures
        ADD
          CONSTRAINT FK_EB8A7B0C5DA37D00 FOREIGN KEY (measure_id) REFERENCES timeline_measures (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
