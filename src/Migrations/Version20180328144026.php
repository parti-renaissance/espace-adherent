<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180328144026 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE organizational_chart_item (id INT UNSIGNED AUTO_INCREMENT NOT NULL, tree_root INT UNSIGNED DEFAULT NULL, parent_id INT UNSIGNED DEFAULT NULL, label VARCHAR(255) NOT NULL, lft INT NOT NULL, lvl INT NOT NULL, rgt INT NOT NULL, type VARCHAR(20) NOT NULL, INDEX IDX_29C1CBACA977936C (tree_root), INDEX IDX_29C1CBAC727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referent_person_link (id INT UNSIGNED AUTO_INCREMENT NOT NULL, person_organizational_chart_item_id INT UNSIGNED DEFAULT NULL, referent_id SMALLINT UNSIGNED DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, postal_address VARCHAR(255) DEFAULT NULL, INDEX IDX_BC75A60A810B5A42 (person_organizational_chart_item_id), INDEX IDX_BC75A60A35E47E35 (referent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE organizational_chart_item ADD CONSTRAINT FK_4300BEE5A977936C FOREIGN KEY (tree_root) REFERENCES organizational_chart_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE organizational_chart_item ADD CONSTRAINT FK_4300BEE5727ACA70 FOREIGN KEY (parent_id) REFERENCES organizational_chart_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE referent_person_link ADD CONSTRAINT FK_BC75A60A810B5A42 FOREIGN KEY (person_organizational_chart_item_id) REFERENCES organizational_chart_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE referent_person_link ADD CONSTRAINT FK_BC75A60A35E47E35 FOREIGN KEY (referent_id) REFERENCES referent (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE organizational_chart_item DROP FOREIGN KEY FK_4300BEE5A977936C');
        $this->addSql('ALTER TABLE organizational_chart_item DROP FOREIGN KEY FK_4300BEE5727ACA70');
        $this->addSql('ALTER TABLE referent_person_link DROP FOREIGN KEY FK_BC75A60A810B5A42');
        $this->addSql('DROP TABLE organizational_chart_item');
        $this->addSql('DROP TABLE referent_person_link');
    }
}
