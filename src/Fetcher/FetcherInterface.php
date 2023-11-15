<?php

namespace App\Fetcher;

use App\Entity\Conference;
use Symfony\Component\Form\FormBuilderInterface;

interface FetcherInterface
{
    /**
     * @param array<mixed> $configuration
     *
     * @return \Generator<Conference>
     */
    public function fetch(array $configuration = []): \Generator;

    public function configureForm(FormBuilderInterface $formBuilder): void;
}
