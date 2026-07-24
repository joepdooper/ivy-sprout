<?php

namespace Ivy\Sprout;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

class IvyExtensionInstaller extends LibraryInstaller
{
    public function supports(string $packageType): bool
    {
        return in_array($packageType, [
            'ivy-plugin',
            'ivy-template',
        ], true);
    }

    public function getInstallPath(PackageInterface $package): string
    {
        $name = $package->getPrettyName();
        $parts = explode('/', $name);

        $directory = end($parts);

        return match ($package->getType()) {
            'ivy-plugin' => 'plugins/'.$directory,
            'ivy-template' => 'templates/'.$directory,
            default => parent::getInstallPath($package),
        };
    }
}