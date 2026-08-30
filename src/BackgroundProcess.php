<?php

namespace Ivy\Sprout;

use Ivy\Shared\Core\Path;

final class BackgroundProcess
{
    private readonly string $projectPath;

    public function __construct()
    {
        $this->projectPath = Path::get('PROJECT_PATH');
    }

    public function require(string $package): void
    {
        $this->runComposer('require', $package);
    }

    public function update(string $package): void
    {
        $this->runComposer('update', $package);
    }

    public function remove(string $package): void
    {
        $this->runComposer('remove', $package);
    }

    private function runComposer(string $command, string $package): void
    {
        $cache = $this->projectPath . 'cache';
        $log = $this->projectPath . 'logs/composer.log';

        $cmd = sprintf(
            'cd %s && HOME=%s COMPOSER_HOME=%s composer %s %s >> %s 2>&1 < /dev/null &',
            escapeshellarg($this->projectPath),
            escapeshellarg($cache),
            escapeshellarg($cache . '/composer'),
            $command,
            escapeshellarg($package),
            escapeshellarg($log),
        );

        exec($cmd);
    }
}