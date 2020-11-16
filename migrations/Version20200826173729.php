<?php

namespace Migrations;

use App\Subscription\SubscriptionTypeEnum;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200826173729 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            sprintf("UPDATE subscription_type 
            SET label = 'Recevoir les e-mails de mes candidat(e)s LaREM', 
                code = '%s'
            WHERE code = 'municipal_email'", SubscriptionTypeEnum::CANDIDATE_EMAIL)
        );

        $this->addSql(sprintf(
            "INSERT IGNORE INTO adherent_subscription_type (adherent_id, subscription_type_id) 
            SELECT 
                adherents.id, 
                subscription_type.id
            FROM adherents
            INNER JOIN subscription_type ON subscription_type.code = '%s'
            WHERE 
                adherents.adherent = true
                AND adherents.status = 'ENABLED'
                AND adherents.email_unsubscribed = false", SubscriptionTypeEnum::CANDIDATE_EMAIL));
    }

    public function down(Schema $schema): void
    {
    }
}
