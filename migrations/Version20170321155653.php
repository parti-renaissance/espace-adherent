<?php

namespace Migrations;

use App\Entity\Event;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170321155653 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE events ADD status VARCHAR(20) NOT NULL');
    }

    public function postUp(Schema $schema)
    {
        /** @var Connection $connection */
        $connection = $this->connection;

        $connection->executeUpdate('UPDATE events e SET e.status = :status', ['status' => Event::STATUS_SCHEDULED], ['status' => \PDO::PARAM_STR]);
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE events DROP status');
    }
}
