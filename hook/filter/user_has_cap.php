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
        'callback' => static function (Container $container, $allcaps) {
            if (!empty($allcaps['manage_options']) || empty($allcaps['w3tc_flush_cache'])) {
                return $allcaps;
            }

            if ((
                isset($_GET['page'])
                    && 'w3tc_dashboard' === $_GET['page']
                    && isset($_GET['w3tc_flush_post'])
            ) || (
                isset($_GET['w3tc_note'])
                    && in_array($_GET['w3tc_note'], ['pgcache_purge_post'], true)
            )) {
                // Temporarily grant permission for this action only
                $allcaps['manage_options'] = true;
            }

            return $allcaps;
        },
    ],
];
