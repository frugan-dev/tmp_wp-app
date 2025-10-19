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
            if (Environment::getBool('GOOGLE_ANALYTICS_COOKIECONSENT') && !empty(Environment::get('GOOGLE_ANALYTICS_CODE')) && !is_user_logged_in()) {
                echo '<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id='.esc_attr(Environment::get('GOOGLE_ANALYTICS_CODE')).'"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag("js", new Date());
    gtag("config", "'.esc_js(Environment::get('GOOGLE_ANALYTICS_CODE')).'");
</script>'.PHP_EOL;
            }
        },
        'priority' => 999,
    ],
];
