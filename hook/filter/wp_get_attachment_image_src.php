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
        'callback' => static function (Container $container, $image) {
            if (Environment::getBool('CACHE_BUSTING_ENABLED')) {
                if (!is_array($image)) {
                    return $image;
                }

                $url = $image[0];

                if (!str_starts_with($url, home_url())) {
                    return $image;
                }

                $image[0] = wpapp_cache_busting($url);
            }

            return $image;
        },
    ],
];
