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
use WpSpaghetti\WpVite\Vite;

if (!defined('WPINC')) {
    exit;
}

return [
    [
        'callback' => static function (Container $container): void {
            if (!empty(Environment::get('WP_ADMIN_IDS'))) {
                $ids = explode(',', (string) Environment::get('WP_ADMIN_IDS'));
                $ids = array_map('intval', $ids);
            }

            Vite::init(
                WPAPP_PATH,
                WPAPP_URL,
                $container->get('plugin_data')['Version'] ?? '0.0.0',
                WPAPP_NAME
            );

            if (!current_user_can('administrator') || (!empty($ids) && !in_array(get_current_user_id(), $ids, true))) {
                Vite::enqueueStyle(
                    'wpapp-admin-override',
                    'wp-admin/override',
                    ['wp-admin']
                );
            }

            // Add Vite dev scripts for hot reload
            Vite::devScripts();
        },
        'priority' => 999,
    ],
];
