<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220204164624 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE geo_zone ADD tags VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_A4CCEF076FBC9426 ON geo_zone (tags)');
        $this->addSql('ALTER TABLE geo_zone RENAME INDEX geo_zone_type_idx TO IDX_A4CCEF078CDE5729');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_A4CCEF076FBC9426 ON geo_zone');
        $this->addSql('ALTER TABLE geo_zone DROP tags');
        $this->addSql('ALTER TABLE geo_zone RENAME INDEX idx_a4ccef078cde5729 TO geo_zone_type_idx');
    }
}
