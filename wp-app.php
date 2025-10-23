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

use DI\ContainerBuilder;
use WpApp\WpApp;
use WpSpaghetti\WpEnv\Environment;

/*
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @wordpress-plugin
 * Plugin Name:       WP-App
 * Plugin URI:        https://frugan.it
 * Description:       WordPress plugin.
 * Version:           3.0.0
 * Author:            Frugan
 * Author URI:        https://frugan.it
 * License:           GPL-3.0-or-later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       wp-app
 * Domain Path:       /lang
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    exit;
}

if (!defined('BEDROCK_ROOT') && !defined('WP_BOOT_ROOT')) {
    exit('BEDROCK_ROOT or WP_BOOT_ROOT not defined');
}

require __DIR__.'/vendor-deps/scoper-autoload.php';
require __DIR__.'/vendor/autoload.php';

\WpApp\Vendor\Safe\define('WPAPP_FILE', __FILE__);
\WpApp\Vendor\Safe\define('WPAPP_NAME', basename(__FILE__, '.php'));

$ContainerBuilder = new ContainerBuilder();

// https://php-di.org/doc/autowiring.html
// Autowiring is enabled by default
$ContainerBuilder->useAutowiring(true);

// https://php-di.org/doc/attributes.html
// Attributes are disabled by default
$ContainerBuilder->useAttributes(false);

if (defined('_HTTP_HOST')) {
    $http_host = _HTTP_HOST;
} elseif (!empty($_SERVER['HTTP_HOST'])) {
    $http_host = str_replace('www.', '', $_SERVER['HTTP_HOST']);
} elseif (!empty(Environment::get('WP_HOME'))) {
    $http_host = str_replace('www.', '', parse_url(Environment::get('WP_HOME'), PHP_URL_HOST));
} else {
    wp_die('http_host not defined');
}

$builderCacheDir = WP_CONTENT_DIR.'/cache/'.WPAPP_NAME.'/'.$http_host;
$builderProxiesDir = $builderCacheDir.'/proxies';

if (Environment::getBool('WPAPP_BUILDER_CACHE', true)) {
    // Uncaught LogicException: You cannot set a definition at runtime on a compiled container.
    // You can either put your definitions in a file, disable compilation
    // or ->set() a raw value directly (PHP object, string, int, ...) instead of a PHP-DI definition.
    $ContainerBuilder->enableCompilation($builderCacheDir);

    $ContainerBuilder->writeProxiesToFile(true, $builderProxiesDir);
} else {
    // https://wordpress.stackexchange.com/a/370377/99214
    if (!function_exists('WP_Filesystem_Direct')) {
        // @phpstan-ignore-next-line
        require_once ABSPATH.'wp-admin/includes/class-wp-filesystem-base.php';

        // @phpstan-ignore-next-line
        require_once ABSPATH.'wp-admin/includes/class-wp-filesystem-direct.php';
    }

    $wp_filesystem = new WP_Filesystem_Direct(null);

    if ($wp_filesystem->is_dir($builderCacheDir)) {
        $wp_filesystem->delete($builderCacheDir, true);
    }
}

// https://github.com/PHP-DI/PHP-DI/issues/674
$ContainerBuilder->addDefinitions(require __DIR__.'/include/container.php');

$container = $ContainerBuilder->build();

(require __DIR__.'/define.php')($container);
(require __DIR__.'/include/helpers.php')($container);

$container->get(WpApp::class)->run();
