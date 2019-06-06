<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190524115905 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE application_request_theme (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE application_request_technical_skill (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE application_request_volunteer (
            id INT UNSIGNED AUTO_INCREMENT NOT NULL,
            custom_technical_skills VARCHAR(255) DEFAULT NULL,
            is_previous_campaign_member TINYINT(1) NOT NULL,
            previous_campaign_details LONGTEXT DEFAULT NULL,
            share_associative_commitment TINYINT(1) NOT NULL,
            associative_commitment_details LONGTEXT DEFAULT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            favorite_cities JSON NOT NULL COMMENT \'(DC2Type:json_array)\',
            email_address VARCHAR(255) NOT NULL,
            address VARCHAR(150) NOT NULL,
            postal_code VARCHAR(15) DEFAULT NULL,
            city VARCHAR(50) NOT NULL,
            country VARCHAR(2) NOT NULL,
            phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\',
            profession VARCHAR(255) NOT NULL,
            custom_favorite_theme LONGTEXT DEFAULT NULL,
            uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE volunteer_request_technical_skill (volunteer_request_id INT UNSIGNED NOT NULL, technical_skill_id INT NOT NULL, INDEX IDX_7F8C5C1EB8D6887 (volunteer_request_id), INDEX IDX_7F8C5C1EE98F0EFD (technical_skill_id), PRIMARY KEY(volunteer_request_id, technical_skill_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE volunteer_request_theme (volunteer_request_id INT UNSIGNED NOT NULL, theme_id INT NOT NULL, INDEX IDX_5427AF53B8D6887 (volunteer_request_id), INDEX IDX_5427AF5359027487 (theme_id), PRIMARY KEY(volunteer_request_id, theme_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE application_request_running_mate (
            id INT UNSIGNED AUTO_INCREMENT NOT NULL,
            curriculum_name VARCHAR(255) NOT NULL,
            is_local_association_member TINYINT(1) NOT NULL,
            local_association_domain LONGTEXT NOT NULL,
            is_political_activist TINYINT(1) NOT NULL,
            political_activist_details LONGTEXT NOT NULL,
            is_previous_elected_official TINYINT(1) NOT NULL,
            previous_elected_official_details LONGTEXT NOT NULL,
            favorite_theme_details LONGTEXT NOT NULL,
            project_details LONGTEXT NOT NULL,
            professional_assets LONGTEXT NOT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            favorite_cities JSON NOT NULL COMMENT \'(DC2Type:json_array)\',
            email_address VARCHAR(255) NOT NULL,
            address VARCHAR(150) NOT NULL,
            postal_code VARCHAR(15) DEFAULT NULL,
            city VARCHAR(50) NOT NULL,
            country VARCHAR(2) NOT NULL,
            phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\',
            profession VARCHAR(255) NOT NULL,
            custom_favorite_theme LONGTEXT DEFAULT NULL,
            uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE running_mate_request_theme (running_mate_request_id INT UNSIGNED NOT NULL, theme_id INT NOT NULL, INDEX IDX_A7326227CEDF4387 (running_mate_request_id), INDEX IDX_A732622759027487 (theme_id), PRIMARY KEY(running_mate_request_id, theme_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE volunteer_request_technical_skill ADD CONSTRAINT FK_7F8C5C1EB8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer_request_technical_skill ADD CONSTRAINT FK_7F8C5C1EE98F0EFD FOREIGN KEY (technical_skill_id) REFERENCES application_request_technical_skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer_request_theme ADD CONSTRAINT FK_5427AF53B8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer_request_theme ADD CONSTRAINT FK_5427AF5359027487 FOREIGN KEY (theme_id) REFERENCES application_request_theme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE running_mate_request_theme ADD CONSTRAINT FK_A7326227CEDF4387 FOREIGN KEY (running_mate_request_id) REFERENCES application_request_running_mate (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE running_mate_request_theme ADD CONSTRAINT FK_A732622759027487 FOREIGN KEY (theme_id) REFERENCES application_request_theme (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE volunteer_request_theme DROP FOREIGN KEY FK_5427AF5359027487');
        $this->addSql('ALTER TABLE running_mate_request_theme DROP FOREIGN KEY FK_A732622759027487');
        $this->addSql('ALTER TABLE volunteer_request_technical_skill DROP FOREIGN KEY FK_7F8C5C1EE98F0EFD');
        $this->addSql('ALTER TABLE volunteer_request_technical_skill DROP FOREIGN KEY FK_7F8C5C1EB8D6887');
        $this->addSql('ALTER TABLE volunteer_request_theme DROP FOREIGN KEY FK_5427AF53B8D6887');
        $this->addSql('ALTER TABLE running_mate_request_theme DROP FOREIGN KEY FK_A7326227CEDF4387');
        $this->addSql('DROP TABLE application_request_theme');
        $this->addSql('DROP TABLE application_request_technical_skill');
        $this->addSql('DROP TABLE application_request_volunteer');
        $this->addSql('DROP TABLE volunteer_request_technical_skill');
        $this->addSql('DROP TABLE volunteer_request_theme');
        $this->addSql('DROP TABLE application_request_running_mate');
        $this->addSql('DROP TABLE running_mate_request_theme');
    }
}
