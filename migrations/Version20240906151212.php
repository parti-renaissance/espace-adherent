<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240906151212 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE application_request_running_mate DROP FOREIGN KEY FK_D1D6095625F06C53');
        $this->addSql('ALTER TABLE application_request_volunteer DROP FOREIGN KEY FK_1139657025F06C53');
        $this->addSql('ALTER TABLE running_mate_request_application_request_tag DROP FOREIGN KEY FK_9D534FCF9644FEDA');
        $this->addSql('ALTER TABLE running_mate_request_application_request_tag DROP FOREIGN KEY FK_9D534FCFCEDF4387');
        $this->addSql('ALTER TABLE running_mate_request_theme DROP FOREIGN KEY FK_A732622759027487');
        $this->addSql('ALTER TABLE running_mate_request_theme DROP FOREIGN KEY FK_A7326227CEDF4387');
        $this->addSql('ALTER TABLE volunteer_request_application_request_tag DROP FOREIGN KEY FK_6F3FA2699644FEDA');
        $this->addSql('ALTER TABLE volunteer_request_application_request_tag DROP FOREIGN KEY FK_6F3FA269B8D6887');
        $this->addSql('ALTER TABLE volunteer_request_technical_skill DROP FOREIGN KEY FK_7F8C5C1EB8D6887');
        $this->addSql('ALTER TABLE volunteer_request_technical_skill DROP FOREIGN KEY FK_7F8C5C1EE98F0EFD');
        $this->addSql('ALTER TABLE volunteer_request_theme DROP FOREIGN KEY FK_5427AF5359027487');
        $this->addSql('ALTER TABLE volunteer_request_theme DROP FOREIGN KEY FK_5427AF53B8D6887');
        $this->addSql('DROP TABLE application_request_running_mate');
        $this->addSql('DROP TABLE application_request_tag');
        $this->addSql('DROP TABLE application_request_technical_skill');
        $this->addSql('DROP TABLE application_request_theme');
        $this->addSql('DROP TABLE application_request_volunteer');
        $this->addSql('DROP TABLE running_mate_request_application_request_tag');
        $this->addSql('DROP TABLE running_mate_request_theme');
        $this->addSql('DROP TABLE volunteer_request_application_request_tag');
        $this->addSql('DROP TABLE volunteer_request_technical_skill');
        $this->addSql('DROP TABLE volunteer_request_theme');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        DROP
          contact_only_volunteers,
        DROP
          contact_only_running_mates');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE application_request_running_mate (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          curriculum_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_local_association_member TINYINT(1) DEFAULT 0 NOT NULL,
          local_association_domain LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_political_activist TINYINT(1) DEFAULT 0 NOT NULL,
          political_activist_details LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_previous_elected_official TINYINT(1) DEFAULT 0 NOT NULL,
          previous_elected_official_details LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          favorite_theme_details LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          project_details LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          professional_assets LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          favorite_cities LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          email_address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          address VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          country VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          profession VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          custom_favorite_theme LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          gender VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          taken_for_city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          displayed TINYINT(1) DEFAULT 1 NOT NULL,
          UNIQUE INDEX UNIQ_D1D60956D17F50A6 (uuid),
          INDEX IDX_D1D6095625F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE application_request_tag (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE application_request_technical_skill (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          display TINYINT(1) DEFAULT 1 NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE application_request_theme (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          display TINYINT(1) DEFAULT 1 NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE application_request_volunteer (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          custom_technical_skills VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          is_previous_campaign_member TINYINT(1) NOT NULL,
          previous_campaign_details LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          share_associative_commitment TINYINT(1) NOT NULL,
          associative_commitment_details LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          favorite_cities LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          email_address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          address VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          city_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          country VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          profession VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          custom_favorite_theme LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          gender VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          taken_for_city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          displayed TINYINT(1) DEFAULT 1 NOT NULL,
          UNIQUE INDEX UNIQ_11396570D17F50A6 (uuid),
          INDEX IDX_1139657025F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE running_mate_request_application_request_tag (
          running_mate_request_id INT UNSIGNED NOT NULL,
          application_request_tag_id INT NOT NULL,
          INDEX IDX_9D534FCF9644FEDA (application_request_tag_id),
          INDEX IDX_9D534FCFCEDF4387 (running_mate_request_id),
          PRIMARY KEY(
            running_mate_request_id, application_request_tag_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE running_mate_request_theme (
          running_mate_request_id INT UNSIGNED NOT NULL,
          theme_id INT NOT NULL,
          INDEX IDX_A732622759027487 (theme_id),
          INDEX IDX_A7326227CEDF4387 (running_mate_request_id),
          PRIMARY KEY(
            running_mate_request_id, theme_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE volunteer_request_application_request_tag (
          volunteer_request_id INT UNSIGNED NOT NULL,
          application_request_tag_id INT NOT NULL,
          INDEX IDX_6F3FA2699644FEDA (application_request_tag_id),
          INDEX IDX_6F3FA269B8D6887 (volunteer_request_id),
          PRIMARY KEY(
            volunteer_request_id, application_request_tag_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE volunteer_request_technical_skill (
          volunteer_request_id INT UNSIGNED NOT NULL,
          technical_skill_id INT NOT NULL,
          INDEX IDX_7F8C5C1EB8D6887 (volunteer_request_id),
          INDEX IDX_7F8C5C1EE98F0EFD (technical_skill_id),
          PRIMARY KEY(
            volunteer_request_id, technical_skill_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE volunteer_request_theme (
          volunteer_request_id INT UNSIGNED NOT NULL,
          theme_id INT NOT NULL,
          INDEX IDX_5427AF5359027487 (theme_id),
          INDEX IDX_5427AF53B8D6887 (volunteer_request_id),
          PRIMARY KEY(volunteer_request_id, theme_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          application_request_running_mate
        ADD
          CONSTRAINT FK_D1D6095625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          application_request_volunteer
        ADD
          CONSTRAINT FK_1139657025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          running_mate_request_application_request_tag
        ADD
          CONSTRAINT FK_9D534FCF9644FEDA FOREIGN KEY (application_request_tag_id) REFERENCES application_request_tag (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          running_mate_request_application_request_tag
        ADD
          CONSTRAINT FK_9D534FCFCEDF4387 FOREIGN KEY (running_mate_request_id) REFERENCES application_request_running_mate (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          running_mate_request_theme
        ADD
          CONSTRAINT FK_A732622759027487 FOREIGN KEY (theme_id) REFERENCES application_request_theme (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          running_mate_request_theme
        ADD
          CONSTRAINT FK_A7326227CEDF4387 FOREIGN KEY (running_mate_request_id) REFERENCES application_request_running_mate (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_application_request_tag
        ADD
          CONSTRAINT FK_6F3FA2699644FEDA FOREIGN KEY (application_request_tag_id) REFERENCES application_request_tag (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_application_request_tag
        ADD
          CONSTRAINT FK_6F3FA269B8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_technical_skill
        ADD
          CONSTRAINT FK_7F8C5C1EB8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_technical_skill
        ADD
          CONSTRAINT FK_7F8C5C1EE98F0EFD FOREIGN KEY (technical_skill_id) REFERENCES application_request_technical_skill (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_theme
        ADD
          CONSTRAINT FK_5427AF5359027487 FOREIGN KEY (theme_id) REFERENCES application_request_theme (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          volunteer_request_theme
        ADD
          CONSTRAINT FK_5427AF53B8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          contact_only_volunteers TINYINT(1) DEFAULT 0,
        ADD
          contact_only_running_mates TINYINT(1) DEFAULT 0');
    }
}
