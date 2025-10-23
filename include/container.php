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

use Psr\Container\ContainerInterface;
use WpApp\Factory\Session\Session;
use WpApp\Factory\Wonolog;
use WpApp\Vendor\Inpsyde\Wonolog\HookListener\HttpApiListener;
use WpApp\Vendor\Inpsyde\Wonolog\LogLevel;
use WpSpaghetti\WpEnv\Environment;
use WpSpaghetti\WpLogger\Logger;

if (!defined('WPINC')) {
    exit;
}

return [
    'plugin_data' => \DI\factory(static function () {
        if (!function_exists('get_plugin_data')) {
            // @phpstan-ignore-next-line
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }

        return get_plugin_data(WPAPP_FILE, false, false);
    }),

    // https://codex.wordpress.org/Global_Variables
    'wp_version' => static function () {
        global $wp_version;

        return $wp_version;
    },

    'wpdb' => static function () {
        global $wpdb;

        return $wpdb;
    },

    'wp_roles' => static function () {
        global $wp_roles;

        return $wp_roles;
    },

    'wp_styles' => static function () {
        global $wp_styles;

        return $wp_styles;
    },

    'post' => static function () {
        global $post;

        return $post;
    },

    'woocommerce' => static function () {
        global $woocommerce;

        return $woocommerce;
    },

    'theme_name' => static function () {
        if (function_exists('wp_get_theme')) {
            $theme = wp_get_theme();

            return $theme->parent() ? $theme->parent()->get('Name') : $theme->get('Name');
        }

        return null;
    },

    'lang' => static function () {
        // Polylang
        if (function_exists('pll_current_language')) {
            return pll_current_language();
        }

        // WPML
        if (defined('ICL_LANGUAGE_CODE')) {
            return ICL_LANGUAGE_CODE;
        }

        // TranslatePress
        global $TRP_LANGUAGE;
        if (isset($TRP_LANGUAGE)) {
            return substr($TRP_LANGUAGE, 0, 2);
        }

        // MultilingualPress
        if (function_exists('mlp_get_current_language')) {
            $mlp_lang = mlp_get_current_language();

            return substr($mlp_lang, 0, 2);
        }

        // qTranslate-X
        global $q_config;
        if (isset($q_config['language'])) {
            return $q_config['language'];
        }

        // Weglot
        if (function_exists('weglot_get_current_language')) {
            return weglot_get_current_language();
        }

        // Other possible WordPress language detection methods:
        // - Global $curlang variable (used by some plugins)
        // - Global $wp_locale variable
        // - get_locale() function
        // - get_bloginfo("language")

        // Fallback
        return substr(get_locale(), 0, 2);
    },

    'http_host' => static function () {
        if (defined('_HTTP_HOST')) {
            $http_host = _HTTP_HOST;
        } elseif (!empty($_SERVER['HTTP_HOST'])) {
            $http_host = str_replace('www.', '', $_SERVER['HTTP_HOST']);
        }

        return $http_host ?? '';
    },

    'db' => static function ($db_user, $db_pass, $db_name, $db_host, $table_prefix, $set_table_names = true): wpdb {
        $handler = new wpdb($db_user, $db_pass, $db_name, $db_host);
        // $handler->show_errors();
        $handler->show_errors = true;
        $handler->suppress_errors = false;

        // $handler->prefix = $table_prefix;
        $handler->prefix = $handler->set_prefix($table_prefix, $set_table_names);

        // php reuses the existing connection when it is only the database that differs in the connection parameters.
        // Either use different user accounts for each connection, or just use selectdb on the same connection before the query.
        // $handler->select($db_name);

        return $handler;
    },

    'wp_filesystem' => static function (): WP_Filesystem_Direct {
        // https://wordpress.stackexchange.com/a/370377/99214
        if (!function_exists('WP_Filesystem_Direct')) {
            // @phpstan-ignore-next-line
            require_once ABSPATH.'wp-admin/includes/class-wp-filesystem-base.php';

            // @phpstan-ignore-next-line
            require_once ABSPATH.'wp-admin/includes/class-wp-filesystem-direct.php';
        }

        return new WP_Filesystem_Direct(null);
    },

    'session' => DI\factory([Session::class, 'create']),

    Wonolog::class => DI\autowire(),

    HttpApiListener::class => DI\create()
        ->constructor(DI\value(LogLevel::WARNING)),

    Logger::class => static function (ContainerInterface $container): Logger {
        // Use Environment class to detect testing environment
        $minLogLevel = Environment::isTesting() ? 'emergency' : (Environment::isDebug() ? 'debug' : 'info');

        return new Logger([
            'component_name' => WPAPP_NAME,
            'min_level' => $minLogLevel,
            // Overwritten by the environment variable `LOGGER_WONOLOG_NAMESPACE`,
            // who also manages the internal Logger in Vite
            'wonolog_namespace' => 'WpApp\Vendor\Inpsyde\Wonolog',
        ]);
    },
];
