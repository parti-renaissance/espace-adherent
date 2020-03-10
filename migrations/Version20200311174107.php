<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200311174107 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elected_representative_label_name (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX elected_representative_label_name_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE elected_representative_label (id INT AUTO_INCREMENT NOT NULL, name_id INT NOT NULL, elected_representative_id INT NOT NULL, on_going TINYINT(1) DEFAULT \'1\' NOT NULL, begin_year INT NOT NULL, finish_year INT DEFAULT NULL, INDEX IDX_D814370471179CD6 (name_id), INDEX IDX_D8143704D38DA5D3 (elected_representative_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE elected_representative_label ADD CONSTRAINT FK_D814370471179CD6 FOREIGN KEY (name_id) REFERENCES elected_representative_label_name (id)');
        $this->addSql('ALTER TABLE elected_representative_label ADD CONSTRAINT FK_D8143704D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('INSERT INTO elected_representative_label_name (name) VALUES (\'LaREM\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_label DROP FOREIGN KEY FK_D814370471179CD6');
        $this->addSql('DROP TABLE elected_representative_label_name');
        $this->addSql('DROP TABLE elected_representative_label');
    }
}
