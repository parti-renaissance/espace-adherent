<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201013145634 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE geo_custom_zone (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(255) NOT NULL,
          active TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_ABE4DB5A77153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE geo_foreign_district ADD custom_zone_id INT UNSIGNED');
        $this->addSql('ALTER TABLE
          geo_foreign_district
        ADD
          CONSTRAINT FK_973BE1F198755666 FOREIGN KEY (custom_zone_id) REFERENCES geo_custom_zone (id)');
        $this->addSql('CREATE INDEX IDX_973BE1F198755666 ON geo_foreign_district (custom_zone_id)');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->insert('geo_custom_zone', [
            'code' => 'FDE',
            'name' => "Français de l'Étranger",
            'active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->connection->update('geo_foreign_district', [
            'custom_zone_id' => $this->connection->lastInsertId(),
        ], [true]);

        $this->connection->executeQuery('ALTER TABLE geo_foreign_district CHANGE custom_zone_id custom_zone_id INT UNSIGNED NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE geo_foreign_district DROP FOREIGN KEY FK_973BE1F198755666');
        $this->addSql('DROP TABLE geo_custom_zone');
        $this->addSql('DROP INDEX IDX_973BE1F198755666 ON geo_foreign_district');
        $this->addSql('ALTER TABLE geo_foreign_district DROP custom_zone_id');
    }
}
