<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Fetcher;

use Symfony\Component\Form\FormBuilderInterface;

interface FetcherInterface
{
    public function fetch(array $configuration = []): ?\Generator;

    public function configureForm(FormBuilderInterface $formBuilder): FormBuilderInterface;
}
