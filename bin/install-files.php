#!/usr/bin/env php
<?php

/**
 * Copies this SDK's ready-to-use application/ files (examples/application/)
 * into the consuming CI3 project's own application/ directory.
 *
 * Usage, from the project root (after `composer require cuongcds/ci3-open-sso`):
 *   php vendor/cuongcds/ci3-open-sso/bin/install-files.php
 *
 * Existing files are never overwritten; use --force to replace them.
 */

$force = in_array('--force', $argv, true);

$source = __DIR__ . '/../examples/application';
$projectRoot = detectProjectRoot();
$destination = $projectRoot . '/application';

if (!is_dir($source)) {
    fwrite(STDERR, "Source not found: {$source}\n");
    exit(1);
}

if (!is_dir($destination)) {
    fwrite(STDERR, "No application/ directory found at {$projectRoot} — run this from your CI3 project root.\n");
    exit(1);
}

$copied = [];
$skipped = [];
copyRecursive($source, $destination, $force, $copied, $skipped);

foreach ($copied as $path) {
    echo "  copied  {$path}\n";
}
foreach ($skipped as $path) {
    echo "  skipped {$path} (already exists, use --force to overwrite)\n";
}

echo sprintf("\n%d file(s) copied, %d skipped.\n", count($copied), count($skipped));

function detectProjectRoot(): string
{
    // bin/ -> ci3-open-sso/ -> cuongcds/ -> vendor/ -> project root
    return dirname(__DIR__, 4);
}

function copyRecursive(string $source, string $destination, bool $force, array &$copied, array &$skipped): void
{
    foreach (scandir($source) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $sourcePath = $source . '/' . $entry;
        $destPath = $destination . '/' . $entry;

        if (is_dir($sourcePath)) {
            if (!is_dir($destPath)) {
                mkdir($destPath, 0755, true);
            }
            copyRecursive($sourcePath, $destPath, $force, $copied, $skipped);
            continue;
        }

        if (is_file($destPath) && !$force) {
            $skipped[] = relativeToProject($destPath);
            continue;
        }

        copy($sourcePath, $destPath);
        $copied[] = relativeToProject($destPath);
    }
}

function relativeToProject(string $path): string
{
    $root = detectProjectRoot() . '/';
    return str_starts_with($path, $root) ? substr($path, strlen($root)) : $path;
}
