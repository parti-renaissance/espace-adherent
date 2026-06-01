<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601130040 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE social_network_feed (scraper_id INT UNSIGNED NOT NULL, post_id VARCHAR(255) NOT NULL, platform VARCHAR(255) NOT NULL, username VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, date_published DATETIME DEFAULT NULL, image_url LONGTEXT DEFAULT NULL, avatar_image_url LONGTEXT DEFAULT NULL, url LONGTEXT DEFAULT NULL, score INT DEFAULT NULL, raw_json JSON DEFAULT NULL, id INT UNSIGNED AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_84BE2EE35A68BBF9 (scraper_id), UNIQUE INDEX UNIQ_84BE2EE3D17F50A6 (uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE social_network_feed_photo (id INT UNSIGNED AUTO_INCREMENT NOT NULL, scraper_id INT UNSIGNED DEFAULT NULL, width INT UNSIGNED DEFAULT NULL, height INT UNSIGNED DEFAULT NULL, src LONGTEXT DEFAULT NULL, feed_id INT UNSIGNED NOT NULL, INDEX IDX_6B3F1A3851A5BC03 (feed_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE social_network_feed_video (id INT UNSIGNED AUTO_INCREMENT NOT NULL, scraper_id INT UNSIGNED DEFAULT NULL, video_type VARCHAR(255) DEFAULT NULL, width INT UNSIGNED DEFAULT NULL, height INT UNSIGNED DEFAULT NULL, bitrate INT UNSIGNED DEFAULT NULL, stream_url LONGTEXT DEFAULT NULL, feed_id INT UNSIGNED NOT NULL, INDEX IDX_34F440C51A5BC03 (feed_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE social_network_feed_photo ADD CONSTRAINT FK_6B3F1A3851A5BC03 FOREIGN KEY (feed_id) REFERENCES social_network_feed (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE social_network_feed_video ADD CONSTRAINT FK_34F440C51A5BC03 FOREIGN KEY (feed_id) REFERENCES social_network_feed (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE social_network_feed_photo DROP FOREIGN KEY FK_6B3F1A3851A5BC03');
        $this->addSql('ALTER TABLE social_network_feed_video DROP FOREIGN KEY FK_34F440C51A5BC03');
        $this->addSql('DROP TABLE social_network_feed');
        $this->addSql('DROP TABLE social_network_feed_photo');
        $this->addSql('DROP TABLE social_network_feed_video');
    }
}
