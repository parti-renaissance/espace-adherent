<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180524165445 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
<<<'SQL'
CREATE TABLE adherent_email_subscription_history_referent_tag (
  email_subscription_history_id INT UNSIGNED NOT NULL,
  referent_tag_id INT UNSIGNED NOT NULL,
  INDEX IDX_6FFBE6E88FCB8132 (email_subscription_history_id),
  INDEX IDX_6FFBE6E89C262DB3 (referent_tag_id),
  PRIMARY KEY(email_subscription_history_id, referent_tag_id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
SQL
        );
        $this->addSql('ALTER TABLE adherent_email_subscription_histories DROP FOREIGN KEY FK_272CB3E99C262DB3');
        $this->addSql('DROP INDEX IDX_51AD83549C262DB3 ON adherent_email_subscription_histories');
        $this->addSql('ALTER TABLE adherent_email_subscription_histories DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE adherent_email_subscription_histories ADD id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL, DROP uuid, DROP referent_tag_id');
        $this->addSql('ALTER TABLE adherent_email_subscription_history_referent_tag ADD CONSTRAINT FK_6FFBE6E88FCB8132 FOREIGN KEY (email_subscription_history_id) REFERENCES adherent_email_subscription_histories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherent_email_subscription_history_referent_tag ADD CONSTRAINT FK_6FFBE6E89C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM adherent_email_subscription_history_referent_tag');
        $this->addSql('DELETE FROM adherent_email_subscription_histories');
        $this->addSql('ALTER TABLE adherent_email_subscription_history_referent_tag DROP FOREIGN KEY FK_6FFBE6E88FCB8132');
        $this->addSql('ALTER TABLE adherent_email_subscription_history_referent_tag DROP FOREIGN KEY FK_6FFBE6E89C262DB3');
        $this->addSql('ALTER TABLE adherent_email_subscription_histories MODIFY id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE adherent_email_subscription_histories DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE adherent_email_subscription_histories ADD uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', ADD referent_tag_id INT UNSIGNED DEFAULT NULL, DROP id');
        $this->addSql('ALTER TABLE adherent_email_subscription_histories ADD CONSTRAINT FK_272CB3E99C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('CREATE INDEX IDX_51AD83549C262DB3 ON adherent_email_subscription_histories (referent_tag_id)');
        $this->addSql('ALTER TABLE adherent_email_subscription_histories ADD PRIMARY KEY (uuid)');
    }
}
