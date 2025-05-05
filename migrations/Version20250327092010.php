<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250327092010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE cities (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(60) NOT NULL, slug VARCHAR(60) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE guides (id INT AUTO_INCREMENT NOT NULL, speciality_id INT NOT NULL, user_id INT DEFAULT NULL, nb_places INT NOT NULL, languages VARCHAR(255) NOT NULL, smoking TINYINT(1) NOT NULL, description LONGTEXT NOT NULL, prefernces VARCHAR(255) DEFAULT NULL, INDEX IDX_4D7795EF3B5A08D7 (speciality_id), UNIQUE INDEX UNIQ_4D7795EFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE guides_cities (guides_id INT NOT NULL, cities_id INT NOT NULL, INDEX IDX_BAA1BBD96A8C820 (guides_id), INDEX IDX_BAA1BBD9CAC75398 (cities_id), PRIMARY KEY(guides_id, cities_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservations (id INT AUTO_INCREMENT NOT NULL, guide_id INT NOT NULL, city_id INT NOT NULL, day DATE NOT NULL, begin TIME NOT NULL, end TIME NOT NULL, meal TINYINT(1) NOT NULL, price INT NOT NULL, INDEX IDX_4DA239D7ED1D4B (guide_id), INDEX IDX_4DA2398BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reviews (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, guide_id INT NOT NULL, reservation_id INT NOT NULL, rate INT NOT NULL, commentary LONGTEXT NOT NULL, INDEX IDX_6970EB0FA76ED395 (user_id), INDEX IDX_6970EB0FD7ED1D4B (guide_id), INDEX IDX_6970EB0FB83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE specialities (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(60) NOT NULL, slug VARCHAR(60) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', password VARCHAR(255) NOT NULL, pseudo VARCHAR(255) NOT NULL, credits INT NOT NULL, picture VARCHAR(255) DEFAULT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users_reservations (users_id INT NOT NULL, reservations_id INT NOT NULL, INDEX IDX_5309843567B3B43D (users_id), INDEX IDX_53098435D9A7F869 (reservations_id), PRIMARY KEY(users_id, reservations_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE guides ADD CONSTRAINT FK_4D7795EF3B5A08D7 FOREIGN KEY (speciality_id) REFERENCES specialities (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE guides ADD CONSTRAINT FK_4D7795EFA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE guides_cities ADD CONSTRAINT FK_BAA1BBD96A8C820 FOREIGN KEY (guides_id) REFERENCES guides (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE guides_cities ADD CONSTRAINT FK_BAA1BBD9CAC75398 FOREIGN KEY (cities_id) REFERENCES cities (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations ADD CONSTRAINT FK_4DA239D7ED1D4B FOREIGN KEY (guide_id) REFERENCES guides (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations ADD CONSTRAINT FK_4DA2398BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0FD7ED1D4B FOREIGN KEY (guide_id) REFERENCES guides (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0FB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservations (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users_reservations ADD CONSTRAINT FK_5309843567B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users_reservations ADD CONSTRAINT FK_53098435D9A7F869 FOREIGN KEY (reservations_id) REFERENCES reservations (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE guides DROP FOREIGN KEY FK_4D7795EF3B5A08D7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE guides DROP FOREIGN KEY FK_4D7795EFA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE guides_cities DROP FOREIGN KEY FK_BAA1BBD96A8C820
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE guides_cities DROP FOREIGN KEY FK_BAA1BBD9CAC75398
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239D7ED1D4B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations DROP FOREIGN KEY FK_4DA2398BAC62AF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0FA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0FD7ED1D4B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0FB83297E7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users_reservations DROP FOREIGN KEY FK_5309843567B3B43D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users_reservations DROP FOREIGN KEY FK_53098435D9A7F869
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cities
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE guides
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE guides_cities
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reviews
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE specialities
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users_reservations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
