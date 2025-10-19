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
        // https://premium.wpmudev.org/forums/topic/hide-administrators-from-user-list-except-current-user
        // https://stackoverflow.com/a/43760108/3929620
        // https://3.7designs.co/blog/2015/10/modifying-wordpress-postpage-quick-links-subsubsub-menu/
        // https://wordpress.stackexchange.com/a/173615
        // https://www.role-editor.com/remove-wordpress-built-in-user-roles/
        'callback' => static function (Container $container, WP_User_Query $wpUserQuery): void {
            if (!empty(Environment::get('WP_HIDDEN_IDS'))) {
                $ids = explode(',', (string) Environment::get('WP_HIDDEN_IDS'));
                $ids = array_map('intval', $ids);
            }

            if (!current_user_can('administrator') || (!empty($ids) && !in_array(get_current_user_id(), $ids, true))) {
                $wpUserQuery->query_where = str_replace(
                    'WHERE 1=1',
                    sprintf('WHERE 1=1 AND %s.ID NOT IN(', $container->get('wpdb')->users).Environment::get('WP_HIDDEN_IDS').')',
                    $wpUserQuery->query_where
                );
            }
        },
        'priority' => 999,
    ],
];
