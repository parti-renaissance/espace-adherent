<?php declare(strict_types = 1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171128100234 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE committees CHANGE address_country address_country VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE adherents CHANGE gender gender VARCHAR(6) NOT NULL, CHANGE position position VARCHAR(20) NOT NULL, CHANGE address_country address_country VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE events CHANGE address_country address_country VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE citizen_projects ADD assistance_content VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE donations CHANGE address_country address_country VARCHAR(2) NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adherents CHANGE gender gender VARCHAR(6) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE position position VARCHAR(20) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE address_country address_country VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE citizen_projects DROP assistance_content');
        $this->addSql('ALTER TABLE committees CHANGE address_country address_country VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE donations CHANGE address_country address_country VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE events CHANGE address_country address_country VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
