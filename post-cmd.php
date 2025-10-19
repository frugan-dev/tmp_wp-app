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

$tmpDir = sys_get_temp_dir();
$targetDir = __DIR__.'/src/Dependencies/Inpsyde/Wonolog/inc';

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

copy($tmpDir.'/bootstrap.php', $targetDir.'/bootstrap.php');

unlink($tmpDir.'/bootstrap.php');
