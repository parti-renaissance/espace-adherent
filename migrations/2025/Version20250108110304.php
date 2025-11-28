<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250108110304 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD public_id VARCHAR(7) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3B5B48B91 ON adherents (public_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_562C7DA3B5B48B91 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP public_id');
    }
}
