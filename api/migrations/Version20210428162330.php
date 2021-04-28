<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210428162330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, title VARCHAR(100) NOT NULL, content LONGTEXT NOT NULL, created_at DATE NOT NULL, active TINYINT(1) NOT NULL, validated TINYINT(1) NOT NULL, validated_at DATE DEFAULT NULL, INDEX IDX_5A8A6C8DB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_image (id INT AUTO_INCREMENT NOT NULL, post_id INT NOT NULL, image VARCHAR(255) NOT NULL, description VARCHAR(140) NOT NULL, INDEX IDX_522688B04B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post_image ADD CONSTRAINT FK_522688B04B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_image DROP FOREIGN KEY FK_522688B04B89032C');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE post_image');
    }
}
