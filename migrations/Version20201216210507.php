<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201216210507 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conference RENAME COLUMN location TO city');
        $this->addSql('ALTER TABLE conference ALTER city DROP NOT NULL');
        $this->addSql('ALTER TABLE conference ALTER city SET DEFAULT NULL');
        $this->addSql('ALTER TABLE conference ADD country VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conference RENAME COLUMN city TO location');
        $this->addSql('ALTER TABLE conference DROP country');
    }
}
