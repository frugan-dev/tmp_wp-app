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

// http://wpengineer.com/2382/wordpress-constants-overview/
// https://bugs.php.net/bug.php?id=46260
// https://github.com/logical-and/symlink-detective
return static function (Container $container): void {
    \Safe\define('WPAPP_URL', Environment::get('WPAPP_URL') ?? plugin_dir_url(__FILE__));
    \Safe\define('WPAPP_PATH', Environment::get('WPAPP_PATH') ?? plugin_dir_path(__FILE__));

    // https://rskuipers.com/entry/different-settings-for-dev-and-live-in-your-htaccess
    \Safe\define('WPAPP_DEV', false !== stristr((string) Environment::get('APACHE_ARGUMENTS'), '-D dev'));

    // FIXED - add `basename()` to work w/ symlink and local type path repositories in composer.json
    // \Safe\define('WPAPP_TEXTDOMAIN', basename(dirname(plugin_basename(__FILE__))));
    \Safe\define('WPAPP_TEXTDOMAIN', WPAPP_NAME);

    // only for development & plugin activation/deactivation
    if (!defined('WPAPP_THEME')) {
        // get_stylesheet_directory();
        // get_template_directory();
        // $theme = wp_get_theme(); $theme->get('stylesheet');
        \Safe\define('WPAPP_THEME', basename(get_stylesheet_directory()));
    }
};
