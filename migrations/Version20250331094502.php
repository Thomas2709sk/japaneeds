<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250331094502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0FB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservations (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users CHANGE credits credits INT NOT NULL
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
            ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0FB83297E7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users CHANGE credits credits INT DEFAULT 20 NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users_reservations DROP FOREIGN KEY FK_5309843567B3B43D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users_reservations DROP FOREIGN KEY FK_53098435D9A7F869
        SQL);
    }
}
