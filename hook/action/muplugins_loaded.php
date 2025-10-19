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

use DI\Container;

if (!defined('WPINC')) {
    exit;
}

return [
    [
        'callback' => static function (Container $container): void {
            // FIXED - don't work w/ symlink and local type path repositories in composer.json
            // if (str_starts_with(WPAPP_PATH, (string) WPMU_PLUGIN_DIR)) {
            if (is_dir(trailingslashit(WPMU_PLUGIN_DIR).WPAPP_NAME)) {
                load_muplugin_textdomain(
                    WPAPP_TEXTDOMAIN,
                    trailingslashit(WPAPP_NAME).'lang'
                );
            }
        },
    ],
];
