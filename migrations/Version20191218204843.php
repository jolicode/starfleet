<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191218204843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE users ADD job VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD twitter_account VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD tshirt_size VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD food_preferences TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD allergies TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA starfleet');
        $this->addSql('ALTER TABLE users DROP job');
        $this->addSql('ALTER TABLE users DROP twitter_account');
        $this->addSql('ALTER TABLE users DROP tshirt_size');
        $this->addSql('ALTER TABLE users DROP food_preferences');
        $this->addSql('ALTER TABLE users DROP allergies');
    }
}
