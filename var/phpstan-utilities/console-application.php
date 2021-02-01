<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

require __DIR__.'../../../config/bootstrap.php';
$kernel = new \App\Kernel('phpstan', true);

return new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
