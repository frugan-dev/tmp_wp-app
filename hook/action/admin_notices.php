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
            $notices = wpapp_get_notices();

            foreach ($notices as $type => $messages) {
                foreach ($messages as $message) {
                    $class = 'notice notice-'.esc_attr($type).' is-dismissible';
                    printf('<div class="%1$s"><p>%2$s</p></div>', $class, wp_kses_post($message));
                }
            }

            if (function_exists('wc_clear_notices')) {
                wc_clear_notices();
            }
        },
    ],
];
