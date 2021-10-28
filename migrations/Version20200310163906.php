<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200310163906 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elected_representative_sponsorship (id INT AUTO_INCREMENT NOT NULL, elected_representative_id INT NOT NULL, presidential_election_year INT NOT NULL, candidate VARCHAR(255) DEFAULT NULL, INDEX IDX_CA6D486D38DA5D3 (elected_representative_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE elected_representative_sponsorship ADD CONSTRAINT FK_CA6D486D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE elected_representative CHANGE is_adherent is_adherent TINYINT(1) DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE elected_representative_sponsorship');
        $this->addSql('ALTER TABLE elected_representative CHANGE is_adherent is_adherent TINYINT(1) DEFAULT NULL');
    }
}
