<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201202140626 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE geo_city ADD replacement_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_city
        ADD
          CONSTRAINT FK_297C2D349D25CF90 FOREIGN KEY (replacement_id) REFERENCES geo_city (id)');
        $this->addSql('CREATE INDEX IDX_297C2D349D25CF90 ON geo_city (replacement_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE geo_city DROP FOREIGN KEY FK_297C2D349D25CF90');
        $this->addSql('DROP INDEX IDX_297C2D349D25CF90 ON geo_city');
        $this->addSql('ALTER TABLE geo_city DROP replacement_id');
    }
}
