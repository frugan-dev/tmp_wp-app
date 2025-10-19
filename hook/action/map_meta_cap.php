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
        // https://wordpress.stackexchange.com/questions/318666/how-to-allow-editor-to-edit-privacy-page-settings-only
        // https://gist.github.com/anthonyboutinov/3fe56de68acedf9be7c162b8730f09a9
        // https://wordpress.org/plugins/manage-privacy-options/
        'callback' => static function (Container $container, $caps, $cap, $user_id, $args) {
            $user_meta = get_userdata($user_id);

            if (!$user_meta) {
                return $caps;
            }

            if ('manage_privacy_options' !== $cap) {
                return $caps;
            }

            if (!array_intersect(['editor', 'administrator'], $user_meta->roles)) {
                return $caps;
            }

            $manage_name = is_multisite() ? 'manage_network' : 'manage_options';

            return array_diff($caps, [$manage_name]);
        },
        'priority' => 1,
        'accepted_args' => 4,
    ],
];
