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
        // You need to add the `wpapp_notices` cookie in W3TC > Page Cache > Advanced > Rejected cookies.
        'callback' => static function (Container $container): void {
            if (!is_admin() && !is_user_logged_in() && !headers_sent() && isset($_COOKIE['wpapp_notices']) && wpapp_is_plugin_active('w3-total-cache/w3-total-cache.php')) {
                $w3tc_config = w3_instance('W3_Config');
                if ($w3tc_config->get_boolean('pgcache.enabled')) {
                    unset($_COOKIE['wpapp_notices']);
                    setcookie('wpapp_notices', '', ['expires' => time() - 3600, 'path' => COOKIEPATH, 'domain' => COOKIE_DOMAIN]);
                }
            }
        },
        'priority' => 999,
    ],
];
