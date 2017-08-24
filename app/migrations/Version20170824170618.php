<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170824170618 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE unregistrations (id INT AUTO_INCREMENT NOT NULL, email_address VARCHAR(255) NOT NULL, reasons LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', comment LONGTEXT DEFAULT NULL, registered_at DATETIME NOT NULL, unregistered_at DATETIME NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, address_address VARCHAR(150) DEFAULT NULL, address_postal_code VARCHAR(15) DEFAULT NULL, address_city_insee VARCHAR(15) DEFAULT NULL, address_city_name VARCHAR(255) DEFAULT NULL, address_country VARCHAR(2) NOT NULL, address_latitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', address_longitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE unregistrations');
    }
}
