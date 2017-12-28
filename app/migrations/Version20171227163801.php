<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171227163801 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('UPDATE pages SET slug=\'emmanuel-macron\' WHERE slug= \'emmanuel-macron-ce-que-je-suis\'');
        $this->addSql('UPDATE pages SET slug=\'emmanuel-macron/revolution\' WHERE slug= \'emmanuel-macron-revolution\'');
        $this->addSql('UPDATE pages SET slug=\'le-mouvement\' WHERE slug= \'le-mouvement-nos-valeurs\'');
        $this->addSql('UPDATE pages SET slug=\'le-mouvement/notre-organisation\' WHERE slug= \'le-mouvement-notre-organisation\'');
        $this->addSql('UPDATE pages SET slug=\'le-mouvement/les-comites\' WHERE slug= \'le-mouvement-les-comites\'');
        $this->addSql('UPDATE pages SET slug=\'le-mouvement/devenez-benevole\' WHERE slug= \'le-mouvement-devenez-benevole\'');
        $this->addSql('UPDATE pages SET slug=\'action-talents\' WHERE slug= \'action-talents-home\'');
        $this->addSql('UPDATE pages SET slug=\'action-talents/candidater\' WHERE slug= \'action-talents-apply\'');
        $this->addSql('UPDATE pages SET slug=\'nos-offres\' WHERE slug= \'jobs\'');
    }

    public function down(Schema $schema)
    {
        $this->addSql('UPDATE pages SET slug=\'emmanuel-macron-ce-que-je-suis\' WHERE slug= \'emmanuel-macron\'');
        $this->addSql('UPDATE pages SET slug=\'emmanuel-macron-revolution\' WHERE slug= \'emmanuel-macron/revolution\'');
        $this->addSql('UPDATE pages SET slug=\'le-mouvement-nos-valeurs\' WHERE slug= \'le-mouvement\'');
        $this->addSql('UPDATE pages SET slug=\'le-mouvement-notre-organisation\' WHERE slug= \'le-mouvement/notre-organisation\'');
        $this->addSql('UPDATE pages SET slug=\'le-mouvement-les-comites\' WHERE slug= \'le-mouvement/les-comites\'');
        $this->addSql('UPDATE pages SET slug=\'le-mouvement-devenez-benevole\' WHERE slug= \'le-mouvement/devenez-benevole\'');
        $this->addSql('UPDATE pages SET slug=\'action-talents-home\' WHERE slug= \'action-talents\'');
        $this->addSql('UPDATE pages SET slug=\'action-talents-apply\' WHERE slug= \'action-talents/candidater\'');
        $this->addSql('UPDATE pages SET slug=\'jobs\' WHERE slug= \'nos-offres\'');
    }
}
