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
            'GIT_CONFIG_COUNT' => '3',
            'GIT_CONFIG_KEY_0' => 'safe.directory',
            'GIT_CONFIG_VALUE_0' => '/var/ivy-cultivate',
            'GIT_CONFIG_KEY_1' => 'safe.directory',
            'GIT_CONFIG_VALUE_1' => '/var/ivy-roots',
            'GIT_CONFIG_KEY_2' => 'safe.directory',
            'GIT_CONFIG_VALUE_2' => '/var/ivy-sprout',
        ];
    }

    public function requirePackage(string $package): void
    {
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

    public static function findPlugins(string $type = 'ivy-plugin', int $limit = 25): array
    {
        $listUrl = "https://packagist.org/packages/list.json?type=" . rawurlencode($type) . "&limit=" . $limit;
        $namesJson = file_get_contents($listUrl);
        if ($namesJson === false) {
            throw new \RuntimeException("Failed to fetch Packagist list: " . $listUrl);
        }

        $namesData = json_decode($namesJson, true);
        $packageNames = $namesData['packageNames'] ?? [];

        $p = [];

        foreach ($packageNames as $fullName) {
            [$vendor, $package] = explode('/', $fullName, 2);

            $url = "https://repo.packagist.org/p2/".$vendor."/".$package.".json";
            $json = file_get_contents($url);
            if ($json === false) {
                continue;
            }

            $meta = json_decode($json, true);
            if (!$meta || !isset($meta['packages'][$fullName])) {
                continue;
            }

            $versions = $meta['packages'][$fullName];
            if (!is_array($versions)) {
                continue;
            }

            $v = [];

            foreach ($versions as $version) {
                $v[] = $version['version'];
            }

            $p[] = [
                'package' => $fullName,
                'versions' => $v,
            ];
        }

        return $p;
    }

}
