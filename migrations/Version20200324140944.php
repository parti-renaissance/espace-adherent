<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200324140944 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_label DROP FOREIGN KEY FK_D814370435A3B2FC');
        $this->addSql('DROP INDEX IDX_D814370435A3B2FC ON elected_representative_label');
        $this->addSql('ALTER TABLE elected_representative_label ADD name VARCHAR(50) NOT NULL, DROP political_label_id');
        $this->addSql('DROP TABLE political_label');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE political_label (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX political_label_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE elected_representative_label ADD political_label_id INT NOT NULL, DROP name');
        $this->addSql('ALTER TABLE elected_representative_label ADD CONSTRAINT FK_D814370435A3B2FC FOREIGN KEY (political_label_id) REFERENCES political_label (id)');
        $this->addSql('CREATE INDEX IDX_D814370435A3B2FC ON elected_representative_label (political_label_id)');
    }
}
