<?php

namespace Ivy\Sprout;

use Ivy\Shared\Core\Path;
use Symfony\Component\Process\Process;

class ComposerRunner
{
    public static function getPluginCatalog(string $type = 'ivy-plugin', int $page = 1, int $per_page = 25): array
    {
        $listUrl = "https://packagist.org/packages/list.json?type=" . rawurlencode($type) . "&page=" . $page . "&per_page=" . $per_page;
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
                'description' => $meta['packages'][$fullName][0]['description'] ?? null,
                'extra' => $meta['packages'][$fullName][0]['extra'] ?? null,
            ];
        }

        return $p;
    }
}
