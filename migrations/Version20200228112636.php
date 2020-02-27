<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200228112636 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elected_representative (id INT AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED DEFAULT NULL, last_name VARCHAR(50) NOT NULL, first_name VARCHAR(50) NOT NULL, gender VARCHAR(10) NOT NULL, birth_date DATE NOT NULL, birth_place VARCHAR(255) DEFAULT NULL, official_id BIGINT DEFAULT NULL, contact_email VARCHAR(255) DEFAULT NULL, contact_phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\', comment VARCHAR(255) DEFAULT NULL, is_supporting_la_rem TINYINT(1) DEFAULT NULL, has_followed_training TINYINT(1) DEFAULT NULL, is_adherent TINYINT(1) DEFAULT NULL, UNIQUE INDEX UNIQ_BF51F0FD25F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE elected_representative_social_network_link (id INT AUTO_INCREMENT NOT NULL, elected_representative_id INT NOT NULL, url VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_231377B5D38DA5D3 (elected_representative_id), UNIQUE INDEX social_network_elected_representative_unique (type, elected_representative_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE elected_representative ADD CONSTRAINT FK_BF51F0FD25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE elected_representative_social_network_link ADD CONSTRAINT FK_231377B5D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_social_network_link DROP FOREIGN KEY FK_231377B5D38DA5D3');
        $this->addSql('DROP TABLE elected_representative');
        $this->addSql('DROP TABLE elected_representative_social_network_link');
    }
}
