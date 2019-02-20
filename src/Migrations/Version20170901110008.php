<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170901110008 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->connection->insert('pages', [
            'title' => 'Les ordonnances expliquées',
            'slug' => 'les-ordonnances-expliquees',
            'keywords' => 'loi travail ordonnances explications',
            'description' => 'Ici vous trouverez les ordonnances expliquées',
            'content' => file_get_contents(__DIR__.'/../../src/DataFixtures/explainer.html'),
            'display_media' => 0,
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->connection->delete('pages', [
            'title' => 'Les ordonnances expliquées',
            'slug' => 'les-ordonnances-expliquees',
        ]);
    }
}
