<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170316160611 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE procuration_requests (id INT AUTO_INCREMENT NOT NULL, gender VARCHAR(6) NOT NULL, last_name VARCHAR(50) NOT NULL, first_names VARCHAR(100) NOT NULL, address VARCHAR(150) NOT NULL, postal_code VARCHAR(15) NOT NULL, city_insee VARCHAR(15) DEFAULT NULL, city_name VARCHAR(255) DEFAULT NULL, country VARCHAR(2) NOT NULL, phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\', email_address VARCHAR(255) NOT NULL, birthdate DATE DEFAULT NULL, vote_postal_code VARCHAR(15) NOT NULL, vote_city_insee VARCHAR(15) DEFAULT NULL, vote_city_name VARCHAR(255) DEFAULT NULL, vote_country VARCHAR(2) NOT NULL, vote_office VARCHAR(50) DEFAULT NULL, election_presidential_first_round TINYINT(1) NOT NULL, election_presidential_second_round TINYINT(1) NOT NULL, election_legislative_first_round TINYINT(1) NOT NULL, election_legislative_second_round TINYINT(1) NOT NULL, reason VARCHAR(15) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE procuration_requests');
    }
}
