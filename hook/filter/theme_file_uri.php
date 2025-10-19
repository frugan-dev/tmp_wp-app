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
use WpSpaghetti\WpEnv\Environment;

if (!defined('WPINC')) {
    exit;
}

return [
    [
        'callback' => static function (Container $container, string $url): string {
            if (!Environment::getBool('CACHE_BUSTING_ENABLED')) {
                return $url;
            }

            // bypass urls w/o extension
            if (preg_match('~\.[a-zA-Z0-9]+$~', $url)) {
                return wpapp_cache_busting($url);
            }

            return $url;
        },
    ],
];
