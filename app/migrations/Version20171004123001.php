<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171004123001 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE referent_areas (referent_id SMALLINT UNSIGNED NOT NULL, area_id SMALLINT UNSIGNED NOT NULL, INDEX IDX_75CEBC6C35E47E35 (referent_id), INDEX IDX_75CEBC6CBD0F409C (area_id), PRIMARY KEY(referent_id, area_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referent_area (id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL, area_code VARCHAR(4) NOT NULL, area_type VARCHAR(20) NOT NULL, name VARCHAR(100) NOT NULL, keywords LONGTEXT NOT NULL, UNIQUE INDEX referent_area_area_code_unique (area_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE referent_areas ADD CONSTRAINT FK_75CEBC6C35E47E35 FOREIGN KEY (referent_id) REFERENCES referent (id)');
        $this->addSql('ALTER TABLE referent_areas ADD CONSTRAINT FK_75CEBC6CBD0F409C FOREIGN KEY (area_id) REFERENCES referent_area (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE referent_areas DROP FOREIGN KEY FK_75CEBC6CBD0F409C');
        $this->addSql('DROP TABLE referent_areas');
        $this->addSql('DROP TABLE referent_area');
    }
}
