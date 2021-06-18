<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210618173936 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_instance_quality
        DROP
          INDEX UNIQ_D63B17FA25F06C53,
        ADD
          INDEX IDX_D63B17FA25F06C53 (adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_instance_quality
        DROP
          INDEX IDX_D63B17FA25F06C53,
        ADD
          UNIQUE INDEX UNIQ_D63B17FA25F06C53 (adherent_id)');
    }
}
