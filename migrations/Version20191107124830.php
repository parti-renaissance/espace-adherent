<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191107124830 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projection_referent_managed_users ADD subscription_types LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', DROP is_mail_subscriber');
        $this->addSql('ALTER TABLE donators ADD last_successful_donation_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE donators ADD CONSTRAINT FK_A902FDD7DE59CB1A FOREIGN KEY (last_successful_donation_id) REFERENCES donations (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A902FDD7DE59CB1A ON donators (last_successful_donation_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donators DROP FOREIGN KEY FK_A902FDD7DE59CB1A');
        $this->addSql('DROP INDEX UNIQ_A902FDD7DE59CB1A ON donators');
        $this->addSql('ALTER TABLE donators DROP last_successful_donation_id');
        $this->addSql('ALTER TABLE projection_referent_managed_users ADD is_mail_subscriber TINYINT(1) NOT NULL, DROP subscription_types');
    }
}
