<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180705144501 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE adherent_subscription_type (
                adherent_id INT UNSIGNED NOT NULL, 
                subscription_type_id INT UNSIGNED NOT NULL,
                INDEX IDX_F93DC28A25F06C53 (adherent_id), 
                INDEX IDX_F93DC28AB6596C08 (subscription_type_id), 
                PRIMARY KEY(adherent_id, subscription_type_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE subscription_type (
                id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                label VARCHAR(255) NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_BBE2473777153098 (code), 
                INDEX IDX_BBE2473777153098 (code), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE adherent_subscription_type ADD CONSTRAINT FK_F93DC28A25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherent_subscription_type ADD CONSTRAINT FK_F93DC28AB6596C08 FOREIGN KEY (subscription_type_id) REFERENCES subscription_type (id) ON DELETE CASCADE');

        $this->addSql(
            "INSERT INTO subscription_type (label, code) VALUES 
            ('Recevoir les informations sur les actions militantes du mouvement par SMS ou MMS','militant_action_sms'),
            ('Recevoir les e-mails de votre animateur local','subscribed_emails_local_host'),
            ('Recevoir les informations du mouvement','subscribed_emails_movement_information'),
            ('Recevoir la newsletter hebdomadaire de LaREM','subscribed_emails_weekly_letter'),
            ('Recevoir les e-mails de votre référent territorial','subscribed_emails_referents'),
            ('Recevoir les e-mails de votre porteuse ou porteur de projet','citizen_project_host_email'),
            ('Être notifié(e) de la création de nouveaux projets citoyens','subscribed_emails_citizen_project_creation')"
        );

        $this->addSql('DROP INDEX adherent_email_subscription_histories_adherent_email_type_idx ON adherent_email_subscription_histories');
        $this->addSql('ALTER TABLE adherent_email_subscription_histories ADD subscription_type_id INT UNSIGNED DEFAULT NULL');

        $this->addSql(
            'UPDATE adherent_email_subscription_histories AS history
            INNER JOIN subscription_type AS st ON st.code = history.subscribed_email_type
            SET history.subscription_type_id = st.id'
        );

        // Remove history for the old no more used subscription types
        $this->addSql('DELETE FROM adherent_email_subscription_histories WHERE subscription_type_id IS NULL');
        $this->addSql('ALTER TABLE adherent_email_subscription_histories DROP subscribed_email_type, CHANGE subscription_type_id subscription_type_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE adherent_email_subscription_histories ADD CONSTRAINT FK_51AD8354B6596C08 FOREIGN KEY (subscription_type_id) REFERENCES subscription_type (id)');
        $this->addSql('CREATE INDEX IDX_51AD8354B6596C08 ON adherent_email_subscription_histories (subscription_type_id)');
        $this->addSql('ALTER TABLE adherents CHANGE local_host_emails_subscription local_host_emails_subscription TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_subscription_type DROP FOREIGN KEY FK_F93DC28AB6596C08');
        $this->addSql('DROP TABLE adherent_subscription_type');

        $this->addSql('ALTER TABLE adherent_email_subscription_histories DROP FOREIGN KEY FK_51AD8354B6596C08');
        $this->addSql('DROP INDEX IDX_51AD8354B6596C08 ON adherent_email_subscription_histories');
        $this->addSql('ALTER TABLE adherent_email_subscription_histories ADD subscribed_email_type VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci');

        $this->addSql(
            'UPDATE adherent_email_subscription_histories AS history
            INNER JOIN subscription_type AS st ON st.id = history.subscription_type_id
            SET history.subscribed_email_type = st.code'
        );

        $this->addSql('ALTER TABLE adherent_email_subscription_histories DROP subscription_type_id');
        $this->addSql('CREATE INDEX adherent_email_subscription_histories_adherent_email_type_idx ON adherent_email_subscription_histories (subscribed_email_type)');
        $this->addSql('DROP TABLE subscription_type');
        $this->addSql('ALTER TABLE adherents CHANGE local_host_emails_subscription local_host_emails_subscription TINYINT(1) NOT NULL');
    }
}
