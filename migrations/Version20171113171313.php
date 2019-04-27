<?php

namespace Migrations;

use AppBundle\DataFixtures\ORM\LoadMyEuropeData;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171113171313 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->connection->executeQuery('SET foreign_key_checks = 0');
        $this->connection->executeQuery('TRUNCATE TABLE interactive_choices');
        $this->connection->executeQuery('SET foreign_key_checks = 1');

        foreach (LoadMyEuropeData::createChoices() as $choice) {
            $this->connection->insert('interactive_choices', [
                'step' => $choice->getStep(),
                'content_key' => $choice->getContentKey(),
                'label' => $choice->getLabel(),
                'content' => $choice->getContent(),
                'uuid' => $choice->getUuid()->toString(),
                'type' => 'my_europe',
            ]);
        }
    }

    public function down(Schema $schema)
    {
    }
}
