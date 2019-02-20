<?php

namespace Migrations;

use AppBundle\Subscription\SubscriptionTypeEnum;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180917103933 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscription_type ADD position SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE subscription_type SET position = 2 WHERE code = ? LIMIT 1', [SubscriptionTypeEnum::LOCAL_HOST_EMAIL]);
        $this->addSql('UPDATE subscription_type SET position = 3 WHERE code = ? LIMIT 1', [SubscriptionTypeEnum::REFERENT_EMAIL]);
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->insert('subscription_type', [
            'label' => 'Recevoir les e-mails de votre député(e)',
            'code' => 'deputy_email',
            'position' => 1,
        ]);

        $this->connection->executeQuery(
            'INSERT INTO adherent_subscription_type (adherent_id, subscription_type_id) 
            SELECT a.id, ? FROM adherents AS a
            INNER JOIN adherent_subscription_type AS ast ON ast.adherent_id = a.id
            INNER JOIN subscription_type AS st ON st.id = ast.subscription_type_id AND st.code IN (?)
            GROUP BY a.id',
            [
                $this->connection->lastInsertId(),
                [
                    SubscriptionTypeEnum::REFERENT_EMAIL,
                    SubscriptionTypeEnum::LOCAL_HOST_EMAIL,
                ],
            ],
            [\PDO::PARAM_INT, Connection::PARAM_STR_ARRAY]
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscription_type DROP position');
    }
}
