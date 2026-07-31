<?php

namespace Ivy\Sprout;

use Ivy\Shared\Core\Path;
use Symfony\Component\Process\Process;

class ComposerRunner
{
    private function getComposerEnv(): array
    {
        $cacheBase = rtrim(Path::get('ROOT'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cache';
        $composerHome = $cacheBase . DIRECTORY_SEPARATOR . 'composer';

        if (! is_dir($composerHome) && ! mkdir($composerHome, 0775, true) && ! is_dir($composerHome)) {
            throw new \RuntimeException("Cannot create composer home: {$composerHome}");
        }

        return [
            'HOME' => $cacheBase,
            'COMPOSER_HOME' => $composerHome,
            'COMPOSER_ALLOW_SUPERUSER' => '1',
        ];
    }

    private function ensureGitSafeDirectory(string $repoPath): void
    {
        $env = $this->getComposerEnv();

        $gitConfigGlobal = $env['GIT_CONFIG_GLOBAL'];
        if (! file_exists($gitConfigGlobal)) {
            @touch($gitConfigGlobal);
        }

        $process = new Process(
            [
                'git',
                'config',
                '--global',
                '--add',
                'safe.directory',
                $repoPath,
            ],
            Path::get('PROJECT_PATH'),
            $env
        );

        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() ?: $process->getOutput());
        }
    }

    public function requirePackage(string $package): void
    {
        $this->ensureGitSafeDirectory(Path::get('PROJECT_PATH'));

        $process = new Process(
            [
                'composer',
                'require',
                $package,
                '--no-interaction',
                '--no-progress',
                '--prefer-dist',
                '--no-scripts'
            ],
            Path::get('PROJECT_PATH'),
            $this->getComposerEnv()
        );

        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() ?: $process->getOutput());
        }
    }

    public function removePackage(string $package): void
    {
        $process = new Process(
            [
                'composer',
                'remove',
                $package,
                '--no-interaction',
                '--no-progress',
                '--prefer-dist',
                '--no-scripts'
            ],
            Path::get('PROJECT_PATH'),
            $this->getComposerEnv()
        );

        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() ?: $process->getOutput());
        }
    }
}
