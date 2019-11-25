<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191125103302 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE senator_area DROP entire_world');

        $this->addSql("INSERT INTO referent_tags (name, code) VALUES ('Français de l\'Étranger', 'FOF')");

        $this->addSql(
            "INSERT INTO subscription_type (label, code) VALUES 
            ('Recevoir les e-mails de mon/ma sénateur/trice', 'senator_email')"
        );

        $this->addSql(
            "INSERT INTO adherent_subscription_type (adherent_id, subscription_type_id) 
            SELECT a.id, (SELECT id FROM subscription_type WHERE code = 'senator_email') FROM adherents AS a
            INNER JOIN adherent_subscription_type AS ast ON ast.adherent_id = a.id
            INNER JOIN subscription_type AS st ON st.id = ast.subscription_type_id AND st.code IN ('subscribed_emails_referents', 'deputy_email')
            WHERE 
                a.adherent = true
                AND a.status = 'ENABLED'
                AND a.email_unsubscribed = false
            GROUP BY a.id"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE senator_area ADD entire_world TINYINT(1) DEFAULT \'0\' NOT NULL');

        $this->addSql(
            "DELETE adherent_subscription_type FROM adherent_subscription_type 
            INNER JOIN subscription_type ON subscription_type_id = subscription_type.id 
            WHERE subscription_type.code = 'senator_email'"
        );

        $this->addSql(
            "DELETE adherent_email_subscription_histories FROM adherent_email_subscription_histories 
            INNER JOIN subscription_type ON subscription_type_id = subscription_type.id 
            WHERE subscription_type.code = 'senator_email'"
        );

        $this->addSql("DELETE FROM subscription_type WHERE code = 'senator_email'");
    }
}
