<?php

namespace Migrations;

use AppBundle\Entity\AdherentTagEnum;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171003164518 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE adherent_tags (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX adherent_tag_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function postUp(Schema $schema)
    {
        foreach (AdherentTagEnum::values() as $tag) {
            $this->connection->insert('adherent_tags', ['name' => $tag]);
        }
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE adherent_tags');
    }
}
