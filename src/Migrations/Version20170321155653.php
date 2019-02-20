<?php

namespace Migrations;

use AppBundle\Entity\Event;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170321155653 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events ADD status VARCHAR(20) NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        /** @var Connection $connection */
        $connection = $this->connection;

        $connection->executeUpdate('UPDATE events e SET e.status = :status', ['status' => Event::STATUS_SCHEDULED], ['status' => \PDO::PARAM_STR]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP status');
    }
}
