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
                    $class = 'alert alert-'.esc_attr($type).' alert-dismissible';
                    printf('<div class="%1$s" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="%2$s"><span aria-hidden="true">&times;</span></button><p>%3$s</p></div>'.PHP_EOL, $class, __('Close', WPAPP_TEXTDOMAIN), wp_kses_post($message));
                }
            }

            if (function_exists('wc_clear_notices')) {
                wc_clear_notices();
            }
        },
    ],
];
