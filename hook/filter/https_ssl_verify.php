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
        // Disable SSL verification for all HTTPS requests
        'callback' => static function (Container $container, $verify, $url): bool {
            if (!is_string($url) || empty($url)) {
                return $verify;
            }

            $host = parse_url(home_url(), PHP_URL_HOST);
            $request_host = parse_url($url, PHP_URL_HOST);

            if ($request_host === $host) {
                return false;
            }

            return !Environment::isDebug();
        },
        'accepted_args' => 2,
    ],
];
