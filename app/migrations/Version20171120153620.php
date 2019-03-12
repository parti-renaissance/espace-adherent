<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171120153620 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->connection->insert('interactive_choices', [
            'step' => 2,
            'content_key' => 'S02C09',
            'label' => 'Aucune',
            'content' => '',
            'uuid' => 'b61dbe63-7c26-4ad7-bd86-5d2f767e6d8b',
            'type' => 'my_europe',
        ]);
    }

    public function down(Schema $schema)
    {
        $this->connection->delete('interactive_choices', [
            'uuid' => 'b61dbe63-7c26-4ad7-bd86-5d2f767e6d8b',
        ]);
    }
}
