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

final class Version20210223150819 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE conference SET country = UPPER(country)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE conference SET country = LOWER(country)');
    }
}
