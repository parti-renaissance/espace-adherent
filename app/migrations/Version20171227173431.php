<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171227173431 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE elections (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, place VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1BD26F335E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE election_rounds (id INT AUTO_INCREMENT NOT NULL, election_id INT NOT NULL, label VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, date DATE NOT NULL, INDEX IDX_37C02EA0A708DAFF (election_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE election_rounds ADD CONSTRAINT FK_37C02EA0A708DAFF FOREIGN KEY (election_id) REFERENCES elections (id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE election_rounds DROP FOREIGN KEY FK_37C02EA0A708DAFF');
        $this->addSql('DROP TABLE elections');
        $this->addSql('DROP TABLE election_rounds');
    }
}
