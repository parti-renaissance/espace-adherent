<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170406113002 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ton_macron_choices (id INT UNSIGNED AUTO_INCREMENT NOT NULL, step SMALLINT UNSIGNED NOT NULL, `content_key` VARCHAR(30) NOT NULL, label VARCHAR(100) NOT NULL, content LONGTEXT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX ton_macron_choices_uuid_unique (uuid), UNIQUE INDEX ton_macron_choices_content_key_unique (`content_key`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ton_macron_friend_invitations (id INT UNSIGNED AUTO_INCREMENT NOT NULL, friend_first_name VARCHAR(50) NOT NULL, friend_age SMALLINT UNSIGNED NOT NULL, friend_gender VARCHAR(6) NOT NULL, friend_position VARCHAR(50) NOT NULL, friend_email_address VARCHAR(255) DEFAULT NULL, author_first_name VARCHAR(50) DEFAULT NULL, author_last_name VARCHAR(50) DEFAULT NULL, author_email_address VARCHAR(255) DEFAULT NULL, mail_subject VARCHAR(100) DEFAULT NULL, mail_body LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX ton_macron_friend_invitations_uuid_unique (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ton_macron_friend_invitation_has_choices (invitation_id INT UNSIGNED NOT NULL, choice_id INT UNSIGNED NOT NULL, INDEX IDX_BB3BCAEEA35D7AF0 (invitation_id), INDEX IDX_BB3BCAEE998666D1 (choice_id), PRIMARY KEY(invitation_id, choice_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ton_macron_friend_invitation_has_choices ADD CONSTRAINT FK_BB3BCAEEA35D7AF0 FOREIGN KEY (invitation_id) REFERENCES ton_macron_friend_invitations (id)');
        $this->addSql('ALTER TABLE ton_macron_friend_invitation_has_choices ADD CONSTRAINT FK_BB3BCAEE998666D1 FOREIGN KEY (choice_id) REFERENCES ton_macron_choices (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ton_macron_friend_invitation_has_choices DROP FOREIGN KEY FK_BB3BCAEE998666D1');
        $this->addSql('ALTER TABLE ton_macron_friend_invitation_has_choices DROP FOREIGN KEY FK_BB3BCAEEA35D7AF0');
        $this->addSql('DROP TABLE ton_macron_choices');
        $this->addSql('DROP TABLE ton_macron_friend_invitations');
        $this->addSql('DROP TABLE ton_macron_friend_invitation_has_choices');
    }
}
