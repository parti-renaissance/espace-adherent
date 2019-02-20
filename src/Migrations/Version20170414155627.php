<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170414155627 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE legislative_candidates (id INT AUTO_INCREMENT NOT NULL, candidate_id INT UNSIGNED DEFAULT NULL, area VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_AE55AF9B91BD8781 (candidate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE legislative_candidates ADD CONSTRAINT FK_AE55AF9B91BD8781 FOREIGN KEY (candidate_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE legislative_candidates');
    }
}
