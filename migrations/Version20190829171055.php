<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190829171055 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            "INSERT INTO subscription_type (label, code) VALUES 
            ('Recevoir les e-mails de votre candidat aux municipales 2020', 'municipal_email')"
        );

        $this->addSql(
            "INSERT IGNORE INTO adherent_subscription_type (adherent_id, subscription_type_id) 
            SELECT 
                adherents.id, 
                subscription_type.id
            FROM adherents
            INNER JOIN subscription_type ON subscription_type.code = 'municipal_email'
            WHERE 
                adherents.adherent = true
                AND adherents.status = 'ENABLED'
                AND adherents.email_unsubscribed = false"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            "DELETE adherent_subscription_type FROM adherent_subscription_type 
            INNER JOIN subscription_type ON subscription_type_id = subscription_type.id 
            WHERE subscription_type.code = 'municipal_email'"
        );

        $this->addSql(
            "DELETE adherent_email_subscription_histories FROM adherent_email_subscription_histories 
            INNER JOIN subscription_type ON subscription_type_id = subscription_type.id 
            WHERE subscription_type.code = 'municipal_email'"
        );

        $this->addSql("DELETE FROM subscription_type WHERE code = 'municipal_email'");
    }
}
