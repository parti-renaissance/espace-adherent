<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200930110416 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->connection->insert('referent_tags', [
            'name' => 'Métropole de Montpellier',
            'code' => '34M',
            'type' => 'metropolis',
        ]);
        $this->connection->insert('referent_tags', [
            'name' => 'Métropole de Lyon',
            'code' => '69M',
            'type' => 'metropolis',
        ]);
    }

    public function down(Schema $schema)
    {
        $this->connection->delete('referent_tags', [
            'code' => '34M',
        ]);
        $this->connection->delete('referent_tags', [
            'code' => '69M',
        ]);
    }
}
