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

namespace App\Doctrine\ORM\Query\AST\Functions;

/**
 * Implementation of PostgreSql check if left side contains right side (using @>).
 *
 * @see https://www.postgresql.org/docs/9.4/static/functions-array.html
 * @since 0.1
 *
 * @author Martin Georgiev <martin.georgiev@gmail.com>
 * Copied from martin-georgiev/postgresql-for-doctrine package
 */
class Contains extends BaseFunction
{
    protected function customiseFunction(): void
    {
        $this->setFunctionPrototype('(%s @> %s)');
        $this->addNodeMapping('StringPrimary');
        $this->addNodeMapping('StringPrimary');
    }
}
