<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200313112431 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elected_representative_label (id INT AUTO_INCREMENT NOT NULL, political_label_id INT NOT NULL, elected_representative_id INT NOT NULL, on_going TINYINT(1) DEFAULT \'1\' NOT NULL, begin_year INT NOT NULL, finish_year INT DEFAULT NULL, INDEX IDX_D814370435A3B2FC (political_label_id), INDEX IDX_D8143704D38DA5D3 (elected_representative_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE political_label (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX political_label_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE elected_representative_label ADD CONSTRAINT FK_D814370435A3B2FC FOREIGN KEY (political_label_id) REFERENCES political_label (id)');
        $this->addSql('ALTER TABLE elected_representative_label ADD CONSTRAINT FK_D8143704D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('INSERT INTO political_label (name) VALUES (\'LaREM\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_label DROP FOREIGN KEY FK_D814370471179CD6');
        $this->addSql('DROP TABLE elected_representative_label');
        $this->addSql('DROP TABLE political_label');
    }
}
