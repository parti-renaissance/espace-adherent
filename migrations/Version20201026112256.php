<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201026112256 extends AbstractMigration
{
    /*
     * Migrate "jeunesse" and "education" to the new "jeunesse_education"
     */
    public function up(Schema $schema) : void
    {
        $this->addSql('UPDATE adherents SET interests = CONCAT_WS(\',\', interests, \'jeunesse_education\') 
            WHERE FIND_IN_SET(\'jeunesse\', interests) > 0
            OR FIND_IN_SET(\'education\', interests) > 0;'
        );

        $this->addSql('UPDATE adherents SET interests =
            TRIM(BOTH \',\' FROM REPLACE(CONCAT(\',\', interests , \',\'), CONCAT(\',\', \'jeunesse\', \',\'), \',\'))
            WHERE FIND_IN_SET(\'jeunesse\', interests) > 0;'
        );

        $this->addSql('UPDATE adherents SET interests =
            TRIM(BOTH \',\' FROM REPLACE(CONCAT(\',\', interests , \',\'), CONCAT(\',\', \'education\', \',\'), \',\'))
            WHERE FIND_IN_SET(\'education\', interests) > 0;'
        );
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('UPDATE adherents SET interests = CONCAT_WS(\',\', interests, \'jeunesse,education\') 
            WHERE FIND_IN_SET(\'jeunesse_education\', interests) > 0'
        );

        $this->addSql('UPDATE adherents SET interests =
            TRIM(BOTH \',\' FROM REPLACE(CONCAT(\',\', interests , \',\'), CONCAT(\',\', \'jeunesse_education\', \',\'), \',\'))
            WHERE FIND_IN_SET(\'jeunesse_education\', interests) > 0;'
        );
    }
}
