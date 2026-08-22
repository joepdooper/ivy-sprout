<?php

namespace Ivy\Sprout;

use Ivy\Shared\Core\Path;

final class BackgroundProcess
{
    public static function require(string $package): void
    {
        $projectPath = Path::get('PROJECT_PATH');
        $cacheBase = $projectPath . 'cache';
        $composerHome = $cacheBase . '/composer';

        exec(
            'cd ' . $projectPath . ' && ' .
            'HOME=' . $cacheBase . ' ' .
            'COMPOSER_HOME=' . $composerHome . ' ' .
            'composer require ' . escapeshellarg($package) .
            ' >> /tmp/plugin-download.log 2>&1 < /dev/null &'
        );
    }
}