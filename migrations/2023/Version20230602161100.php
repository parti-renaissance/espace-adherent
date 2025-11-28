<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230602161100 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elected_representative_revenue_declaration (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          elected_representative_id INT UNSIGNED NOT NULL,
          amount INT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_6A0C2D59D17F50A6 (uuid),
          INDEX IDX_6A0C2D59D38DA5D3 (elected_representative_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          elected_representative_revenue_declaration
        ADD
          CONSTRAINT FK_6A0C2D59D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_revenue_declaration DROP FOREIGN KEY FK_6A0C2D59D38DA5D3');
        $this->addSql('DROP TABLE elected_representative_revenue_declaration');
    }
}
