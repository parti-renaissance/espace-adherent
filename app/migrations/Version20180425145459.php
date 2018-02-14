<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180425145459 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE adherent_email_subscription_histories (
                uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
                adherent_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
                referent_tag_id INT UNSIGNED DEFAULT NULL, 
                subscribed_email_type VARCHAR(50) NOT NULL,
                action VARCHAR(32) NOT NULL, 
                date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
                INDEX adherent_email_subscription_histories_adherent_uuid_idx (adherent_uuid),
                INDEX adherent_email_subscription_histories_adherent_action_idx (action),
                INDEX adherent_email_subscription_histories_adherent_date_idx (date),
                INDEX adherent_email_subscription_histories_adherent_email_type_idx (subscribed_email_type), 
                INDEX IDX_51AD83549C262DB3 (referent_tag_id),
                PRIMARY KEY(uuid)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('ALTER TABLE adherent_email_subscription_histories ADD CONSTRAINT FK_272CB3E99C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE adherent_email_subscription_histories');
    }
}
