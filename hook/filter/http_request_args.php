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
        'callback' => static function (Container $container, array $args, string $url): array {
            if (Environment::isDebug()) {
                // Disable SSL verification for HTTP requests
                $args['sslverify'] = false;
                $args['timeout'] = 20; // default = 5
            }

            return $args;
        },
        'accepted_args' => 2,
    ],
];
