<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210819085308 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conference ADD featured BOOLEAN DEFAULT NULL');
        $this->addSql('UPDATE conference SET featured = false');
        $this->addSql('ALTER TABLE conference ALTER COLUMN featured SET NOT NULL');
        $this->addSql('ALTER TABLE conference ALTER featured DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conference DROP featured');
    }
}
