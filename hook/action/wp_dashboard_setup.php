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
                global $wp_meta_boxes;

                $wp_meta_boxes['dashboard']['side']['core'] = [];
                $wp_meta_boxes['dashboard']['normal']['core'] = [];

                if (!empty(Environment::get('WP_HIDDEN_DASHBOARD_META_BOXES'))) {
                    $items = explode(',', (string) Environment::get('WP_HIDDEN_DASHBOARD_META_BOXES'));
                    $items = array_map('trim', $items);

                    foreach ($items as $item) {
                        remove_meta_box($item, 'dashboard', 'normal');
                        remove_meta_box($item, 'dashboard', 'side');
                    }
                }
            }
        },
    ],
];
