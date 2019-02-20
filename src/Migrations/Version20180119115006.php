<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180119115006 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE activity_subscriptions');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE activity_subscriptions (id INT AUTO_INCREMENT NOT NULL, following_adherent_id INT UNSIGNED DEFAULT NULL, followed_adherent_id INT UNSIGNED DEFAULT NULL, subscribed_at DATETIME NOT NULL, unsubscribed_at DATETIME DEFAULT NULL, UNIQUE INDEX activity_subscriptions_unique (followed_adherent_id, following_adherent_id), INDEX IDX_5A543C56016700F (following_adherent_id), INDEX IDX_5A543C57D7402F7 (followed_adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activity_subscriptions ADD CONSTRAINT FK_5A543C56016700F FOREIGN KEY (following_adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE activity_subscriptions ADD CONSTRAINT FK_5A543C57D7402F7 FOREIGN KEY (followed_adherent_id) REFERENCES adherents (id)');
    }
}
