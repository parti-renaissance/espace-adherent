<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200110225009 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projection_referent_managed_users RENAME projection_managed_users');
        $this->addSql('ALTER TABLE 
          projection_managed_users RENAME INDEX projection_referent_managed_users_search TO projection_managed_users_search');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          projection_managed_users RENAME INDEX projection_managed_users_search TO projection_referent_managed_users_search');
        $this->addSql('ALTER TABLE projection_managed_users RENAME projection_referent_managed_users');
    }
}
