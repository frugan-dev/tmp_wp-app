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
            if ($container->has('session')) {
                $session = $container->get('session');

                if (!empty($session) && !$session->has('referer')) {
                    if (($referer = wp_get_referer()) !== false) {
                        $session->set('referer', $referer);
                    }
                }
            }
        },
    ],
];
