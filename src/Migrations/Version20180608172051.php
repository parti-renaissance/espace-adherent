<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180608172051 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mooc_element_attachment_link (base_mooc_element_id INT UNSIGNED NOT NULL, attachment_link_id INT UNSIGNED NOT NULL, INDEX IDX_324635C7B1828C9D (base_mooc_element_id), INDEX IDX_324635C7653157F7 (attachment_link_id), PRIMARY KEY(base_mooc_element_id, attachment_link_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mooc_element_attachment_file (base_mooc_element_id INT UNSIGNED NOT NULL, attachment_file_id INT UNSIGNED NOT NULL, INDEX IDX_88759A26B1828C9D (base_mooc_element_id), INDEX IDX_88759A265B5E2CEA (attachment_file_id), PRIMARY KEY(base_mooc_element_id, attachment_file_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mooc_attachment_file (id INT UNSIGNED AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, extension VARCHAR(255) NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mooc_element_attachment_link ADD CONSTRAINT FK_324635C7B1828C9D FOREIGN KEY (base_mooc_element_id) REFERENCES mooc_elements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mooc_element_attachment_link ADD CONSTRAINT FK_324635C7653157F7 FOREIGN KEY (attachment_link_id) REFERENCES mooc_attachment_link (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mooc_element_attachment_file ADD CONSTRAINT FK_88759A26B1828C9D FOREIGN KEY (base_mooc_element_id) REFERENCES mooc_elements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mooc_element_attachment_file ADD CONSTRAINT FK_88759A265B5E2CEA FOREIGN KEY (attachment_file_id) REFERENCES mooc_attachment_file (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE mooc_video_attachment_link');
        $this->addSql('ALTER TABLE mooc_elements ADD position SMALLINT NOT NULL, ADD type_form LONGTEXT DEFAULT NULL, DROP display_order');
        $this->addSql('CREATE UNIQUE INDEX mooc_element_slug ON mooc_elements (slug, chapter_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_element_attachment_file DROP FOREIGN KEY FK_88759A265B5E2CEA');
        $this->addSql('CREATE TABLE mooc_video_attachment_link (video_id INT UNSIGNED NOT NULL, attachment_link_id INT UNSIGNED NOT NULL, UNIQUE INDEX UNIQ_2A88515653157F7 (attachment_link_id), INDEX IDX_2A8851529C1004E (video_id), PRIMARY KEY(video_id, attachment_link_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mooc_video_attachment_link ADD CONSTRAINT FK_2A8851529C1004E FOREIGN KEY (video_id) REFERENCES mooc_elements (id)');
        $this->addSql('ALTER TABLE mooc_video_attachment_link ADD CONSTRAINT FK_2A88515653157F7 FOREIGN KEY (attachment_link_id) REFERENCES mooc_attachment_link (id)');
        $this->addSql('DROP TABLE mooc_element_attachment_link');
        $this->addSql('DROP TABLE mooc_element_attachment_file');
        $this->addSql('DROP TABLE mooc_attachment_file');
        $this->addSql('DROP INDEX mooc_element_slug ON mooc_elements');
        $this->addSql('ALTER TABLE mooc_elements ADD display_order SMALLINT DEFAULT NULL, DROP position, DROP type_form');
    }
}
