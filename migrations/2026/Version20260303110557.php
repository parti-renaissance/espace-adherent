<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303110557 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_hit ADD suspicious TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX IDX_74A09586E5FF5C81 ON app_hit (suspicious)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_74A09586E5FF5C81 ON app_hit');
        $this->addSql('ALTER TABLE app_hit DROP suspicious');
    }
}
