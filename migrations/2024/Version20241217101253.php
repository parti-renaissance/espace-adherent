<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241217101253 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_A36198C6BF1CD3C3 ON committees');
        $this->addSql('ALTER TABLE committees DROP version');
        $this->addSql('ALTER TABLE
          committees_memberships
        DROP
          INDEX IDX_E7A6490E25F06C53,
        ADD
          UNIQUE INDEX UNIQ_E7A6490E25F06C53 (adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees ADD version SMALLINT UNSIGNED DEFAULT 2 NOT NULL');
        $this->addSql('CREATE INDEX IDX_A36198C6BF1CD3C3 ON committees (version)');
        $this->addSql('ALTER TABLE
          committees_memberships
        DROP
          INDEX UNIQ_E7A6490E25F06C53,
        ADD
          INDEX IDX_E7A6490E25F06C53 (adherent_id)');
    }
}
