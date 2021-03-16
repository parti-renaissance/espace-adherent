<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170620164351 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE member_summary_job_experiences (id INT AUTO_INCREMENT NOT NULL, summary_id INT DEFAULT NULL, company VARCHAR(255) NOT NULL, position VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, website VARCHAR(255) DEFAULT NULL, company_facebook_page VARCHAR(255) DEFAULT NULL, company_twitter_nickname VARCHAR(255) DEFAULT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, on_going TINYINT(1) DEFAULT \'0\' NOT NULL, contract VARCHAR(255) NOT NULL, duration VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, display_order SMALLINT DEFAULT 1 NOT NULL, INDEX IDX_72DD8B7F2AC2D45C (summary_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_summary_languages (id INT AUTO_INCREMENT NOT NULL, summary_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, level VARCHAR(255) NOT NULL, INDEX IDX_70C88322AC2D45C (summary_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_summary_mission_types (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_summary_skills (id INT AUTO_INCREMENT NOT NULL, summary_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, INDEX IDX_CB3F6F8F2AC2D45C (summary_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_summary_trainings (id INT AUTO_INCREMENT NOT NULL, summary_id INT DEFAULT NULL, organization VARCHAR(255) NOT NULL, diploma VARCHAR(255) NOT NULL, study_field VARCHAR(255) NOT NULL, started_at DATE NOT NULL, ended_at DATE NOT NULL, on_going TINYINT(1) DEFAULT \'0\' NOT NULL, description LONGTEXT DEFAULT NULL, extracurricular LONGTEXT DEFAULT NULL, display_order SMALLINT DEFAULT 1 NOT NULL, INDEX IDX_C101987B2AC2D45C (summary_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE summaries (id INT AUTO_INCREMENT NOT NULL, member_id INT UNSIGNED DEFAULT NULL, slug VARCHAR(255) NOT NULL, current_profession VARCHAR(255) DEFAULT NULL, contribution_wish VARCHAR(255) NOT NULL, availability VARCHAR(255) NOT NULL, job_location VARCHAR(255) NOT NULL, professional_synopsis LONGTEXT NOT NULL, motivation LONGTEXT NOT NULL, contact_email VARCHAR(255) NOT NULL, linked_in_url VARCHAR(255) DEFAULT NULL, website_url VARCHAR(255) DEFAULT NULL, facebook_url VARCHAR(255) DEFAULT NULL, twitter_nickname VARCHAR(255) DEFAULT NULL, viadeo_url VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_66783CCA7597D3FE (member_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE summary_mission_type_wishes (summary_id INT NOT NULL, mission_type_id INT NOT NULL, INDEX IDX_7F3FC70F2AC2D45C (summary_id), INDEX IDX_7F3FC70F547018DE (mission_type_id), PRIMARY KEY(summary_id, mission_type_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE member_summary_job_experiences ADD CONSTRAINT FK_72DD8B7F2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id)');
        $this->addSql('ALTER TABLE member_summary_languages ADD CONSTRAINT FK_70C88322AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id)');
        $this->addSql('ALTER TABLE member_summary_skills ADD CONSTRAINT FK_CB3F6F8F2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id)');
        $this->addSql('ALTER TABLE member_summary_trainings ADD CONSTRAINT FK_C101987B2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id)');
        $this->addSql('ALTER TABLE summaries ADD CONSTRAINT FK_66783CCA7597D3FE FOREIGN KEY (member_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE summary_mission_type_wishes ADD CONSTRAINT FK_7F3FC70F2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id)');
        $this->addSql('ALTER TABLE summary_mission_type_wishes ADD CONSTRAINT FK_7F3FC70F547018DE FOREIGN KEY (mission_type_id) REFERENCES member_summary_mission_types (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE summary_mission_type_wishes DROP FOREIGN KEY FK_7F3FC70F547018DE');
        $this->addSql('ALTER TABLE member_summary_job_experiences DROP FOREIGN KEY FK_72DD8B7F2AC2D45C');
        $this->addSql('ALTER TABLE member_summary_languages DROP FOREIGN KEY FK_70C88322AC2D45C');
        $this->addSql('ALTER TABLE member_summary_skills DROP FOREIGN KEY FK_CB3F6F8F2AC2D45C');
        $this->addSql('ALTER TABLE member_summary_trainings DROP FOREIGN KEY FK_C101987B2AC2D45C');
        $this->addSql('ALTER TABLE summary_mission_type_wishes DROP FOREIGN KEY FK_7F3FC70F2AC2D45C');
        $this->addSql('DROP TABLE member_summary_job_experiences');
        $this->addSql('DROP TABLE member_summary_languages');
        $this->addSql('DROP TABLE member_summary_mission_types');
        $this->addSql('DROP TABLE member_summary_skills');
        $this->addSql('DROP TABLE member_summary_trainings');
        $this->addSql('DROP TABLE summaries');
        $this->addSql('DROP TABLE summary_mission_type_wishes');
    }
}
