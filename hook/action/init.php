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
            add_shortcode(WPAPP_NAME, function ($atts, $content, $shortcode_tag) use ($container) {
                $atts = shortcode_atts([
                    'controller' => null,
                    'view' => null,
                ], $atts);

                $parts = [];
                if ($atts['controller']) {
                    $parts[] = $atts['controller'];
                }

                if ($atts['view']) {
                    $parts[] = $atts['view'];
                }

                if ($parts) {
                    $path = implode('/', $parts);

                    $template = locate_template(WPAPP_NAME.'/views/'.$path.'.php');
                    if (!$template) {
                        $template = WPAPP_PATH.'views/'.$path.'.php';
                    }

                    if (file_exists($template)) {
                        ob_start();

                        include $template;

                        return ob_get_clean();
                    }
                }
            });
        },
    ],
];
