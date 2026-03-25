<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325153436 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_90A7D656AB78BDC2 ON projection_managed_users');
        $this->addSql('CREATE INDEX IDX_90A7D6566D804024 ON projection_managed_users (adherent_uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_90A7D6566D804024 ON projection_managed_users');
        $this->addSql('CREATE INDEX IDX_90A7D656AB78BDC2 ON projection_managed_users (zones_ids)');
    }
}
