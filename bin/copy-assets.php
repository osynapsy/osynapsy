#!/usr/bin/env php
<?php

$baseDir = realpath(__DIR__ . '/../');
$vendorDir = $baseDir . '/vendor/osynapsy';
$publicVendorDir = $baseDir . '/public/assets/vendor/osynapsy';

if (!is_dir($vendorDir)) {
    echo "Vendor osynapsy directory not found.\n";
    exit(1);
}

$packages = glob($vendorDir . '/*', GLOB_ONLYDIR);

foreach ($packages as $packagePath) {
    $packageName = basename($packagePath);
    $assetSource = $packagePath . '/assets';

    // 1. Copia degli assets
    if (is_dir($assetSource)) {
        $assetTarget = $publicVendorDir . '/' . $packageName;
        echo "📦 Copio assets da $packageName...\n";
        recursiveCopy($assetSource, $assetTarget);
    }

    // 2. Esecuzione script post-package-install
    $composerJsonPath = $packagePath . '/composer.json';
    if (file_exists($composerJsonPath)) {
        $composerData = json_decode(file_get_contents($composerJsonPath), true);
        if (isset($composerData['scripts']['post-package-install'])) {
            $commands = $composerData['scripts']['post-package-install'];
            if (is_string($commands)) {
                $commands = [$commands];
            }

            foreach ($commands as $cmd) {
                echo "🔧 Eseguo script '$cmd' da $packageName...\n";
                chdir($packagePath);
                passthru($cmd);
            }
        }
    }
}

echo "✅ Operazione completata.\n";

// Funzione per copia ricorsiva
function recursiveCopy($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst, 0777, true);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        if (is_dir($srcPath)) {
            recursiveCopy($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
        }
    }
    closedir($dir);
}
