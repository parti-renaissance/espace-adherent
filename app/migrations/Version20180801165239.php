<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180801165239 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE turnkey_projects (
                        id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                        category_id INT UNSIGNED DEFAULT NULL, 
                        name VARCHAR(255) NOT NULL, 
                        canonical_name VARCHAR(255) NOT NULL, 
                        slug VARCHAR(255) NOT NULL, 
                        subtitle VARCHAR(255) NOT NULL, 
                        problem_description LONGTEXT DEFAULT NULL, 
                        proposed_solution LONGTEXT DEFAULT NULL, 
                        required_means LONGTEXT DEFAULT NULL, 
                        image_name VARCHAR(255) DEFAULT NULL, 
                        youtube_id VARCHAR(11) DEFAULT NULL,
                        is_pinned TINYINT(1) DEFAULT \'0\' NOT NULL, 
                        is_favorite TINYINT(1) DEFAULT \'0\' NOT NULL, 
                        position SMALLINT DEFAULT 1 NOT NULL,
                        INDEX IDX_CB66CFAE12469DE2 (category_id), 
                        UNIQUE INDEX turnkey_project_canonical_name_unique (canonical_name), 
                        UNIQUE INDEX turnkey_project_slug_unique (slug), 
                        PRIMARY KEY(id)
                    ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE turnkey_projects ADD CONSTRAINT FK_CB66CFAE12469DE2 FOREIGN KEY (category_id) REFERENCES citizen_project_categories (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE turnkey_projects');
    }
}
