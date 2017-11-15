<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171115100101 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE pages ADD page_title VARCHAR(100) NOT NULL');
        $this->addSql('UPDATE pages SET page_title \'Mentions légales\' WHERE slug = \'mentions-legales\'');
        $this->addSql('UPDATE pages SET page_title \'Politique de Cookies\' WHERE slug = \'politique-cookies\'');
        $this->addSql('UPDATE pages SET slug = \'emmanuel-macron\', page_title \'Emmanuel Macron - Ce que je suis\' WHERE slug = \'emmanuel-macron-ce-que-je-suis\'');
        $this->addSql('UPDATE pages SET slug = \'emmanuel-macron/revolution\', page_title \'Emmanuel Macron - Révolution\' WHERE slug = \'emmanuel-macron-revolution\'');
        $this->addSql('UPDATE pages SET slug = \'le-mouvement\', page_title \'Le mouvement - Nos valeurs\' WHERE slug = \'le-mouvement-nos-valeurs\'');
        $this->addSql('UPDATE pages SET slug = \'le-mouvement/notre-organisation\' WHERE slug = \'le-mouvement-notre-organisation\'');
        $this->addSql('UPDATE pages SET slug = \'le-mouvement/les-comites\', page_title \'Le mouvement - Les comités\' WHERE slug = \'le-mouvement-les-comites\'');
        $this->addSql('UPDATE pages SET slug = \'le-mouvement/devenez-benevole\', page_title \'Le mouvement - Devenez bénévole\' WHERE slug = \'le-mouvement-devenez-benevole\'');
        $this->addSql('UPDATE pages SET slug = \'action-talents\' WHERE slug = \'action-talents-home\'');
        $this->addSql('UPDATE pages SET slug = \'action-talents/candidater\' WHERE slug = \'action-talents-apply\'');
        $this->addSql('UPDATE pages SET slug = \'nos-offres\', page_title \'Nos offres d\'\'emploi et stages\' WHERE slug = \'jobs\'');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE pages DROP page_title');
        $this->addSql('UPDATE pages SET slug = \'emmanuel-macron-ce-que-je-suis\' WHERE slug = \'emmanuel-macron\'');
        $this->addSql('UPDATE pages SET slug = \'emmanuel-macron-revolution\' WHERE slug = \'emmanuel-macron/revolution\'');
        $this->addSql('UPDATE pages SET slug = \'le-mouvement-nos-valeurs\' WHERE slug = \'le-mouvement\'');
        $this->addSql('UPDATE pages SET slug = \'le-mouvement-notre-organisation\' WHERE slug = \'le-mouvement/notre-organisation\'');
        $this->addSql('UPDATE pages SET slug = \'le-mouvement-les-comites\' WHERE slug = \'le-mouvement/les-comites\'');
        $this->addSql('UPDATE pages SET slug = \'le-mouvement-devenez-benevole\' WHERE slug = \'le-mouvement/devenez-benevole\'');
        $this->addSql('UPDATE pages SET slug = \'action-talents-home\' WHERE slug = \'action-talents\'');
        $this->addSql('UPDATE pages SET slug = \'action-talents-apply\' WHERE slug = \'action-talents/candidater\'');
        $this->addSql('UPDATE pages SET slug = \'jobs\' WHERE slug = \'nos-offres\'');
    }
}
