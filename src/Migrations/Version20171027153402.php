<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171027153402 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE groups (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, canonical_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, status VARCHAR(20) NOT NULL, approved_at DATETIME DEFAULT NULL, refused_at DATETIME DEFAULT NULL, created_by CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\', members_counts SMALLINT UNSIGNED NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, address_address VARCHAR(150) DEFAULT NULL, address_postal_code VARCHAR(15) DEFAULT NULL, address_city_insee VARCHAR(15) DEFAULT NULL, address_city_name VARCHAR(255) DEFAULT NULL, address_country VARCHAR(2) DEFAULT NULL, address_latitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', address_longitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', INDEX group_status_idx (status), UNIQUE INDEX group_uuid_unique (uuid), UNIQUE INDEX group_canonical_name_unique (canonical_name), UNIQUE INDEX group_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_feed_items (id INT UNSIGNED AUTO_INCREMENT NOT NULL, group_id INT UNSIGNED DEFAULT NULL, author_id INT UNSIGNED DEFAULT NULL, event_id INT UNSIGNED DEFAULT NULL, item_type VARCHAR(18) NOT NULL, content LONGTEXT DEFAULT NULL, published TINYINT(1) DEFAULT \'1\' NOT NULL, created_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_C35CDB43FE54D947 (group_id), INDEX IDX_C35CDB43F675F31B (author_id), INDEX IDX_C35CDB4371F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_memberships (id INT UNSIGNED AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED DEFAULT NULL, group_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', privilege VARCHAR(15) NOT NULL, joined_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_A3E258B725F06C53 (adherent_id), INDEX groups_memberships_role_idx (privilege), UNIQUE INDEX adherent_has_joined_group (adherent_id, group_uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE group_feed_items ADD CONSTRAINT FK_C35CDB43FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id)');
        $this->addSql('ALTER TABLE group_feed_items ADD CONSTRAINT FK_C35CDB43F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE group_feed_items ADD CONSTRAINT FK_C35CDB4371F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE group_memberships ADD CONSTRAINT FK_A3E258B725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE group_feed_items DROP FOREIGN KEY FK_C35CDB43FE54D947');
        $this->addSql('DROP TABLE groups');
        $this->addSql('DROP TABLE group_feed_items');
        $this->addSql('DROP TABLE group_memberships');
    }
}
