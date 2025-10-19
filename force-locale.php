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

// https://make.wordpress.org/cli/handbook/guides/force-output-specific-locale/

if (!defined('WP_CLI') || !WP_CLI) {
    exit;
}

if (!empty($GLOBALS['argv'])) {
    $locale_arg = null;
    foreach ($GLOBALS['argv'] as $k => $v) {
        if (str_starts_with($v, '--locale=')) {
            $locale_arg = substr($v, 9);

            break;
        }

        if ('--locale' === $v && isset($GLOBALS['argv'][$k + 1])) {
            $locale_arg = $GLOBALS['argv'][$k + 1];

            break;
        }
    }

    if ($locale_arg) {
        WP_CLI::add_wp_hook('pre_option_WPLANG', static fn (): mixed => $locale_arg);
    }
}
