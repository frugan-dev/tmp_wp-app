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
        'callback' => static function (Container $container): void {
            if (!empty(Environment::get('WP_ADMIN_IDS'))) {
                $ids = explode(',', (string) Environment::get('WP_ADMIN_IDS'));
                $ids = array_map('intval', $ids);
            }

            if (!current_user_can('administrator') || (!empty($ids) && !in_array(get_current_user_id(), $ids, true))) {
                remove_action('admin_notices', 'update_nag', 3);
                remove_action('admin_notices', 'maintenance_nag', 10);

                if (!empty(Environment::get('WP_HIDDEN_ADMIN_NOTICES'))) {
                    $items = explode(',', (string) Environment::get('WP_HIDDEN_ADMIN_NOTICES'));
                    $items = array_map('trim', $items);

                    global $wp_filter;

                    if (isset($wp_filter['admin_notices'])) {
                        foreach ($wp_filter['admin_notices']->callbacks as $priority => $callbacks) {
                            foreach ($callbacks as $callback) {
                                if (is_array($callback['function']) && is_object($callback['function'][0])) {
                                    $class = $callback['function'][0]::class;

                                    foreach ($items as $item) {
                                        if (str_contains($class, $item)) {
                                            remove_action('admin_notices', $callback['function'], $priority);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        'priority' => 999,
    ],
];
