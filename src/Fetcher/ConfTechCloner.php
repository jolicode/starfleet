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

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class ConfTechCloner
{
    private Filesystem $filesystem;
    private string $projectDir;

    public function __construct(Filesystem $filesystem, string $projectDir)
    {
        $this->filesystem = $filesystem;
        $this->projectDir = $projectDir;
    }

    public function clone(): string
    {
        $conftechFile = $this->projectDir.'/var/tmp/conftech_data/conferences';

        if ($this->filesystem->exists($conftechFile)) {
            $process = new Process(['git', 'pull'], $conftechFile);
        } else {
            $this->filesystem->mkdir($this->projectDir.'/var/tmp');
            $process = new Process(['git', 'clone', '--depth', '1', 'https://github.com/tech-conferences/conference-data/', 'conftech_data/'], $this->projectDir.'/var/tmp');
        }
        $process->mustRun();

        return $conftechFile;
    }
}
