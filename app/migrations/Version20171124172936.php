<?php declare(strict_types = 1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171124172936 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE committees CHANGE address_country address_country VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE adherents CHANGE gender gender VARCHAR(6) NOT NULL, CHANGE position position VARCHAR(20) NOT NULL, CHANGE address_country address_country VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE events CHANGE address_country address_country VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE citizen_projects ADD committee_id INT UNSIGNED DEFAULT NULL, CHANGE problem_description problem_description LONGTEXT DEFAULT NULL, CHANGE proposed_solution proposed_solution LONGTEXT DEFAULT NULL, CHANGE required_means required_means LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE citizen_projects ADD CONSTRAINT FK_6514902ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('CREATE INDEX IDX_6514902ED1A100B ON citizen_projects (committee_id)');
        $this->addSql('ALTER TABLE donations CHANGE address_country address_country VARCHAR(2) NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE citizen_project_skills ADD CONSTRAINT FK_B23BCADE5585C142 FOREIGN KEY (skill_id) REFERENCES skills (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citizen_project_skills ADD CONSTRAINT FK_B23BCADE6FBEFC74 FOREIGN KEY (citizen_initiative_id) REFERENCES citizen_projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherents CHANGE gender gender VARCHAR(6) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE position position VARCHAR(20) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE address_country address_country VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE citizen_projects DROP FOREIGN KEY FK_6514902ED1A100B');
        $this->addSql('DROP INDEX IDX_6514902ED1A100B ON citizen_projects');
        $this->addSql('ALTER TABLE citizen_projects DROP committee_id, CHANGE problem_description problem_description VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE proposed_solution proposed_solution VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE required_means required_means VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE committees CHANGE address_country address_country VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE donations CHANGE address_country address_country VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE events CHANGE address_country address_country VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
