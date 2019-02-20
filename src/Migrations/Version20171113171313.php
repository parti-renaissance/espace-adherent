<?php

namespace Migrations;

use AppBundle\DataFixtures\ORM\LoadPurchasingPowerData;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171113171313 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->connection->executeQuery('SET foreign_key_checks = 0');
        $this->connection->executeQuery('TRUNCATE TABLE interactive_choices');
        $this->connection->executeQuery('SET foreign_key_checks = 1');

        foreach (LoadPurchasingPowerData::createChoices() as $choice) {
            $this->connection->insert('interactive_choices', [
                'step' => $choice->getStep(),
                'content_key' => $choice->getContentKey(),
                'label' => $choice->getLabel(),
                'content' => $choice->getContent(),
                'uuid' => $choice->getUuid()->toString(),
                'type' => 'purchasing_power',
            ]);
        }
    }

    public function down(Schema $schema): void
    {
    }
}
