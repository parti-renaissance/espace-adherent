<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210708133208 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE summary_mission_type_wishes DROP FOREIGN KEY FK_7F3FC70F547018DE');
        $this->addSql('ALTER TABLE summary_skills DROP FOREIGN KEY FK_2FD2B63C5585C142');
        $this->addSql('ALTER TABLE member_summary_job_experiences DROP FOREIGN KEY FK_72DD8B7F2AC2D45C');
        $this->addSql('ALTER TABLE member_summary_languages DROP FOREIGN KEY FK_70C88322AC2D45C');
        $this->addSql('ALTER TABLE member_summary_trainings DROP FOREIGN KEY FK_C101987B2AC2D45C');
        $this->addSql('ALTER TABLE summary_mission_type_wishes DROP FOREIGN KEY FK_7F3FC70F2AC2D45C');
        $this->addSql('ALTER TABLE summary_skills DROP FOREIGN KEY FK_2FD2B63C2AC2D45C');
        $this->addSql('DROP TABLE member_summary_job_experiences');
        $this->addSql('DROP TABLE member_summary_languages');
        $this->addSql('DROP TABLE member_summary_mission_types');
        $this->addSql('DROP TABLE member_summary_trainings');
        $this->addSql('DROP TABLE skills');
        $this->addSql('DROP TABLE summaries');
        $this->addSql('DROP TABLE summary_mission_type_wishes');
        $this->addSql('DROP TABLE summary_skills');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE member_summary_job_experiences (
          id INT AUTO_INCREMENT NOT NULL,
          summary_id INT DEFAULT NULL,
          company VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          position VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          location VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          website VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          company_facebook_page VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          company_twitter_nickname VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          started_at DATE NOT NULL,
          ended_at DATE DEFAULT NULL,
          on_going TINYINT(1) DEFAULT \'0\' NOT NULL,
          contract VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          duration VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          display_order SMALLINT DEFAULT 1 NOT NULL,
          INDEX IDX_72DD8B7F2AC2D45C (summary_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE member_summary_languages (
          id INT AUTO_INCREMENT NOT NULL,
          summary_id INT DEFAULT NULL,
          code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          level VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_70C88322AC2D45C (summary_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE member_summary_mission_types (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX member_summary_mission_type_name_unique (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE member_summary_trainings (
          id INT AUTO_INCREMENT NOT NULL,
          summary_id INT DEFAULT NULL,
          organization VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          diploma VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          study_field VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          started_at DATE NOT NULL,
          ended_at DATE DEFAULT NULL,
          on_going TINYINT(1) DEFAULT \'0\' NOT NULL,
          description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          extra_curricular LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          display_order SMALLINT DEFAULT 1 NOT NULL,
          INDEX IDX_C101987B2AC2D45C (summary_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE skills (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX skill_slug_unique (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE summaries (
          id INT AUTO_INCREMENT NOT NULL,
          member_id INT UNSIGNED DEFAULT NULL,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          current_profession VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          contribution_wish VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          professional_synopsis LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          motivation LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          contact_email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          linked_in_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          website_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          facebook_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_nickname VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          viadeo_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          availabilities LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          job_locations LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          public TINYINT(1) DEFAULT \'0\' NOT NULL,
          showing_recent_activities TINYINT(1) NOT NULL,
          picture_uploaded TINYINT(1) DEFAULT \'0\' NOT NULL,
          UNIQUE INDEX UNIQ_66783CCA7597D3FE (member_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE summary_mission_type_wishes (
          summary_id INT NOT NULL,
          mission_type_id INT NOT NULL,
          INDEX IDX_7F3FC70F547018DE (mission_type_id),
          INDEX IDX_7F3FC70F2AC2D45C (summary_id),
          PRIMARY KEY(summary_id, mission_type_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE summary_skills (
          summary_id INT NOT NULL,
          skill_id INT NOT NULL,
          INDEX IDX_2FD2B63C5585C142 (skill_id),
          INDEX IDX_2FD2B63C2AC2D45C (summary_id),
          PRIMARY KEY(summary_id, skill_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          member_summary_job_experiences
        ADD
          CONSTRAINT FK_72DD8B7F2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id)');
        $this->addSql('ALTER TABLE
          member_summary_languages
        ADD
          CONSTRAINT FK_70C88322AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id)');
        $this->addSql('ALTER TABLE
          member_summary_trainings
        ADD
          CONSTRAINT FK_C101987B2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id)');
        $this->addSql('ALTER TABLE
          summaries
        ADD
          CONSTRAINT FK_66783CCA7597D3FE FOREIGN KEY (member_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          summary_mission_type_wishes
        ADD
          CONSTRAINT FK_7F3FC70F2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id)');
        $this->addSql('ALTER TABLE
          summary_mission_type_wishes
        ADD
          CONSTRAINT FK_7F3FC70F547018DE FOREIGN KEY (mission_type_id) REFERENCES member_summary_mission_types (id)');
        $this->addSql('ALTER TABLE
          summary_skills
        ADD
          CONSTRAINT FK_2FD2B63C2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          summary_skills
        ADD
          CONSTRAINT FK_2FD2B63C5585C142 FOREIGN KEY (skill_id) REFERENCES skills (id) ON DELETE CASCADE');
    }
}
