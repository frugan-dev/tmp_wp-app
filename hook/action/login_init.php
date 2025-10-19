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
            $action = filter_input(INPUT_GET, 'action');

            if (is_user_logged_in() && (!$action || in_array($action, ['login'], true))) {
                wpapp_wp_safe_redirect(admin_url());

                exit;
            }
        },
        'priority' => 999,
    ],
];
