<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209222019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_favorites (user_id INT NOT NULL, vehicle_id INT NOT NULL, INDEX IDX_E489ED11A76ED395 (user_id), INDEX IDX_E489ED11545317D1 (vehicle_id), PRIMARY KEY(user_id, vehicle_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicle_image (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT NOT NULL, filename VARCHAR(255) NOT NULL, INDEX IDX_A79284B3545317D1 (vehicle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_favorites ADD CONSTRAINT FK_E489ED11A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorites ADD CONSTRAINT FK_E489ED11545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vehicle_image ADD CONSTRAINT FK_A79284B3545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_favorites DROP FOREIGN KEY FK_E489ED11A76ED395');
        $this->addSql('ALTER TABLE user_favorites DROP FOREIGN KEY FK_E489ED11545317D1');
        $this->addSql('ALTER TABLE vehicle_image DROP FOREIGN KEY FK_A79284B3545317D1');
        $this->addSql('DROP TABLE user_favorites');
        $this->addSql('DROP TABLE vehicle_image');
    }
}
