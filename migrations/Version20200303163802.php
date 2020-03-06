<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200303163802 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elected_representative_mandate (id INT AUTO_INCREMENT NOT NULL, elected_representative_id INT NOT NULL, type VARCHAR(255) NOT NULL, is_elected TINYINT(1) DEFAULT \'0\' NOT NULL, geographical_area VARCHAR(255) NOT NULL, begin_at DATE NOT NULL, finish_at DATE DEFAULT NULL, political_affiliation VARCHAR(10) NOT NULL, la_remsupport VARCHAR(255) DEFAULT NULL, INDEX IDX_38609146D38DA5D3 (elected_representative_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE elected_representative_mandate ADD CONSTRAINT FK_38609146D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE elected_representative_mandate');
    }
}
