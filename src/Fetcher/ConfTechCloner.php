<?php

namespace App\Fetcher;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class ConfTechCloner
{
    public function __construct(
        private Filesystem $filesystem,
        private string $projectDir,
    ) {
    }

    public function clone(): string
    {
        $conftechFile = $this->projectDir . '/var/tmp/conftech_data/conferences';

        if ($this->filesystem->exists($conftechFile)) {
            $process = new Process(['git', 'pull'], $conftechFile);
        } else {
            $this->filesystem->mkdir($this->projectDir . '/var/tmp');
            $process = new Process(['git', 'clone', '--depth', '1', 'https://github.com/tech-conferences/conference-data/', 'conftech_data/'], $this->projectDir . '/var/tmp');
        }
        $process->mustRun();

        return $conftechFile;
    }
}
