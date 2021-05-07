<?php

declare(strict_types=1);

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210506150419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE participation ADD transport_status VARCHAR(255) NOT NULL DEFAULT \'not_needed\'');
        $this->addSql('ALTER TABLE participation ADD hotel_status VARCHAR(255) NOT NULL DEFAULT \'not_needed\'');
        $this->addSql('ALTER TABLE participation ADD conference_ticket_status VARCHAR(255) NOT NULL DEFAULT \'not_needed\'');
        $this->addSql('ALTER TABLE participation ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE participation ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE participation DROP need_transport');
        $this->addSql('ALTER TABLE participation DROP need_hotel');
        $this->addSql('ALTER TABLE participation DROP need_ticket');
        $this->addSql('ALTER TABLE participation ALTER marking TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE participation ALTER marking SET DEFAULT \'pending\'');
        $this->addSql('UPDATE participation SET marking = \'pending\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE participation ADD need_transport BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE participation ADD need_hotel BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE participation ADD need_ticket BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE participation DROP transport_status');
        $this->addSql('ALTER TABLE participation DROP hotel_status');
        $this->addSql('ALTER TABLE participation DROP conference_ticket_status');
        $this->addSql('ALTER TABLE participation DROP created_at');
        $this->addSql('ALTER TABLE participation DROP updated_at');
        $this->addSql('ALTER TABLE participation ALTER marking TYPE jsonb');
        $this->addSql('ALTER TABLE participation ALTER marking DROP DEFAULT');
    }
}
