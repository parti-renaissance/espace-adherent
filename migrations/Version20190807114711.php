<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190807114711 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE projection_referent_managed_users_id_seq');
        $this->addSql('CREATE SEQUENCE projection_referent_managed_users_id_seq');
        $this->addSql('SELECT setval(\'projection_referent_managed_users_id_seq\', (SELECT MAX(id) FROM projection_referent_managed_users))');
        $this->addSql('ALTER TABLE projection_referent_managed_users ALTER id SET DEFAULT nextval(\'projection_referent_managed_users_id_seq\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projection_referent_managed_users ALTER id DROP DEFAULT');
    }
}
