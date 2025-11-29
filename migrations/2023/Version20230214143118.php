<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230214143118 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          donations
        ADD
          zone_id INT UNSIGNED DEFAULT NULL,
        ADD
          visibility VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE
          donations
        ADD
          CONSTRAINT FK_CDE989629F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_CDE989629F2C3FAB ON donations (zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations DROP FOREIGN KEY FK_CDE989629F2C3FAB');
        $this->addSql('DROP INDEX IDX_CDE989629F2C3FAB ON donations');
        $this->addSql('ALTER TABLE donations DROP zone_id, DROP visibility');
    }
}
