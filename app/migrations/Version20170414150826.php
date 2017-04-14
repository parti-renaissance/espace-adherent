<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170414150826 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE legislative (id INT AUTO_INCREMENT NOT NULL, candidate_id INT UNSIGNED DEFAULT NULL, area VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1023FA7391BD8781 (candidate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE legislative ADD CONSTRAINT FK_1023FA7391BD8781 FOREIGN KEY (candidate_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE legislative');
    }
}
