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
