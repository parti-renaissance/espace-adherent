<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200930110416 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->connection->insert('referent_tags', [
            'name' => 'Métropole de Montpellier (34M)',
            'code' => '34M',
            'type' => 'metropolis',
        ]);
        $this->connection->insert('referent_tags', [
            'name' => 'Métropole de Lyon (69M)',
            'code' => '69M',
            'type' => 'metropolis',
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->connection->delete('referent_tags', [
            'code' => '34M',
        ]);
        $this->connection->delete('referent_tags', [
            'code' => '69M',
        ]);
    }
}
