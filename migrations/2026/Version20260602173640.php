<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260602173640 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  social_network_feed
                ADD
                  public_image_path LONGTEXT DEFAULT NULL,
                ADD
                  public_avatar_image_path LONGTEXT DEFAULT NULL,
                ADD
                  image_width INT UNSIGNED DEFAULT NULL,
                ADD
                  image_height INT UNSIGNED DEFAULT NULL,
                ADD
                  avatar_width INT UNSIGNED DEFAULT NULL,
                ADD
                  avatar_height INT UNSIGNED DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE social_network_feed_photo ADD public_src LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE social_network_feed_video DROP FOREIGN KEY `FK_34F440C29C1004E`');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  social_network_feed_video
                ADD
                  CONSTRAINT FK_34F440C29C1004E FOREIGN KEY (video_id) REFERENCES video (id) ON DELETE
                SET
                  NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  social_network_feed
                DROP
                  public_image_path,
                DROP
                  public_avatar_image_path,
                DROP
                  image_width,
                DROP
                  image_height,
                DROP
                  avatar_width,
                DROP
                  avatar_height
            SQL);
        $this->addSql('ALTER TABLE social_network_feed_photo DROP public_src');
        $this->addSql('ALTER TABLE social_network_feed_video DROP FOREIGN KEY FK_34F440C29C1004E');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  social_network_feed_video
                ADD
                  CONSTRAINT `FK_34F440C29C1004E` FOREIGN KEY (video_id) REFERENCES video (id) ON
                UPDATE
                  NO ACTION ON DELETE NO ACTION
            SQL);
    }
}
