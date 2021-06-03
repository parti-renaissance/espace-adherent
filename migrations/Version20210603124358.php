<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210603124358 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_project_category_skills DROP FOREIGN KEY FK_168C868A12469DE2');
        $this->addSql('ALTER TABLE citizen_projects DROP FOREIGN KEY FK_651490212469DE2');
        $this->addSql('ALTER TABLE turnkey_projects DROP FOREIGN KEY FK_CB66CFAE12469DE2');
        $this->addSql('ALTER TABLE citizen_project_category_skills DROP FOREIGN KEY FK_168C868A5585C142');
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9EA64A9D0');
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94B3584533');
        $this->addSql('ALTER TABLE citizen_project_committee_supports DROP FOREIGN KEY FK_F694C3BCB3584533');
        $this->addSql('ALTER TABLE citizen_project_memberships DROP FOREIGN KEY FK_2E41816B3584533');
        $this->addSql('ALTER TABLE citizen_project_referent_tag DROP FOREIGN KEY FK_73ED204AB3584533');
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9B3584533');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AB3584533');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745B3584533');
        $this->addSql('ALTER TABLE citizen_projects DROP FOREIGN KEY FK_6514902B5315DF4');
        $this->addSql('ALTER TABLE turnkey_project_turnkey_project_file DROP FOREIGN KEY FK_67BF8377B5315DF4');
        $this->addSql('ALTER TABLE turnkey_project_turnkey_project_file DROP FOREIGN KEY FK_67BF83777D06E1CD');

        $this->addSql('DROP TABLE citizen_action_categories');
        $this->addSql('DROP TABLE citizen_project_categories');
        $this->addSql('DROP TABLE citizen_project_category_skills');
        $this->addSql('DROP TABLE citizen_project_committee_supports');
        $this->addSql('DROP TABLE citizen_project_memberships');
        $this->addSql('DROP TABLE citizen_project_referent_tag');
        $this->addSql('DROP TABLE citizen_project_skills');
        $this->addSql('DROP TABLE citizen_projects');
        $this->addSql('DROP TABLE citizen_projects_skills');
        $this->addSql('DROP TABLE turnkey_project_turnkey_project_file');
        $this->addSql('DROP TABLE turnkey_projects');
        $this->addSql('DROP TABLE turnkey_projects_files');
        $this->addSql('DROP INDEX IDX_28CA9F94B3584533 ON adherent_message_filters');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        DROP
          citizen_project_id,
        DROP
          include_citizen_project_hosts');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA37034326B');
        $this->addSql('DROP INDEX UNIQ_562C7DA37034326B ON adherents');
        $this->addSql('ALTER TABLE adherents DROP coordinator_citizen_project_area_id');
        $this->addSql('ALTER TABLE chez_vous_measure_types DROP citizen_projects_link');
        $this->addSql('DROP INDEX IDX_5387574AB3584533 ON events');
        $this->addSql('ALTER TABLE events DROP citizen_project_id');
        $this->addSql('ALTER TABLE projection_managed_users DROP citizen_projects, DROP citizen_projects_organizer');
        $this->addSql('ALTER TABLE referent_managed_users_message DROP include_cp');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745A2DD3412');
        $this->addSql('DROP INDEX IDX_F11FA745B3584533 ON reports');
        $this->addSql('DROP INDEX IDX_F11FA745A2DD3412 ON reports');
        $this->addSql('ALTER TABLE reports DROP citizen_project_id, DROP citizen_action_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE citizen_action_categories (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          status VARCHAR(10) CHARACTER SET utf8 DEFAULT \'ENABLED\' NOT NULL COLLATE `utf8_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          UNIQUE INDEX citizen_action_category_slug_unique (slug),
          UNIQUE INDEX citizen_action_category_name_unique (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE citizen_project_categories (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          status VARCHAR(10) CHARACTER SET utf8 DEFAULT \'ENABLED\' NOT NULL COLLATE `utf8_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          UNIQUE INDEX citizen_project_category_slug_unique (slug),
          UNIQUE INDEX citizen_project_category_name_unique (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE citizen_project_category_skills (
          id INT AUTO_INCREMENT NOT NULL,
          category_id INT UNSIGNED DEFAULT NULL,
          skill_id INT DEFAULT NULL,
          promotion TINYINT(1) DEFAULT \'0\' NOT NULL,
          INDEX IDX_168C868A5585C142 (skill_id),
          INDEX IDX_168C868A12469DE2 (category_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE citizen_project_committee_supports (
          id INT AUTO_INCREMENT NOT NULL,
          citizen_project_id INT UNSIGNED DEFAULT NULL,
          committee_id INT UNSIGNED DEFAULT NULL,
          status VARCHAR(20) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          requested_at DATETIME DEFAULT NULL,
          approved_at DATETIME DEFAULT NULL,
          INDEX IDX_F694C3BCB3584533 (citizen_project_id),
          INDEX IDX_F694C3BCED1A100B (committee_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE citizen_project_memberships (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          citizen_project_id INT UNSIGNED NOT NULL,
          privilege VARCHAR(15) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          joined_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX adherent_has_joined_citizen_project (adherent_id, citizen_project_id),
          INDEX IDX_2E4181625F06C53 (adherent_id),
          INDEX IDX_2E41816B3584533 (citizen_project_id),
          INDEX citizen_project_memberships_role_idx (privilege),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE citizen_project_referent_tag (
          citizen_project_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_73ED204A9C262DB3 (referent_tag_id),
          INDEX IDX_73ED204AB3584533 (citizen_project_id),
          PRIMARY KEY(
            citizen_project_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE citizen_project_skills (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          UNIQUE INDEX citizen_project_skill_slug_unique (slug),
          UNIQUE INDEX citizen_project_skill_name_unique (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE citizen_projects (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          category_id INT UNSIGNED DEFAULT NULL,
          turnkey_project_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          canonical_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          status VARCHAR(20) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          approved_at DATETIME DEFAULT NULL,
          refused_at DATETIME DEFAULT NULL,
          created_by CHAR(36) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          phone VARCHAR(35) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          members_count SMALLINT UNSIGNED NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          address_address VARCHAR(150) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          address_postal_code VARCHAR(15) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          address_city_insee VARCHAR(15) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          address_city_name VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          address_country VARCHAR(2) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          subtitle VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          problem_description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          proposed_solution LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          required_means LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          image_uploaded TINYINT(1) DEFAULT \'0\' NOT NULL,
          matched_skills TINYINT(1) DEFAULT \'0\' NOT NULL,
          featured TINYINT(1) DEFAULT \'0\' NOT NULL,
          admin_comment LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          image_name VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          district VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          address_region VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          mailchimp_id INT DEFAULT NULL,
          address_geocodable_hash VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          UNIQUE INDEX citizen_project_uuid_unique (uuid),
          INDEX citizen_project_status_idx (status),
          INDEX IDX_6514902B5315DF4 (turnkey_project_id),
          UNIQUE INDEX citizen_project_slug_unique (slug),
          INDEX IDX_651490212469DE2 (category_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE citizen_projects_skills (
          citizen_project_id INT UNSIGNED NOT NULL,
          citizen_project_skill_id INT NOT NULL,
          INDEX IDX_B3D202D9EA64A9D0 (citizen_project_skill_id),
          INDEX IDX_B3D202D9B3584533 (citizen_project_id),
          PRIMARY KEY(
            citizen_project_id, citizen_project_skill_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE turnkey_project_turnkey_project_file (
          turnkey_project_id INT UNSIGNED NOT NULL,
          turnkey_project_file_id INT UNSIGNED NOT NULL,
          INDEX IDX_67BF8377B5315DF4 (turnkey_project_id),
          INDEX IDX_67BF83777D06E1CD (turnkey_project_file_id),
          PRIMARY KEY(
            turnkey_project_id, turnkey_project_file_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE turnkey_projects (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          category_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          canonical_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          subtitle VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          problem_description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          proposed_solution LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          image_name VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          youtube_id VARCHAR(11) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          is_pinned TINYINT(1) DEFAULT \'0\' NOT NULL,
          is_favorite TINYINT(1) DEFAULT \'0\' NOT NULL,
          position SMALLINT DEFAULT 1 NOT NULL,
          is_approved TINYINT(1) DEFAULT \'0\' NOT NULL,
          UNIQUE INDEX turnkey_project_slug_unique (slug),
          UNIQUE INDEX turnkey_project_canonical_name_unique (canonical_name),
          INDEX IDX_CB66CFAE12469DE2 (category_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE turnkey_projects_files (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          title VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          path VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          extension VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          UNIQUE INDEX turnkey_projects_file_slug_extension (slug, extension),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          citizen_project_category_skills
        ADD
          CONSTRAINT FK_168C868A12469DE2 FOREIGN KEY (category_id) REFERENCES citizen_project_categories (id)');
        $this->addSql('ALTER TABLE
          citizen_project_category_skills
        ADD
          CONSTRAINT FK_168C868A5585C142 FOREIGN KEY (skill_id) REFERENCES citizen_project_skills (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          citizen_project_committee_supports
        ADD
          CONSTRAINT FK_F694C3BCB3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('ALTER TABLE
          citizen_project_committee_supports
        ADD
          CONSTRAINT FK_F694C3BCED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE
          citizen_project_memberships
        ADD
          CONSTRAINT FK_2E4181625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          citizen_project_memberships
        ADD
          CONSTRAINT FK_2E41816B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('ALTER TABLE
          citizen_project_referent_tag
        ADD
          CONSTRAINT FK_73ED204A9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          citizen_project_referent_tag
        ADD
          CONSTRAINT FK_73ED204AB3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          citizen_projects
        ADD
          CONSTRAINT FK_651490212469DE2 FOREIGN KEY (category_id) REFERENCES citizen_project_categories (id)');
        $this->addSql('ALTER TABLE
          citizen_projects
        ADD
          CONSTRAINT FK_6514902B5315DF4 FOREIGN KEY (turnkey_project_id) REFERENCES turnkey_projects (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          citizen_projects_skills
        ADD
          CONSTRAINT FK_B3D202D9B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          citizen_projects_skills
        ADD
          CONSTRAINT FK_B3D202D9EA64A9D0 FOREIGN KEY (citizen_project_skill_id) REFERENCES citizen_project_skills (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          turnkey_project_turnkey_project_file
        ADD
          CONSTRAINT FK_67BF83777D06E1CD FOREIGN KEY (turnkey_project_file_id) REFERENCES turnkey_projects_files (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          turnkey_project_turnkey_project_file
        ADD
          CONSTRAINT FK_67BF8377B5315DF4 FOREIGN KEY (turnkey_project_id) REFERENCES turnkey_projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          turnkey_projects
        ADD
          CONSTRAINT FK_CB66CFAE12469DE2 FOREIGN KEY (category_id) REFERENCES citizen_project_categories (id)');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          citizen_project_id INT UNSIGNED DEFAULT NULL,
        ADD
          include_citizen_project_hosts TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F94B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('CREATE INDEX IDX_28CA9F94B3584533 ON adherent_message_filters (citizen_project_id)');
        $this->addSql('ALTER TABLE adherents ADD coordinator_citizen_project_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA37034326B FOREIGN KEY (
            coordinator_citizen_project_area_id
          ) REFERENCES coordinator_managed_areas (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA37034326B ON adherents (coordinator_citizen_project_area_id)');
        $this->addSql('ALTER TABLE
          chez_vous_measure_types
        ADD
          citizen_projects_link VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE events ADD citizen_project_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574AB3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('CREATE INDEX IDX_5387574AB3584533 ON events (citizen_project_id)');
        $this->addSql('ALTER TABLE
          projection_managed_users
        ADD
          citizen_projects JSON DEFAULT NULL,
        ADD
          citizen_projects_organizer JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE referent_managed_users_message ADD include_cp TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE
          reports
        ADD
          citizen_project_id INT UNSIGNED DEFAULT NULL,
        ADD
          citizen_action_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          reports
        ADD
          CONSTRAINT FK_F11FA745A2DD3412 FOREIGN KEY (citizen_action_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE
          reports
        ADD
          CONSTRAINT FK_F11FA745B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('CREATE INDEX IDX_F11FA745B3584533 ON reports (citizen_project_id)');
        $this->addSql('CREATE INDEX IDX_F11FA745A2DD3412 ON reports (citizen_action_id)');
    }
}
