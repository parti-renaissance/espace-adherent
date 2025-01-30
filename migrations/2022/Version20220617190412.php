<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220617190412 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_B6FB4E7B7B00651C ON pap_building_statistics (status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_B6FB4E7B7B00651C ON pap_building_statistics');
    }
}
