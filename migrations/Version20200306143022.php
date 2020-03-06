<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200306143022 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elected_representative_political_function (id INT AUTO_INCREMENT NOT NULL, elected_representative_id INT NOT NULL, name VARCHAR(255) NOT NULL, clarification VARCHAR(255) DEFAULT NULL, geographical_area VARCHAR(255) NOT NULL, on_going TINYINT(1) DEFAULT \'1\' NOT NULL, begin_at DATE NOT NULL, finish_at DATE DEFAULT NULL, INDEX IDX_303BAF41D38DA5D3 (elected_representative_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE elected_representative_political_function ADD CONSTRAINT FK_303BAF41D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE elected_representative_mandate ADD on_going TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE elected_representative_political_function');
        $this->addSql('ALTER TABLE elected_representative_mandate DROP on_going');
    }
}
