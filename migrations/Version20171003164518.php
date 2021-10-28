<?php

namespace Migrations;

use App\Entity\AdherentTagEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171003164518 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_tags (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX adherent_tag_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function postUp(Schema $schema): void
    {
        foreach (AdherentTagEnum::values() as $tag) {
            $this->connection->insert('adherent_tags', ['name' => $tag]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE adherent_tags');
    }
}
