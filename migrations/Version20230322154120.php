<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230322154120 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE geo_city DROP FOREIGN KEY FK_297C2D349D25CF90');
        $this->addSql('ALTER TABLE
          geo_city
        ADD
          CONSTRAINT FK_297C2D349D25CF90 FOREIGN KEY (replacement_id) REFERENCES geo_city (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE geo_city DROP FOREIGN KEY FK_297C2D349D25CF90');
        $this->addSql('ALTER TABLE
          geo_city
        ADD
          CONSTRAINT FK_297C2D349D25CF90 FOREIGN KEY (replacement_id) REFERENCES geo_city (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
