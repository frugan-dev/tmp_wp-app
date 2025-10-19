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
        'callback' => static function (Container $container, $post_id, $post, $update): void {
            /*if (!wpapp_is_plugin_active('w3-total-cache/w3-total-cache.php') || !current_user_can('w3tc_flush_cache')) {
                return;
            }

            if ( $update && $post->post_status === 'publish'
            && !in_array($post->post_type, ['revision', 'attachment'], true) ) {

                $config = \W3TC\Dispatcher::config();
                $is_w3tc_flushable = \W3TC\Util_Environment::is_flushable_post( $post, 'posts', $config );

                if ( $is_w3tc_flushable ) {
                    return;
                }

                // Force flush after WordPress has finished processing
                add_action( 'shutdown', function() use ( $post_id ) {
                    w3tc_flush_post( $post_id );
                }, 1 );
            }*/
        },
        'priority' => 999,
        'accepted_args' => 3,
    ],
];
