<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180828104646 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            "UPDATE subscription_type SET label = 'Recevoir les e-mails de votre porteur de projet' WHERE label = 'Recevoir les e-mails de votre porteuse ou porteur de projet'"
        );
        $this->addSql(
            "UPDATE subscription_type SET label = 'Recevoir les e-mails de votre animateur local de comité' WHERE label = 'Recevoir les e-mails de votre animateur local'"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            "UPDATE subscription_type SET label = 'Recevoir les e-mails de votre porteuse ou porteur de projet' WHERE label = 'Recevoir les e-mails de votre porteur de projet'"
        );
        $this->addSql(
            "UPDATE subscription_type SET label = 'Recevoir les e-mails de votre animateur local' WHERE label = 'Recevoir les e-mails de votre animateur local de comité'"
        );
    }
}
