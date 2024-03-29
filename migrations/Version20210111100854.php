<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210111100854 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conference ADD online BOOLEAN DEFAULT NULL');
        $this->addSql('UPDATE conference SET online = false');
        $this->addSql('UPDATE conference SET online = true WHERE LOWER(city) = \'online\'');
        $this->addSql('ALTER TABLE conference ALTER online SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE conference DROP online');
    }
}
