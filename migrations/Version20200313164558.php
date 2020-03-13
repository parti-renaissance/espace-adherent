<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200313164558 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE election_city_contact (
          id INT AUTO_INCREMENT NOT NULL, 
          city_id INT NOT NULL, 
          first_name VARCHAR(255) NOT NULL, 
          last_name VARCHAR(255) NOT NULL, 
          function VARCHAR(255) DEFAULT NULL, 
          phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\', 
          caller VARCHAR(255) DEFAULT NULL, 
          done TINYINT(1) DEFAULT \'0\' NOT NULL, 
          comment LONGTEXT DEFAULT NULL, 
          INDEX IDX_D04AFB68BAC62AF (city_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE election_city_card (
          id INT AUTO_INCREMENT NOT NULL, 
          city_id INT UNSIGNED NOT NULL, 
          first_candidate_id INT DEFAULT NULL, 
          headquarters_manager_id INT DEFAULT NULL, 
          politic_manager_id INT DEFAULT NULL, 
          task_force_manager_id INT DEFAULT NULL, 
          preparation_prevision_id INT DEFAULT NULL, 
          candidate_prevision_id INT DEFAULT NULL, 
          national_prevision_id INT DEFAULT NULL, 
          population INT DEFAULT NULL, 
          UNIQUE INDEX UNIQ_EB01E8D1E449D110 (first_candidate_id), 
          UNIQUE INDEX UNIQ_EB01E8D1B29FABBC (headquarters_manager_id), 
          UNIQUE INDEX UNIQ_EB01E8D1E4A014FA (politic_manager_id), 
          UNIQUE INDEX UNIQ_EB01E8D1781FEED9 (task_force_manager_id), 
          UNIQUE INDEX UNIQ_EB01E8D15EC54712 (preparation_prevision_id), 
          UNIQUE INDEX UNIQ_EB01E8D1EBF42685 (candidate_prevision_id), 
          UNIQUE INDEX UNIQ_EB01E8D1B86B270B (national_prevision_id), 
          UNIQUE INDEX city_card_city_unique (city_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE election_city_candidate (
          id INT AUTO_INCREMENT NOT NULL, 
          first_name VARCHAR(255) NOT NULL, 
          last_name VARCHAR(255) NOT NULL, 
          gender VARCHAR(6) DEFAULT NULL, 
          email VARCHAR(255) DEFAULT NULL, 
          phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\', 
          political_scheme VARCHAR(255) DEFAULT NULL, 
          alliances VARCHAR(255) DEFAULT NULL, 
          agreement TINYINT(1) DEFAULT \'0\' NOT NULL, 
          eligible_advisers_count INT DEFAULT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE election_city_partner (
          id INT AUTO_INCREMENT NOT NULL, 
          city_id INT NOT NULL, 
          label VARCHAR(255) NOT NULL, 
          consensus VARCHAR(255) DEFAULT NULL, 
          INDEX IDX_704D77988BAC62AF (city_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE election_city_prevision (
          id INT AUTO_INCREMENT NOT NULL, 
          strategy VARCHAR(255) DEFAULT NULL, 
          first_name VARCHAR(255) DEFAULT NULL, 
          last_name VARCHAR(255) DEFAULT NULL, 
          alliances VARCHAR(255) DEFAULT NULL, 
          allies VARCHAR(255) DEFAULT NULL, 
          validated_by VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE election_city_manager (
          id INT AUTO_INCREMENT NOT NULL, 
          first_name VARCHAR(255) NOT NULL, 
          last_name VARCHAR(255) NOT NULL, 
          phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\', 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          code VARCHAR(10) NOT NULL, 
          country VARCHAR(2) NOT NULL, 
          UNIQUE INDEX UNIQ_F62F17677153098 (code), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE department (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          region_id INT UNSIGNED NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          label VARCHAR(100) DEFAULT NULL, 
          code VARCHAR(10) NOT NULL, 
          UNIQUE INDEX UNIQ_CD1DE18A77153098 (code), 
          INDEX IDX_CD1DE18A98260155 (region_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          election_city_contact 
        ADD 
          CONSTRAINT FK_D04AFB68BAC62AF FOREIGN KEY (city_id) REFERENCES election_city_card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D18BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1E449D110 FOREIGN KEY (first_candidate_id) REFERENCES election_city_candidate (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1B29FABBC FOREIGN KEY (headquarters_manager_id) REFERENCES election_city_manager (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1E4A014FA FOREIGN KEY (politic_manager_id) REFERENCES election_city_manager (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1781FEED9 FOREIGN KEY (task_force_manager_id) REFERENCES election_city_manager (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D15EC54712 FOREIGN KEY (preparation_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1EBF42685 FOREIGN KEY (candidate_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1B86B270B FOREIGN KEY (national_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          election_city_partner 
        ADD 
          CONSTRAINT FK_704D77988BAC62AF FOREIGN KEY (city_id) REFERENCES election_city_card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          department 
        ADD 
          CONSTRAINT FK_CD1DE18A98260155 FOREIGN KEY (region_id) REFERENCES region (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cities ADD department_id INT UNSIGNED DEFAULT NULL, DROP country');
        $this->addSql('ALTER TABLE 
          cities 
        ADD 
          CONSTRAINT FK_D95DB16BAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('CREATE INDEX IDX_D95DB16BAE80F5DF ON cities (department_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE election_city_contact DROP FOREIGN KEY FK_D04AFB68BAC62AF');
        $this->addSql('ALTER TABLE election_city_partner DROP FOREIGN KEY FK_704D77988BAC62AF');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1E449D110');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D15EC54712');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1EBF42685');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1B86B270B');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1B29FABBC');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1E4A014FA');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1781FEED9');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A98260155');
        $this->addSql('ALTER TABLE cities DROP FOREIGN KEY FK_D95DB16BAE80F5DF');
        $this->addSql('DROP TABLE election_city_contact');
        $this->addSql('DROP TABLE election_city_card');
        $this->addSql('DROP TABLE election_city_candidate');
        $this->addSql('DROP TABLE election_city_partner');
        $this->addSql('DROP TABLE election_city_prevision');
        $this->addSql('DROP TABLE election_city_manager');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP INDEX IDX_D95DB16BAE80F5DF ON cities');
        $this->addSql('ALTER TABLE cities ADD country VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci, DROP department_id');
    }
}
