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

$sourceDir = __DIR__.'/vendor/inpsyde/wonolog/inc';
$targetDir = __DIR__.'/src/Dependencies/Inpsyde/Wonolog/inc';
$tmpDir = sys_get_temp_dir();

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$sourceContent = file_get_contents($sourceDir.'/bootstrap.php');

$content = str_replace('makeLogger(', '_makeLogger(', $sourceContent);

file_put_contents($targetDir.'/bootstrap.php', $content);

$content = str_replace('Inpsyde\Wonolog', 'WpApp\Dependencies\Inpsyde\Wonolog', $sourceContent);
$content = str_replace('Psr\Log', 'WpApp\Dependencies\Psr\Log', $content);

file_put_contents($tmpDir.'/bootstrap.php', $content);
