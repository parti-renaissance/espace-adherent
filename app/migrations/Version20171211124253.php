<?php declare(strict_types = 1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171211124253 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE reports (id INT UNSIGNED AUTO_INCREMENT NOT NULL, citizen_project_id INT UNSIGNED DEFAULT NULL, reasons JSON NOT NULL, comment LONGTEXT DEFAULT NULL, status VARCHAR(16) NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', type VARCHAR(255) NOT NULL, INDEX IDX_F11FA745B3584533 (citizen_project_id), INDEX report_status_idx (status), INDEX report_type_idx (type), UNIQUE INDEX report_uuid_unique (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA745B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('ALTER TABLE events CHANGE expert_found expert_found TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_69d950adc14e7bc9 TO IDX_A956D4E4C14E7BC9');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_69d950ad6bf91e2f TO IDX_A956D4E46BF91E2F');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE reports');
        $this->addSql('ALTER TABLE events CHANGE expert_found expert_found TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_a956d4e4c14e7bc9 TO IDX_69D950ADC14E7BC9');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_a956d4e46bf91e2f TO IDX_69D950AD6BF91E2F');
    }
}
