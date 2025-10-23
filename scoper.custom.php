<?php

declare(strict_types=1);

/*
 * This file is part of the Wp-App WordPress plugin.
 *
 * (ɔ) Frugan <dev@frugan.it>
 *
 * This source file is subject to the GNU GPLv3 or later license that is bundled
 * with this source code in the file LICENSE.
 */

function customize_php_scoper_config(array $config): array
{
    $config['patchers'][] = static function (string $filePath, string $prefix, string $content): string {
        // wpify/scoper copies files to a temp directory, so we need to get the original project root
        // The temp dir is like: /project/root/tmp-xxxxx/
        // So we need to go up one level from __DIR__
        $projectRoot = dirname(__DIR__);
        $patchesDir = $projectRoot.'/patches-deps';

        if (!is_dir($patchesDir)) {
            return $content;
        }

        // Get all patch files
        $patches = glob($patchesDir.'/*.patch');

        foreach ($patches as $patch) {
            // Read patch to extract target file path
            $patchContent = file_get_contents($patch);

            // Extract file path from patch (e.g., "diff --git a/src/PhpErrorController.php")
            if (preg_match('/^diff --git a\/(.+?) b\//m', $patchContent, $matches)) {
                $patchTargetFile = $matches[1];

                // Check if current file matches the patch target
                if (str_contains($filePath, $patchTargetFile)) {
                    // Create temporary file with current content
                    $tempFile = tempnam(sys_get_temp_dir(), 'patch_');
                    file_put_contents($tempFile, $content);

                    // Apply patch
                    exec(
                        'cd '.escapeshellarg(dirname($tempFile)).' && '
                        .'patch -p1 -N '.escapeshellarg(basename($tempFile)).' < '.escapeshellarg($patch).' 2>&1',
                        $output,
                        $returnCode
                    );

                    // Read patched content if successful or already applied
                    if (0 === $returnCode || 1 === $returnCode) {
                        $content = file_get_contents($tempFile);
                    }

                    // Cleanup
                    unlink($tempFile);
                }
            }
        }

        return $content;
    };

    return $config;
}
