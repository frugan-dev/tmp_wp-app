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
                if (!empty(Environment::get('WP_HIDDEN_MENU_PAGES'))) {
                    $items = explode(',', (string) Environment::get('WP_HIDDEN_MENU_PAGES'));
                    $items = array_map('trim', $items);

                    foreach ($items as $item) {
                        remove_menu_page($item);
                    }
                }

                if (!empty(Environment::get('WP_HIDDEN_SUBMENU_PAGES'))) {
                    if (($items = json_decode(Environment::get('WP_HIDDEN_SUBMENU_PAGES'), true)) !== null) {
                        foreach ($items as $key => $val) {
                            foreach ($val as $item) {
                                remove_submenu_page($key, $item);
                            }
                        }
                    }
                }

                if ('open' !== get_option('default_comment_status')) {
                    remove_menu_page('edit-comments.php');
                }

                // https://stackoverflow.com/a/41499544/3929620
                // https://wordpress.org/support/topic/how-to-remove-the-customize-menu-entry/
                global $submenu;
                if (isset($submenu['themes.php'])) {
                    foreach ($submenu['themes.php'] as $key => $val) {
                        if (in_array('customize', $val, true)) {
                            unset($submenu['themes.php'][$key]);
                        }
                    }
                }
            }
        },
        'priority' => 999,
    ],
];
