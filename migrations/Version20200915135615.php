<?php

namespace Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200915135615 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council ADD is_active TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE political_committee ADD is_active TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function postUp(Schema $schema)
    {
        /** @var Connection $connection */
        $connection = $this->connection;

        $connection->executeUpdate(
            'UPDATE territorial_council tc SET tc.is_active = 0 WHERE codes LIKE \'FDE-%\''
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE political_committee DROP is_active');
        $this->addSql('ALTER TABLE territorial_council DROP is_active');
    }
}
