<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Ramsey\Uuid\Uuid;

final class Version20200512163952 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          elected_representative 
        ADD 
          uuid CHAR(36) NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function postUp(Schema $schema)
    {
        foreach ($this->connection->fetchAll('SELECT id FROM elected_representative') as $electedRepresentative) {
            $uuid = (Uuid::uuid4())->toString();
            $this->connection->executeUpdate(
                'UPDATE elected_representative SET uuid = ? WHERE id = ?',
                [$uuid, $electedRepresentative['id']]
            );
        }

        $this->connection->executeQuery('ALTER TABLE elected_representative MODIFY COLUMN uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative DROP uuid');
    }
}
