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
        'callback' => static function (Container $container, string $content): string {
            global $wp_current_filter;
            if (in_array('get_the_excerpt', $wp_current_filter, true) || !in_the_loop() || !is_main_query()) {
                return $content;
            }

            ob_start();
            do_action('wpapp_before_content');
            $buffer = ob_get_clean();

            return $buffer.$content;
        },
        // IMPORTANT
        // This allows notices to be shown immediately within the same page load
        // (without requiring a page redirect or reload), after all other content
        // and filters have been processed.
        'priority' => 999,
    ],
];
