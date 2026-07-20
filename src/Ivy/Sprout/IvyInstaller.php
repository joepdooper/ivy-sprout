<?php

namespace Ivy\Sprout;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class IvyInstaller implements PluginInterface
{
    public function activate(
        Composer $composer,
        IOInterface $io
    ): void {
        $io->write('Ivy Sprout activated');
    }

    public function deactivate(
        Composer $composer,
        IOInterface $io
    ): void {
    }

    public function uninstall(
        Composer $composer,
        IOInterface $io
    ): void {
    }
}