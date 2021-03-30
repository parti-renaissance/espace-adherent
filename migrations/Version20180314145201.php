<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180314145201 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD emails_subscriptions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('UPDATE adherents SET emails_subscriptions = \'subscribed_emails_main,subscribed_emails_movement_information,subscribed_emails_government_information,subscribed_emails_weekly_letter,subscribed_emails_mooc,subscribed_emails_microlearning\' WHERE main_emails_subscription = TRUE');
        $this->addSql('UPDATE adherents SET emails_subscriptions = CONCAT_WS(\',\', emails_subscriptions, \'subscribed_emails_referents\') WHERE referents_emails_subscription = TRUE');
        $this->addSql('UPDATE adherents INNER JOIN donations ON adherents.email_address = donations.email_address SET emails_subscriptions = CONCAT_WS(\',\', emails_subscriptions, \'subscribed_emails_donator_information\') WHERE finished = TRUE');
        $this->addSql('ALTER TABLE adherents DROP main_emails_subscription, DROP referents_emails_subscription');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD main_emails_subscription TINYINT(1) NOT NULL, ADD referents_emails_subscription TINYINT(1) NOT NULL');
        $this->addSql('UPDATE adherents SET main_emails_subscription = TRUE WHERE emails_subscriptions LIKE \'%subscribed_emails_movement_information%\'');
        $this->addSql('UPDATE adherents SET referents_emails_subscription = TRUE WHERE emails_subscriptions LIKE \'%subscribed_emails_referents%\'');
        $this->addSql('ALTER TABLE adherents DROP emails_subscriptions');
    }
}
