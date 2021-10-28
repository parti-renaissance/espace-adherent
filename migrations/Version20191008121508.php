<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191008121508 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE donation_tags (id INT UNSIGNED AUTO_INCREMENT NOT NULL, label VARCHAR(100) NOT NULL, color VARCHAR(255) NOT NULL, UNIQUE INDEX donation_tag_label_unique (label), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE donator_tags (id INT UNSIGNED AUTO_INCREMENT NOT NULL, label VARCHAR(100) NOT NULL, color VARCHAR(255) NOT NULL, UNIQUE INDEX donator_tag_label_unique (label), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE donation_tags');
        $this->addSql('DROP TABLE donator_tags');
    }
}
