<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170217233047 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE je_marche_reports (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(30) NOT NULL, email_address VARCHAR(255) NOT NULL, convinced LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', almost_convinced LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', not_convinced SMALLINT NOT NULL, reaction LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE je_marche_reports');
    }
}
